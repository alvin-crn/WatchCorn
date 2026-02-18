import { Component } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { Router, RouterModule } from '@angular/router';
import { AuthService } from '../../services/auth';

@Component({
    selector: 'app-login',
    standalone: true,
    imports: [CommonModule, FormsModule, RouterModule],
    templateUrl: './login.html',
    styleUrls: ['./login.scss']
})
export class Login {

    username = '';
    password = '';
    errorMessage = '';

    constructor(
        private authService: AuthService,
        private router: Router
    ) { }

    onLogin() {
        this.authService.login(this.username, this.password).subscribe({
            next: () => {
                this.router.navigate(['/']);
            },
            error: () => {
                this.errorMessage = "Nom d'utilisateur ou mot de passe incorrect";
            }
        });
    }
}