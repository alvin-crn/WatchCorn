import { Component, Input, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AuthService } from '../../../services/auth';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../../../environments/environment';
import { take } from 'rxjs/operators';
import { Loader1 } from '../../../shared/loader-1/loader-1';

@Component({
  selector: 'app-edit-profile',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, Loader1],
  templateUrl: './edit-profile.html',
  styleUrl: './edit-profile.scss',
})
export class EditProfile {

  @Input() show = false; // Contrôle de l'affichage du modal
  @Output() showChange = new EventEmitter<boolean>(); // Événement pour notifier le parent de la fermeture du modal

  editProfileForm!: FormGroup;
  loading = false;
  error: string | null = null;

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private http: HttpClient
  ) {
    this.authService.currentUser$.pipe(take(1)).subscribe(currentUser => {
      this.editProfileForm = this.fb.group({
        displayName: [currentUser?.displayName || '', [Validators.required, Validators.maxLength(30), Validators.pattern(/\S/)]],
        // photoProfile: ajout futur (upload)
      });
    });
  }

  saveProfile() {
    if (this.editProfileForm.invalid) return;

    this.loading = true;
    this.error = null;

    const payload = this.editProfileForm.value;

    this.authService.currentUser$.pipe(take(1)).subscribe(currentUser => {
      const username = currentUser?.username;

      this.http.put(`${environment.apiUrl}/user/${username}`, payload).subscribe({
        next: (res) => {
          this.loading = false;
          this.show = false;
          this.showChange.emit(this.show); // Notifier le parent que le modal est fermé
          this.authService.refreshCurrentUser().subscribe(); // Rafraîchir les données utilisateur après la mise à jour
        },
        error: (err) => {
          this.loading = false;
          this.error = err.error?.message || 'Erreur lors de la sauvegarde';
        }
      });
    });
  }

  close() {
    this.show = false;
    this.showChange.emit(this.show); // Notifier le parent que le modal est fermé
  }
}
