import { Routes } from '@angular/router';
import { authGuard } from './guards/auth-guard';
import { adminGuard } from './guards/admin-guard';

export const routes: Routes = [
  {
    path: '',
    loadComponent: () => import('./pages/report-list/report-list').then(m => m.ReportList),
  },
  {
    path: 'login',
    loadComponent: () => import('./pages/login/login').then(m => m.Login),
  },
  {
    path: 'register',
    loadComponent: () => import('./pages/register/register').then(m => m.Register),
  },
  {
    path: 'reports/create',
    canActivate: [authGuard],
    loadComponent: () => import('./pages/report-form/report-form').then(m => m.ReportForm),
  },
  {
    path: 'reports/:id/edit',
    canActivate: [authGuard],
    loadComponent: () => import('./pages/report-form/report-form').then(m => m.ReportForm),
  },
  {
    path: 'reports/:id',
    loadComponent: () => import('./pages/report-detail/report-detail').then(m => m.ReportDetail),
  },
  {
    path: 'statistics',
    loadComponent: () => import('./pages/statistics/statistics').then(m => m.Statistics),
  },
  {
    path: 'profile',
    canActivate: [authGuard],
    loadComponent: () => import('./pages/profile/profile').then(m => m.Profile),
  },
  {
    path: 'admin/reports',
    canActivate: [adminGuard],
    loadComponent: () => import('./pages/admin-reports/admin-reports').then(m => m.AdminReports),
  },
  {
    path: 'admin/users',
    canActivate: [adminGuard],
    loadComponent: () => import('./pages/admin-users/admin-users').then(m => m.AdminUsers),
  },
  {
    path: 'admin/categories',
    canActivate: [adminGuard],
    loadComponent: () => import('./pages/admin-categories/admin-categories').then(m => m.AdminCategories),
  },
  { path: '**', redirectTo: '' },
];
