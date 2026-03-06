import { Component, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute } from '@angular/router';
import { AuthService } from '../../services/auth';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../../environments/environment';
import { EditProfile } from './edit-profile/edit-profile';

@Component({
    selector: 'app-profile',
    standalone: true,
    imports: [CommonModule, EditProfile],
    templateUrl: './profile.html',
    styleUrls: ['./profile.scss', './loader-profile.scss'],
})
export class Profile {

    mediaUrl = environment.mediaUrl; // URL de base pour les médias (photos de profil)
    isOwnProfile: boolean | undefined; // Indique si le profil affiché est celui de l'utilisateur connecté
    profileUsername: string | null = null; // Nom d'utilisateur du profil affiché
    profileData: any; // Données du profil récupérées depuis l'API
    isLoading: boolean = true; // Indique si les données du profil sont en cours de chargement
    error: string | null = null; // Message d'erreur en cas de problème lors de la récupération des données du profil
    showEditForm: boolean = false; // Contrôle l'affichage du formulaire de modification du profil

    constructor(
        private route: ActivatedRoute,
        private authService: AuthService,
        private http: HttpClient,
        private cdr: ChangeDetectorRef
    ) { }

    ngOnInit() {
        this.profileUsername = this.route.snapshot.paramMap.get('username');

        this.authService.currentUser$.subscribe(currentUser => {
            this.isOwnProfile = currentUser?.username === this.profileUsername;

            // Appel API pour récupérer les données du profil
            this.http.get(`${environment.apiUrl}/user/${this.profileUsername}`).subscribe({
                next: (data) => {
                    this.profileData = data;
                    this.isLoading = false;
                    this.cdr.markForCheck();
                },
                error: (err) => {
                    this.error = err?.error?.message || 'Erreur inconnue';
                    this.isLoading = false;
                    this.cdr.markForCheck();
                }
            });
        });
    }

}
