import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router, RouterModule } from '@angular/router';
import { environment } from '../../../environments/environment';
import { CommonModule } from '@angular/common';
import { ChangeDetectorRef } from '@angular/core';

@Component({
  selector: 'app-unactived-account',
  standalone: true,
  imports: [RouterModule, CommonModule],
  templateUrl: './unactived-account.html',
  styleUrls: ['./unactived-account.scss'],
})
export class UnactivedAccount implements OnInit {

  username: string | null = null;
  message: string = '';
  error: string = '';
  loading: boolean = false;

  constructor(
    private http: HttpClient,
    private router: Router,
    private cdr: ChangeDetectorRef
  ) { }

  ngOnInit(): void {
    this.username = sessionStorage.getItem('account_to_verify');
    if (!this.username) this.router.navigate(['/connexion']);
  }

  resendVerification(): void {
    if (!this.username) return;

    this.loading = true;
    this.message = '';
    this.error = '';

    this.http.post<{ message: string }>(`${environment.apiUrl}/send-email-verification`, {
      username: this.username
    }).subscribe({
      next: (resp) => {
        this.message = resp?.message || 'Un nouvel email de confirmation a été envoyé.';
        this.loading = false;
        this.cdr.detectChanges();
      },
      error: (err) => {
        this.error = err.error?.message || 'Une erreur est survenue.';
        this.loading = false;
        this.cdr.detectChanges();
      }
    });
  }
}
