import { Component, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute } from '@angular/router';
import { AuthService } from '../../services/auth';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../../environments/environment';

@Component({
    selector: 'app-profile',
    standalone: true,
    imports: [CommonModule],
    templateUrl: './profile.html',
    styleUrls: ['./profile.scss', './loader-profile.scss'],
})
export class Profile {

    isOwnProfile: boolean | undefined;
    profileUsername: string | null = null;
    profileData: any;
    isLoading: boolean = true;
    error: string | null = null;

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
                    const token = localStorage.getItem('token');
                    console.log('Token actuel :', token);
                    this.isLoading = false;
                    this.cdr.markForCheck();
                }
            });
        });
    }

}
