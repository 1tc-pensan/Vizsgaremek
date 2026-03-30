import { Component, OnInit, ChangeDetectionStrategy, ChangeDetectorRef } from '@angular/core';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { DatePipe } from '@angular/common';
import { ReportService } from '../../services/report';
import { VoteService } from '../../services/vote';
import { AuthService } from '../../services/auth';
import { HttpClient } from '@angular/common/http';
import { MapComponent } from '../../components/map/map';
import { environment } from '../../../environments/environment';

@Component({
  selector: 'app-report-detail',
  imports: [RouterLink, DatePipe, MapComponent],
  templateUrl: './report-detail.html',
  styleUrl: './report-detail.css',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ReportDetail implements OnInit {
  private apiUrl = environment.apiUrl;
  report: any = null;
  loading = false;
  voteMessage = '';
  uploadError = '';
  uploading = false;
  lightboxIndex: number | null = null;

  openLightbox(index: number) {
    this.lightboxIndex = index;
    this.cdr.markForCheck();
  }

  closeLightbox() {
    this.lightboxIndex = null;
    this.cdr.markForCheck();
  }

  lightboxPrev() {
    if (this.lightboxIndex === null) return;
    const len = this.report.images.length;
    this.lightboxIndex = (this.lightboxIndex - 1 + len) % len;
    this.cdr.markForCheck();
  }

  lightboxNext() {
    if (this.lightboxIndex === null) return;
    const len = this.report.images.length;
    this.lightboxIndex = (this.lightboxIndex + 1) % len;
    this.cdr.markForCheck();
  }

  onLightboxKey(event: KeyboardEvent) {
    if (event.key === 'ArrowLeft') this.lightboxPrev();
    else if (event.key === 'ArrowRight') this.lightboxNext();
    else if (event.key === 'Escape') this.closeLightbox();
  }

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private reportService: ReportService,
    private voteService: VoteService,
    public auth: AuthService,
    private cdr: ChangeDetectorRef,
    private http: HttpClient
  ) {}

  ngOnInit() {
    const id = Number(this.route.snapshot.paramMap.get('id'));
    this.loading = true;
    this.reportService.getOne(id).subscribe({
      next: data => { this.report = data; this.loading = false; this.cdr.markForCheck(); },
      error: () => { this.loading = false; this.cdr.markForCheck(); },
    });
  }

  vote(type: 'up' | 'down') {
    if (!this.auth.isLoggedIn()) { this.router.navigate(['/login']); return; }
    this.voteService.vote(this.report.id, type).subscribe(res => {
      this.report.upvotes_count = res.upvotes;
      this.report.downvotes_count = res.downvotes;
      this.voteMessage = res.message;
      this.cdr.markForCheck();
    });
  }

  delete() {
    if (!confirm('Biztosan törlöd ezt a bejelentést?')) return;
    this.reportService.delete(this.report.id).subscribe(() => this.router.navigate(['/']));
  }

  onFilesSelected(event: Event) {
    const input = event.target as HTMLInputElement;
    if (!input.files || input.files.length === 0) return;
    this.uploadError = '';
    this.uploading = true;
    this.reportService.uploadImages(this.report.id, input.files).subscribe({
      next: (uploaded: any[]) => {
        this.report.images = [...(this.report.images ?? []), ...uploaded];
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

  deleteImage(imageId: number) {
    if (!confirm('Törlöd ezt a képet?')) return;
    this.http.delete(`${this.apiUrl}/images/${imageId}`).subscribe(() => {
      this.report.images = this.report.images.filter((img: any) => img.id !== imageId);
      this.cdr.markForCheck();
    });
  }

  isOwner(): boolean {
    return this.auth.currentUser()?.id === this.report?.user_id;
  }

  goBack() {
    window.history.back();
  }

  imageUrl(img: any): string {
    return img.image_url ?? `${environment.storageUrl}/${img.image_path}`;
  }

  statusLabel(status: string): string {
    return ({ pending: 'Várakozik', approved: 'Jóváhagyva', rejected: 'Elutasítva' } as any)[status] ?? status;
  }

  statusClass(status: string): string {
    return ({ pending: 'warning', approved: 'success', rejected: 'danger' } as any)[status] ?? 'secondary';
  }
}
