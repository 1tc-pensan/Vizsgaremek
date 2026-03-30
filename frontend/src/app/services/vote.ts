import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../environments/environment';

@Injectable({ providedIn: 'root' })
export class VoteService {
  private apiUrl = environment.apiUrl;

  constructor(private http: HttpClient) {}

  vote(reportId: number, voteType: 'up' | 'down') {
    return this.http.post<any>(`${this.apiUrl}/reports/${reportId}/vote`, { vote_type: voteType });
  }
}
