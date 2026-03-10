import { Component, OnInit, signal } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { NavbarComponent } from './components/navbar/navbar';
import { AuthService } from './services/auth';

@Component({
  selector: 'app-root',
  imports: [RouterOutlet, NavbarComponent],
  templateUrl: './app.html',
  styleUrl: './app.scss'
})
export class App implements OnInit {
  protected readonly title = signal('watchcorn');

  constructor(public authService: AuthService) {
  }

  ngOnInit() {
    // Initialiser l'authentification au démarrage de l'application
    this.authService.initAuth();
  }
}
