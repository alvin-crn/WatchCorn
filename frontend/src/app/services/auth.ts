import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable, tap } from 'rxjs';
import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class AuthService {

  // URL de base de l'API
  private baseUrl = environment.apiUrl;

  // Utilisateur courant
  private currentUserSubject = new BehaviorSubject<any>(null);
  public currentUser$ = this.currentUserSubject.asObservable();

  // état connecté ou non
  private loggedIn = new BehaviorSubject<boolean>(this.hasToken());
  public isLoggedIn$ = this.loggedIn.asObservable();

  constructor(private http: HttpClient) { }

  // LOGIN
  login(username: string, password: string): Observable<any> {
    return this.http.post<any>(`${this.baseUrl}/login_check`, {
      username,
      password
    }).pipe(
      tap(response => {
        localStorage.setItem('token', response.token);
        this.loggedIn.next(true);
        this.loadUser();
      })
    );
  }

  // User info (lite)
  loadUser() {
    return this.http.get(`${this.baseUrl}/me`).subscribe({
      next: (user) => this.currentUserSubject.next(user),
      error: () => this.currentUserSubject.next(null),
    });
  }

  // LOGOUT
  logout(): void {
    localStorage.removeItem('token');
    this.loggedIn.next(false);
    this.currentUserSubject.next(null);
  }

  // CHECK TOKEN
  private hasToken(): boolean {
    return !!localStorage.getItem('token');
  }

  // PUBLIC GETTER
  isLoggedIn(): boolean {
    return this.loggedIn.value;
  }
}