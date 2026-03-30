import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../environments/environment';

@Injectable({ providedIn: 'root' })
export class CategoryService {
  private apiUrl = environment.apiUrl;

  constructor(private http: HttpClient) {}

  getAll() {
    return this.http.get<any[]>(`${this.apiUrl}/categories`);
  }

  create(data: any) {
    return this.http.post<any>(`${this.apiUrl}/admin/categories`, data);
  }

  update(id: number, data: any) {
    return this.http.put<any>(`${this.apiUrl}/admin/categories/${id}`, data);
  }

  delete(id: number) {
    return this.http.delete<any>(`${this.apiUrl}/admin/categories/${id}`);
  }
}
