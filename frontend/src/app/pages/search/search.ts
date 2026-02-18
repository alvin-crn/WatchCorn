import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { ApiService } from '../../services/api';
import { CommonModule } from '@angular/common';
import { ChangeDetectorRef } from '@angular/core';

@Component({
  selector: 'app-search',
  imports: [CommonModule],
  templateUrl: './search.html',
  styleUrls: ['./search.scss'],
})

export class Search implements OnInit {

  results: any[] = [];
  loading = false;

  constructor(
    private route: ActivatedRoute,
    private api: ApiService,
    private cd: ChangeDetectorRef
  ) {}

  ngOnInit() {
    this.route.queryParams.subscribe(params => {
      const query = params['q'];

      if (query) {
        this.fetchResults(query);
      }
    });
  }

  fetchResults(query: string) {
    this.loading = true;

    this.api.search(query).subscribe({
      next: (data) => {
        this.results = data.results;
        this.loading = false;
        this.cd.detectChanges();
      },
      error: (err) => {
        console.error(err);
        this.loading = false;
        this.cd.detectChanges();
      }
    });
  }
}