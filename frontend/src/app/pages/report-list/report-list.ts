import { Component, OnInit, OnDestroy, ChangeDetectionStrategy, ChangeDetectorRef } from '@angular/core';
import { RouterLink } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { DatePipe, SlicePipe } from '@angular/common';
import { ReportService } from '../../services/report';
import { CategoryService } from '../../services/category';
import { AuthService } from '../../services/auth';
import { environment } from '../../../environments/environment';
import { Subject, debounceTime, takeUntil } from 'rxjs';

@Component({
  selector: 'app-report-list',
  imports: [RouterLink, FormsModule, DatePipe, SlicePipe],
  templateUrl: './report-list.html',
  styleUrl: './report-list.css',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ReportList implements OnInit, OnDestroy {
  reports: any[] = [];
  top3: any[] = [];
  categories: any[] = [];
  loading = false;
  sliderIndex = 0;

  // Pagination
  readonly pageSize = 9;
  currentPage = 1;

  get totalPages(): number {
    return Math.ceil(this.reports.length / this.pageSize);
  }

  get pagedReports(): any[] {
    const start = (this.currentPage - 1) * this.pageSize;
    return this.reports.slice(start, start + this.pageSize);
  }

  get pageNumbers(): number[] {
    return Array.from({ length: this.totalPages }, (_, i) => i + 1);
  }

  goToPage(page: number) {
    if (page < 1 || page > this.totalPages) return;
    this.currentPage = page;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  filters: Record<string, string> = {
    category_id: '',
    date_from: '',
    date_to: '',
    sort_by: 'created_at',
    sort_dir: 'desc',
  };

  // Egyetlen select értéke, pl. "created_at_desc"
  sortOption = 'created_at_desc';

  sortOptions = [
    { value: 'created_at_desc', label: 'Feltöltés dátuma (újabb elöl)' },
    { value: 'created_at_asc',  label: 'Feltöltés dátuma (régebbi elöl)' },
    { value: 'date_desc',       label: 'Esemény dátuma (újabb elöl)' },
    { value: 'date_asc',        label: 'Esemény dátuma (régebbi elöl)' },
    { value: 'title_asc',       label: 'Cím (A → Z)' },
    { value: 'title_desc',      label: 'Cím (Z → A)' },
    { value: 'credibility_desc', label: 'Hitelesség (nagyobb elöl)' },
    { value: 'credibility_asc',  label: 'Hitelesség (kisebb elöl)' },
  ];

  private filterChange$ = new Subject<void>();
  private destroy$ = new Subject<void>();
  private sliderTimer: any = null;

  constructor(
    private reportService: ReportService,
    private categoryService: CategoryService,
    public auth: AuthService,
    private cdr: ChangeDetectorRef
  ) {}

  ngOnInit() {
    this.categoryService.getAll().subscribe(data => { this.categories = data; this.cdr.markForCheck(); });

    // 400ms debounce – ne induljon kérés minden egyes változásnál
    this.filterChange$.pipe(
      debounceTime(400),
      takeUntil(this.destroy$),
    ).subscribe(() => this.loadReports());

    this.loadReports();
  }

  ngOnDestroy() {
    this.destroy$.next();
    this.destroy$.complete();
    this.stopSlider();
  }

  startSlider() {
    this.stopSlider();
    this.sliderTimer = setInterval(() => {
      this.sliderIndex = (this.sliderIndex + 1) % this.top3.length;
      this.cdr.markForCheck();
    }, 5000);
  }

  stopSlider() {
    if (this.sliderTimer) {
      clearInterval(this.sliderTimer);
      this.sliderTimer = null;
    }
  }

  onFilterChange() {
    const [sort_by, sort_dir] = this.sortOption.split(/_(?=[^_]+$)/);
    this.filters['sort_by'] = sort_by;
    this.filters['sort_dir'] = sort_dir;
    this.currentPage = 1;
    this.filterChange$.next();
  }

  loadReports() {
    this.loading = true;
    this.cdr.markForCheck();
    this.reportService.getAll(this.filters).subscribe({
      next: data => {
        this.reports = data;
        this.loading = false;
        this.top3 = this.calcTop3(data);
        this.cdr.markForCheck();
        if (this.top3.length > 1) this.startSlider();
      },
      error: () => { this.loading = false; this.cdr.markForCheck(); },
    });
  }

  resetFilters() {
    this.sortOption = 'created_at_desc';
    this.filters = { category_id: '', date_from: '', date_to: '', sort_by: 'created_at', sort_dir: 'desc' };
    this.currentPage = 1;
    this.loadReports();
  }

  statusLabel(status: string): string {
    return ({ pending: 'Várakozik', approved: 'Jóváhagyva', rejected: 'Elutasítva' } as any)[status] ?? status;
  }

  statusClass(status: string): string {
    return ({ pending: 'warning', approved: 'success', rejected: 'danger' } as any)[status] ?? 'secondary';
  }

  calcTop3(reports: any[]): any[] {
    return [...reports]
      .sort((a, b) => (b.upvotes_count - b.downvotes_count) - (a.upvotes_count - a.downvotes_count))
      .slice(0, 3);
  }

  sliderPrev() {
    this.sliderIndex = (this.sliderIndex - 1 + this.top3.length) % this.top3.length;
    this.startSlider();
  }

  sliderNext() {
    this.sliderIndex = (this.sliderIndex + 1) % this.top3.length;
    this.startSlider();
  }

  imageUrl(path: string): string {
    return `${environment.storageUrl}/${path}`;
  }
}
