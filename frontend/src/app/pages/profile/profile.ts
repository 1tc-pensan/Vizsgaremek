import { Component, OnInit, ChangeDetectionStrategy, ChangeDetectorRef } from '@angular/core';
import { RouterLink } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { DatePipe } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { AuthService } from '../../services/auth';
import { environment } from '../../../environments/environment';

@Component({
  selector: 'app-profile',
  imports: [RouterLink, FormsModule, DatePipe],
  templateUrl: './profile.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class Profile implements OnInit {
  private apiUrl = environment.apiUrl;
  user: any = null;
  reports: any[] = [];
  successMsg = '';
  error = '';
  form = { name: '', email: '', password: '', password_confirmation: '' };

  constructor(private http: HttpClient, public auth: AuthService, private cdr: ChangeDetectorRef) {}

  ngOnInit() {
    this.http.get<any>(`${this.apiUrl}/profile`).subscribe(data => {
      this.user = data;
      this.form.name = data.name;
      this.form.email = data.email;
      this.cdr.markForCheck();
    });
    const userId = this.auth.currentUser()?.id;
    if (userId) {
      this.http.get<any[]>(`${this.apiUrl}/users/${userId}/reports`).subscribe(data => {
        this.reports = data;
        this.cdr.markForCheck();
      });
    }
  }

  updateProfile() {
    this.successMsg = '';
    this.error = '';
    const payload: any = { name: this.form.name, email: this.form.email };
    if (this.form.password) {
      payload.password = this.form.password;
      payload.password_confirmation = this.form.password_confirmation;
    }
    this.http.put<any>(`${this.apiUrl}/profile`, payload).subscribe({
      next: data => {
        this.user = data;
        localStorage.setItem('user', JSON.stringify(data));
        this.auth.currentUser.set(data);
        this.successMsg = 'Profil frissítve.';
        this.form.password = '';
        this.form.password_confirmation = '';
        this.cdr.markForCheck();
      },
      error: err => {
        const errors = err.error?.errors;
        if (errors) {
          this.error = (Object.values(errors) as string[][]).flat().join(' ');
        } else {
          this.error = err.error?.message || 'Hiba történt.';
        }
        this.cdr.markForCheck();
      },
    });
  }
}
