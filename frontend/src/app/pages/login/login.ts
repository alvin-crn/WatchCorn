import { Component } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { Router, RouterModule } from '@angular/router';
import { AuthService } from '../../services/auth';
import { Loader1 } from '../../shared/loader-1/loader-1';
import { ChangeDetectorRef } from '@angular/core';

@Component({
    selector: 'app-login',
    standalone: true,
    imports: [CommonModule, FormsModule, RouterModule, Loader1],
    templateUrl: './login.html',
    styleUrls: ['./login.scss']
})
export class Login {

    username = '';
    password = '';
    errorMessage = '';
    isLoading = false;

    constructor(
        private authService: AuthService,
        private router: Router,
        private cdr: ChangeDetectorRef
    ) { }

    onLogin() {
        this.isLoading = true;
        this.cdr.detectChanges();
        this.authService.login(this.username, this.password).subscribe({
            next: () => {
                this.isLoading = false;
                this.cdr.detectChanges();
                if (!this.authService.isUserActived()) {
                    const payload = this.authService.getDecodedToken();
                    if (payload?.username) { sessionStorage.setItem('account_to_verify', payload.username); }
                    localStorage.removeItem('token');
                    this.router.navigate(['/verifier-mon-compte']);
                    return;
                }
                this.router.navigate(['/']);
            },
            error: () => {
                this.isLoading = false;
                this.errorMessage = "Nom d'utilisateur ou mot de passe incorrect";
                this.cdr.detectChanges();
            }
        });
    }
}