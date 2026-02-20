import { Component } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { Router, RouterModule } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../../environments/environment';
import { ChangeDetectorRef } from '@angular/core';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [CommonModule, FormsModule, ReactiveFormsModule, RouterModule],
  templateUrl: './register.html',
  styleUrls: ['./register.scss']
})

export class Register {

  registerForm: FormGroup;
  errorMessage = '';

  constructor(
    private http: HttpClient,
    private router: Router,
    private cdr: ChangeDetectorRef,
    private fb: FormBuilder) {
    // Initialisation du formulaire avec validation
    this.registerForm = this.fb.group({
      username: ['', [
        Validators.required,
        Validators.minLength(3),
        Validators.maxLength(20),
        Validators.pattern('^[a-zA-Z0-9_-]+$')
      ]],
      email: ['', [
        Validators.required,
        Validators.pattern('^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$')
      ]],
      password: ['', [
        Validators.required,
        Validators.minLength(12),
        Validators.maxLength(128),
        Validators.pattern('^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[!@#$%^&*()_\\-+=]).+$')
      ]]
    });
  }

  onRegister() {
    // Vérifie que le formulaire est valide avant tout envoi
    if (this.registerForm.invalid) {
      this.registerForm.markAllAsTouched();
      return;
    }

    this.errorMessage = '';

    this.http.post(`${environment.apiUrl}/register`, {
      username: this.registerForm.get('username')?.value,
      email: this.registerForm.get('email')?.value,
      password: this.registerForm.get('password')?.value
    }).subscribe({
      next: () => {
        this.router.navigate(['/inscription-reussie']);
      },
      error: (err) => {
        this.errorMessage = err.error.message || "Une erreur est survenue";
        this.cdr.detectChanges();
      }
    });
  }
}