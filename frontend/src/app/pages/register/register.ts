import { Component, ChangeDetectionStrategy, ChangeDetectorRef, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { AuthService } from '../../services/auth';

@Component({
  selector: 'app-register',
  imports: [FormsModule, RouterLink],
  templateUrl: './register.html',
  styleUrl: './register.css',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class Register {
  name = '';
  email = '';
  password = '';
  password_confirmation = '';
  error = signal('');
  loading = signal(false);

  constructor(private auth: AuthService, private router: Router, private cdr: ChangeDetectorRef) {}

  submit() {
    this.error.set('');
    this.loading.set(true);
    this.auth
      .register({
        name: this.name,
        email: this.email,
        password: this.password,
        password_confirmation: this.password_confirmation,
      })
      .subscribe({
        next: () => this.router.navigate(['/']),
        error: err => {
          const errors = err.error?.errors;
          if (errors) {
            this.error.set((Object.values(errors) as string[][]).flat().join(' '));
          } else {
            this.error.set(err.error?.message || 'Hiba a regisztrációnál.');
          }
          this.loading.set(false);
        },
      });
  }
}
