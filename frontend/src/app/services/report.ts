import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { environment } from '../../environments/environment';

@Injectable({ providedIn: 'root' })
export class ReportService {
  private apiUrl = environment.apiUrl;

  constructor(private http: HttpClient) {}

  getAll(filters?: Record<string, string>) {
    let params = new HttpParams();
    if (filters) {
      Object.keys(filters).forEach(k => {
        if (filters[k]) params = params.set(k, filters[k]);
      });
    }
    return this.http.get<any[]>(`${this.apiUrl}/reports`, { params });
  }

  getOne(id: number) {
    return this.http.get<any>(`${this.apiUrl}/reports/${id}`);
  }

  create(data: any) {
    return this.http.post<any>(`${this.apiUrl}/reports`, data);
  }

  update(id: number, data: any) {
    return this.http.put<any>(`${this.apiUrl}/reports/${id}`, data);
  }

  delete(id: number) {
    return this.http.delete<any>(`${this.apiUrl}/reports/${id}`);
  }

  getMapReports() {
    return this.http.get<any[]>(`${this.apiUrl}/map/reports`);
  }

  uploadImages(reportId: number, files: FileList | File[]) {
    const fd = new FormData();
    Array.from(files).forEach(f => fd.append('images[]', f));
    return this.http.post<any>(`${this.apiUrl}/reports/${reportId}/images`, fd);
  }
}
