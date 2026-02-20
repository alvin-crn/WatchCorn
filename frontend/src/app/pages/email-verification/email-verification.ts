import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { CommonModule } from '@angular/common';
import { environment } from '../../../environments/environment';
import { ChangeDetectorRef } from '@angular/core';

@Component({
  selector: 'app-email-verification',
  imports: [CommonModule],
  templateUrl: './email-verification.html',
  styleUrls: ['./email-verification.scss'],
})
export class EmailVerification implements OnInit {

  message = '';
  isLoading = true;
  isSuccess = false;

  constructor(
    private route: ActivatedRoute,
    private http: HttpClient,
    private router: Router,
    private cdr: ChangeDetectorRef
  ) { }

  ngOnInit(): void {
    const token = this.route.snapshot.queryParamMap.get('token');
    console.log('Token récupéré:', token);

    if (!token) {
      this.message = "Ce lien est invalide.";
      this.isLoading = false;
      return;
    }

    this.http.get(`${environment.apiUrl}/verify-email?token=${token}`)
      .subscribe({
        next: () => {
          this.isSuccess = true;
          this.isLoading = false;
          this.cdr.detectChanges();
        },
        error: (e: any) => {
          console.log('Erreur backend:', e);
          this.message = e.error?.message || "Lien invalide ou expiré.";
          this.isLoading = false;
          this.cdr.detectChanges();
        }
      });
  }
}