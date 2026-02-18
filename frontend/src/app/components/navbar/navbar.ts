import { Component } from '@angular/core';
import { RouterModule, Router } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { AuthService } from '../../services/auth';

@Component({
  selector: 'app-navbar',
  standalone: true,
  imports: [RouterModule, FormsModule, CommonModule],
  templateUrl: './navbar.html',
  styleUrls: ['./navbar.scss'],
})
export class NavbarComponent {
  searchQuery: string = '';

  constructor(private router: Router, public authService: AuthService) { }

  onSearch() {
    if (!this.searchQuery.trim()) return;

    this.router.navigate(['/recherche'], {
      queryParams: { q: this.searchQuery.trim() },
    });

    this.searchQuery = '';
  }

  logout() {
    this.authService.logout();
    this.router.navigate(['/']);
  }
}
