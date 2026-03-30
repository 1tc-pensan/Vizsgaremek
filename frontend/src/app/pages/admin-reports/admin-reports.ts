import { Component, OnInit, ChangeDetectionStrategy, ChangeDetectorRef } from '@angular/core';
import { RouterLink } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { DatePipe } from '@angular/common';
import { AdminService } from '../../services/admin';

@Component({
  selector: 'app-admin-reports',
  imports: [RouterLink, FormsModule, DatePipe],
  templateUrl: './admin-reports.html',
  styleUrl: './admin-reports.css',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AdminReports implements OnInit {
  reports: any[] = [];
  statusFilter = '';
  loading = false;

  constructor(private admin: AdminService, private cdr: ChangeDetectorRef) {}

  ngOnInit() { this.load(); }

  load() {
    this.loading = true;
    this.admin.getReports(this.statusFilter || undefined).subscribe({
      next: data => { this.reports = data; this.loading = false; this.cdr.markForCheck(); },
      error: () => { this.loading = false; this.cdr.markForCheck(); },
    });
  }

  approve(id: number) { this.admin.approveReport(id).subscribe(() => this.load()); }
  reject(id: number)  { this.admin.rejectReport(id).subscribe(() => this.load()); }

  delete(id: number) {
    if (!confirm('Biztosan törlöd?')) return;
    this.admin.deleteReport(id).subscribe(() => this.load());
  }

  statusLabel(status: string): string {
    return ({ pending: 'Várakozik', approved: 'Jóváhagyva', rejected: 'Elutasítva' } as any)[status] ?? status;
  }

  statusClass(status: string): string {
    return ({ pending: 'warning', approved: 'success', rejected: 'danger' } as any)[status] ?? 'secondary';
  }
}
