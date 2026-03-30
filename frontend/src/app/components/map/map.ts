import {
  Component,
  Input,
  Output,
  EventEmitter,
  OnChanges,
  OnDestroy,
  AfterViewInit,
  SimpleChanges,
  ElementRef,
  ViewChild,
  ChangeDetectionStrategy,
  NgZone,
} from '@angular/core';
import * as L from 'leaflet';

// Fix leaflet default marker icon (webpack/angular build issue)
const iconDefault = L.icon({
  iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
  iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
  shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
  iconSize: [25, 41],
  iconAnchor: [12, 41],
  popupAnchor: [1, -34],
  shadowSize: [41, 41],
});
L.Marker.prototype.options.icon = iconDefault;

@Component({
  selector: 'app-map',
  standalone: true,
  changeDetection: ChangeDetectionStrategy.OnPush,
  templateUrl: './map.html',
  styleUrl: './map.css',
})
export class MapComponent implements AfterViewInit, OnChanges, OnDestroy {
  @Input() lat: number | null = null;
  @Input() lng: number | null = null;
  @Input() clickable = false;
  @Input() height = '300px';
  @Output() mapClick = new EventEmitter<[number, number]>();

  @ViewChild('mapEl', { static: true }) mapEl!: ElementRef<HTMLDivElement>;

  private map!: L.Map;
  private marker: L.Marker | null = null;

  constructor(private zone: NgZone) {}

  ngAfterViewInit() {
    // NgZone-on kívül futtatjuk, hogy az Angular CD ne akadjon bele
    this.zone.runOutsideAngular(() => {
      this.initMap();
      // Két RAF-os késleltetés: biztosan megkapja a konténer a végleges méretét
      requestAnimationFrame(() => {
        requestAnimationFrame(() => {
          this.map?.invalidateSize();
        });
      });
    });
  }

  ngOnChanges(changes: SimpleChanges) {
    if (!this.map) return;
    if (changes['lat'] || changes['lng']) {
      this.zone.runOutsideAngular(() => this.updateMarker());
    }
  }

  ngOnDestroy() {
    this.map?.remove();
  }

  private initMap() {
    const centerLat = this.lat ?? 47.4979;
    const centerLng = this.lng ?? 19.0402;
    const zoom = (this.lat && this.lng) ? 10 : 5;

    this.map = L.map(this.mapEl.nativeElement, {
      zoomControl: true,
      // Prevent gray tiles on first render
      fadeAnimation: true,
      preferCanvas: false,
    }).setView([centerLat, centerLng], zoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
      maxZoom: 19,
    }).addTo(this.map);

    if (this.lat && this.lng) {
      this.placeMarker(this.lat, this.lng);
    }

    if (this.clickable) {
      this.map.on('click', (e: L.LeafletMouseEvent) => {
        const { lat, lng } = e.latlng;
        this.placeMarker(lat, lng);
        this.zone.run(() => {
          this.mapClick.emit([
            Math.round(lat * 1e6) / 1e6,
            Math.round(lng * 1e6) / 1e6,
          ]);
        });
      });
    }
  }

  private updateMarker() {
    if (this.lat && this.lng) {
      this.placeMarker(this.lat, this.lng);
      this.map.setView([this.lat, this.lng], this.map.getZoom() < 5 ? 10 : this.map.getZoom());
    } else {
      this.marker?.remove();
      this.marker = null;
    }
  }

  private placeMarker(lat: number, lng: number) {
    if (this.marker) {
      this.marker.setLatLng([lat, lng]);
    } else {
      this.marker = L.marker([lat, lng]).addTo(this.map);
    }
  }
}
