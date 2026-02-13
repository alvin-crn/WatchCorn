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

    // Méthode pour se connecter
    login(username: string, password: string) {
        return this.http.post(`${this.baseUrl}/login_check`, {
            username,
            password
        });
    }

    // Méthode pour rechercher des films
    search(query: string): Observable<any> {
        return this.http.get(`${this.baseUrl}/tmdb/search`, {
            params: { q: query }
        });
    }

}