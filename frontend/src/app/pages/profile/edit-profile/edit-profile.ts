import { Component, Input, Output, EventEmitter, ChangeDetectorRef } from '@angular/core';
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

  private _show = false;

  editProfileForm!: FormGroup; // Formulaire de modification du profil
  loading = false; // Indique si la sauvegarde est en cours
  error: string | null = null; // Message d'erreur en cas de problème lors de la sauvegarde
  errorPhoto: string | null = null; // Message d'erreur spécifique pour la photo de profil

  username: string | null = null;

  // Variable pour la gestion de l'upload de la photo de profil
  selectedFile: File | null = null;
  previewUrl: string | null = null;
  currentPhotoUrl: string = '';
  mediaUrl = environment.mediaUrl; // URL de base pour les médias (photos de profil)

  @Input()
  set show(value: boolean) {
    this._show = value;

    if (value) {
      this.authService.currentUser$.pipe(take(1)).subscribe(currentUser => {
        this.editProfileForm?.reset({
          displayName: currentUser?.displayName || '',
        });
        this.currentPhotoUrl = this.mediaUrl + (currentUser?.profilePic || '');
        this.username = currentUser?.username || null;
        this.previewUrl = null;
        this.selectedFile = null;
      });
    }
  }

  get show(): boolean {
    return this._show;
  }

  @Output() showChange = new EventEmitter<boolean>(); // Événement pour notifier le parent de la fermeture du modal

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private http: HttpClient,
    private cdr: ChangeDetectorRef
  ) {
    this.editProfileForm = this.fb.group({
      displayName: ['', [Validators.required, Validators.maxLength(30), Validators.pattern(/\S/)]]
    });
  }

  onFileSelected(event: Event) {
    const input = event.target as HTMLInputElement;

    if (input.files && input.files.length > 0) {
      const file = input.files[0];
      const maxSize = 5 * 1024 * 1024; // 5 Mo

      if (file.size > maxSize) {
        this.errorPhoto = 'La photo ne doit pas dépasser 5 Mo';
        this.selectedFile = null;
        this.previewUrl = null;
        input.value = '';
        return;
      }

      this.errorPhoto = null;
      this.selectedFile = file;

      const reader = new FileReader();
      reader.onload = () => {
        this.previewUrl = reader.result as string;
        this.cdr.detectChanges();
      };
      reader.readAsDataURL(file);
    }
  }

  saveProfile() {
    if (this.editProfileForm.invalid) return;

    this.loading = true;
    this.error = null;

    const displayName = this.editProfileForm.value.displayName;
    const formData = new FormData();

    this.authService.currentUser$.pipe(take(1)).subscribe(currentUser => {
      const username = currentUser?.username;

      if (displayName !== currentUser?.displayName) {
        formData.append('displayName', displayName);
      }

      if (this.selectedFile) {
        formData.append('photo', this.selectedFile);
      }

      if (!formData.has('displayName') && !formData.has('photo')) {
        this.error = 'Aucune modification détectée';
        this.loading = false;
        return;
      }

      this.http.put(`${environment.apiUrl}/user/${username}`, formData).subscribe({
        next: (res) => {
          this.loading = false;
          this.close();
          this.authService.refreshCurrentUser().subscribe(); // Rafraîchir les données utilisateur après la mise à jour
        },
        error: (err) => {
          this.loading = false;
          this.error = err.error?.message || 'Erreur lors de la sauvegarde';
          console.error('Erreur lors de la sauvegarde du profil :', err);
          this.cdr.detectChanges();
        }
      });
    });
  }

  close() {
    this.previewUrl = null;
    this.selectedFile = null;
    this.error = null;
    this.errorPhoto = null;
    this._show = false;
    this.showChange.emit(false); // Notifier le parent que le modal est fermé
  }
}
