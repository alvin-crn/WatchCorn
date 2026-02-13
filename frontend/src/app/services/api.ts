import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

@Injectable({
    providedIn: 'root'
})

export class ApiService {

    private baseUrl = environment.apiUrl;
    
    constructor(private http: HttpClient) { }

    // Méthode pour rechercher des films
    search(query: string): Observable<any> {
        return this.http.get(`${this.baseUrl}/tmdb/search`, {
            params: { q: query }
        });
    }

}