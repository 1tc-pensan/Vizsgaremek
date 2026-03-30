import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { environment } from '../../environments/environment';

@Injectable({ providedIn: 'root' })
export class AdminService {
  private apiUrl = `${environment.apiUrl}/admin`;

  constructor(private http: HttpClient) {}

  getReports(status?: string) {
    let params = new HttpParams();
    if (status) params = params.set('status', status);
    return this.http.get<any[]>(`${this.apiUrl}/reports`, { params });
  }

  approveReport(id: number) {
    return this.http.put<any>(`${this.apiUrl}/reports/${id}/approve`, {});
  }

  rejectReport(id: number) {
    return this.http.put<any>(`${this.apiUrl}/reports/${id}/reject`, {});
  }

  deleteReport(id: number) {
    return this.http.delete<any>(`${this.apiUrl}/reports/${id}`);
  }

  getUsers(search?: string) {
    let params = new HttpParams();
    if (search) params = params.set('search', search);
    return this.http.get<any[]>(`${this.apiUrl}/users`, { params });
  }

  banUser(id: number) {
    return this.http.put<any>(`${this.apiUrl}/users/${id}/ban`, {});
  }

  unbanUser(id: number) {
    return this.http.put<any>(`${this.apiUrl}/users/${id}/unban`, {});
  }
}
