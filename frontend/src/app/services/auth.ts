import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable, tap, throwError, catchError } from 'rxjs';
import { environment } from '../../environments/environment';
import { jwtDecode } from 'jwt-decode';

interface JwtPayload {
  username: string;
  roles: string[];
  isActived: boolean;
  exp: number;
  iat: number;
}

@Injectable({
  providedIn: 'root'
})

export class AuthService {

  // URL de base de l'API
  private baseUrl = environment.apiUrl;

  // Utilisateur courant
  private currentUserSubject = new BehaviorSubject<any>(null);
  public currentUser$ = this.currentUserSubject.asObservable();

  // État connecté ou non
  private authenticated = new BehaviorSubject<boolean>(this.hasToken());
  public isAuthenticated$ = this.authenticated.asObservable();

  constructor(private http: HttpClient) { }

  // Initialiser l'authentification au démarrage/refresh de l'application
  initAuth() {
    const token = localStorage.getItem('token');

    if (!token) return;

    this.refreshCurrentUser().subscribe({
      error: () => {
        this.logout();
      }
    });
  }

  // Fonction LOGIN
  login(username: string, password: string): Observable<any> {
    return this.http.post<any>(`${this.baseUrl}/login_check`, { username, password }).pipe(
      tap(response => {
        localStorage.setItem('token', response.token); // Stocker le token dans le localStorage
        localStorage.setItem('refresh_token', response.refresh_token); // Stocker le refresh token dans le localStorage

        // Vérifier si le compte est activé
        const payload = this.getDecodedToken();
        // Si le compte n'est pas activé, ne pas authentifier l'utilisateur
        if (payload && !payload.isActived) {
          this.authenticated.next(false);
          this.currentUserSubject.next(null);
          return;
        }

        this.refreshCurrentUser().subscribe(); // Rafraîchir les données de l'utilisateur courant après le login
      })
    );
  }

  // Fonction REFRESH TOKEN
  refreshToken(refreshToken: string): Observable<any> {
    // Si pas de refresh token, logout
    if (!refreshToken) {
      this.logout();
      return throwError(() => new Error('No refresh token'));
    }

    return this.http.post<any>(`${this.baseUrl}/auth/refreshToken`, { refresh_token: refreshToken }, { headers: { 'x-refresh-call': 'true' } }).pipe(
      catchError(err => {
        return throwError(() => err);
      }),
      tap(response => {
        if (response.token) { localStorage.setItem('token', response.token); } // Stocker le nouveau token
        if (response.refresh_token) { localStorage.setItem('refresh_token', response.refresh_token); } // Stocker le nouveau refresh token
      })
    );
  }

  // Fonction LOGOUT
  logout(): void {
    localStorage.removeItem('token');
    localStorage.removeItem('refresh_token');
    this.authenticated.next(false);
    this.currentUserSubject.next(null);
  }

  // CHECK TOKEN
  private hasToken(): boolean {
    const token = localStorage.getItem('token');
    if (!token) return false;

    return !this.isTokenExpired();
  }

  // === PUBLIC GETTER ===

  // Decoder le token JWT
  getDecodedToken(): JwtPayload | null {
    const token = localStorage.getItem('token');
    if (!token) return null;

    try {
      return jwtDecode<JwtPayload>(token);
    } catch {
      return null;
    }
  }

  // Vérifier si l'utilisateur est connecté
  isAuthenticated(): boolean {
    return this.authenticated.value;
  }

  // Vérifier si l'utilisateur a activé son compte
  isUserActived(): boolean {
    const payload = this.getDecodedToken();
    return payload?.isActived ?? false;
  }

  // Vérifier si le token est expiré
  isTokenExpired(): boolean {
    const payload = this.getDecodedToken();
    if (!payload) return true;

    const now = Math.floor(Date.now() / 1000);
    return payload.exp < now;
  }

  // Rafraîchir les données de l'utilisateur courant
  refreshCurrentUser(): Observable<any> {
    return this.http.get(`${this.baseUrl}/me`).pipe(
      tap(user => {
        this.currentUserSubject.next(user); // Mettre à jour les données de l'utilisateur courant
        this.authenticated.next(true); // Mettre à jour l'état d'authentification
      })
    );
  }
}