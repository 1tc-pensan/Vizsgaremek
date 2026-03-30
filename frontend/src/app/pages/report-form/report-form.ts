import { Component, OnInit, ChangeDetectionStrategy, ChangeDetectorRef } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { ReportService } from '../../services/report';
import { CategoryService } from '../../services/category';
import { MapComponent } from '../../components/map/map';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../../environments/environment';

@Component({
  selector: 'app-report-form',
  imports: [FormsModule, MapComponent],
  templateUrl: './report-form.html',
  styleUrl: './report-form.css',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ReportForm implements OnInit {
  isEdit = false;
  reportId: number | null = null;
  categories: any[] = [];
  existingImages: any[] = [];
  error = '';
  loading = false;
  uploadError = '';
  uploading = false;
  selectedFileList: File[] = [];
  previews: string[] = [];

  form: Record<string, any> = {
    category_id: '',
    title: '',
    description: '',
    latitude: '',
    longitude: '',
    date: '',
    witnesses: '',
  };

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private reportService: ReportService,
    private categoryService: CategoryService,
    private cdr: ChangeDetectorRef,
    private http: HttpClient
  ) {}

  ngOnInit() {
    this.categoryService.getAll().subscribe(data => { this.categories = data; this.cdr.markForCheck(); });
    const id = Number(this.route.snapshot.paramMap.get('id'));
    if (id) {
      this.isEdit = true;
      this.reportId = id;
      this.reportService.getOne(id).subscribe(data => {
        this.form = {
          category_id: data.category_id,
          title: data.title,
          description: data.description,
          latitude: data.latitude ?? '',
          longitude: data.longitude ?? '',
          date: data.date,
          witnesses: data.witnesses ?? '',
        };
        this.existingImages = data.images ?? [];
        this.cdr.markForCheck();
      });
    }
  }

  onFilesSelected(event: Event) {
    const files = (event.target as HTMLInputElement).files;
    if (!files) return;
    Array.from(files).forEach(file => {
      const reader = new FileReader();
      reader.onload = (e: any) => {
        this.selectedFileList.push(file);
        this.previews.push(e.target.result);
        this.cdr.markForCheck();
      };
      reader.readAsDataURL(file);
    });
    (event.target as HTMLInputElement).value = '';
  }

  removeSelectedFile(index: number) {
    this.selectedFileList.splice(index, 1);
    this.previews.splice(index, 1);
    this.cdr.markForCheck();
  }

  onMapClick(coords: [number, number]) {
    this.form['latitude'] = coords[0];
    this.form['longitude'] = coords[1];
    this.cdr.markForCheck();
  }

  submit() {
    this.error = '';
    this.loading = true;
    const obs = this.isEdit && this.reportId
      ? this.reportService.update(this.reportId, this.form)
      : this.reportService.create(this.form);

    obs.subscribe({
      next: res => {
        if (!this.isEdit && this.selectedFileList.length > 0) {
          this.reportService.uploadImages(res.id, this.selectedFileList).subscribe({
            next: () => this.router.navigate(['/reports', res.id]),
            error: () => this.router.navigate(['/reports', res.id]),
          });
        } else {
          this.router.navigate(['/reports', res.id]);
        }
      },
      error: err => {
        const errors = err.error?.errors;
        if (errors) {
          this.error = (Object.values(errors) as string[][]).flat().join(' ');
        } else {
          this.error = err.error?.message || 'Hiba történt.';
        }
        this.loading = false;
        this.cdr.markForCheck();
      },
    });
  }

  goBack() {
    window.history.back();
  }

  deleteExistingImage(imageId: number) {
    if (!confirm('Törlöd ezt a képet?')) return;
    this.http.delete(`${environment.apiUrl}/images/${imageId}`).subscribe(() => {
      this.existingImages = this.existingImages.filter(img => img.id !== imageId);
      this.cdr.markForCheck();
    });
  }

  onEditFilesSelected(event: Event) {
    const input = event.target as HTMLInputElement;
    if (!input.files || input.files.length === 0) return;
    this.uploadError = '';
    this.uploading = true;
    this.reportService.uploadImages(this.reportId!, input.files).subscribe({
      next: (uploaded: any[]) => {
        this.existingImages = [...this.existingImages, ...uploaded];
        this.uploading = false;
        input.value = '';
        this.cdr.markForCheck();
      },
      error: err => {
        this.uploadError = err.error?.message || 'Feltöltési hiba.';
        this.uploading = false;
        this.cdr.markForCheck();
      },
    });
  }

  imageUrl(img: any): string {
    return img.image_url ?? `${environment.storageUrl}/${img.image_path}`;
  }
}
