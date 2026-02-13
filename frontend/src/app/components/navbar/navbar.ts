import { Component } from '@angular/core';
import { RouterModule, Router } from '@angular/router';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-navbar',
  standalone: true,
  imports: [RouterModule, FormsModule],
  templateUrl: './navbar.html',
  styleUrls: ['./navbar.scss'],
})
export class NavbarComponent {
  searchQuery: string = '';

  constructor(private router: Router) { }

  onSearch() {
    if (!this.searchQuery.trim()) return;

    this.router.navigate(['/recherche'], {
      queryParams: { q: this.searchQuery.trim() },
    });

    this.searchQuery = '';
  }
}
