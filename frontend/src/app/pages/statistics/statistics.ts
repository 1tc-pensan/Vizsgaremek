import { Component, OnInit, ChangeDetectionStrategy, ChangeDetectorRef } from '@angular/core';
import { RouterLink } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { DatePipe, KeyValuePipe } from '@angular/common';
import { environment } from '../../../environments/environment';

@Component({
  selector: 'app-statistics',
  imports: [RouterLink, DatePipe, KeyValuePipe],
  templateUrl: './statistics.html',
  styleUrl: './statistics.css',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class Statistics implements OnInit {
  private apiUrl = environment.apiUrl;
  stats: any = null;
  loading = true;

  constructor(private http: HttpClient, private cdr: ChangeDetectorRef) {}

  ngOnInit() {
    this.http.get<any>(`${this.apiUrl}/statistics`).subscribe({
      next: data => { this.stats = data; this.loading = false; this.cdr.markForCheck(); },
      error: () => { this.loading = false; this.cdr.markForCheck(); },
    });
  }

  statusLabel(status: string): string {
    return ({ pending: 'Várakozik', approved: 'Jóváhagyva', rejected: 'Elutasítva' } as any)[status] ?? status;
  }

  statusClass(status: string): string {
    return ({ pending: 'warning', approved: 'success', rejected: 'danger' } as any)[status] ?? 'secondary';
  }

  categoryBarWidth(count: number): number {
    if (!this.stats?.by_category?.length) return 0;
    const max = this.stats.by_category[0].reports_count;
    return max > 0 ? Math.round((count / max) * 100) : 0;
  }
}
