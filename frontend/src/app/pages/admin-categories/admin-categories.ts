import { Component, OnInit, ChangeDetectionStrategy, ChangeDetectorRef } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { CategoryService } from '../../services/category';

@Component({
  selector: 'app-admin-categories',
  imports: [FormsModule],
  templateUrl: './admin-categories.html',
  styleUrl: './admin-categories.css',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AdminCategories implements OnInit {
  categories: any[] = [];
  form = { name: '', description: '' };
  editingId: number | null = null;
  error = '';

  constructor(private categoryService: CategoryService, private cdr: ChangeDetectorRef) {}

  ngOnInit() { this.load(); }

  load() {
    this.categoryService.getAll().subscribe(data => { this.categories = data; this.cdr.markForCheck(); });
  }

  submit() {
    this.error = '';
    const obs = this.editingId
      ? this.categoryService.update(this.editingId, this.form)
      : this.categoryService.create(this.form);

    obs.subscribe({
      next: () => { this.form = { name: '', description: '' }; this.editingId = null; this.load(); },
      error: err => {
        const errors = err.error?.errors;
        this.error = errors
          ? (Object.values(errors) as string[][]).flat().join(' ')
          : (err.error?.message || 'Hiba.');
        this.cdr.markForCheck();
      },
    });
  }

  edit(cat: any) {
    this.editingId = cat.id;
    this.form = { name: cat.name, description: cat.description ?? '' };
  }

  cancelEdit() {
    this.editingId = null;
    this.form = { name: '', description: '' };
  }

  delete(id: number) {
    if (!confirm('Biztosan törlöd?')) return;
    this.categoryService.delete(id).subscribe(() => this.load());
  }
}
