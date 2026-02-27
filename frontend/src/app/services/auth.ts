import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable, tap } from 'rxjs';
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

  // Fonction LOGIN
  login(username: string, password: string): Observable<any> {
    return this.http.post<any>(`${this.baseUrl}/login_check`, {
      username,
      password
    }).pipe(
      tap(response => {
        localStorage.setItem('token', response.token);
        const payload = this.getDecodedToken();
        if (payload && !payload.isActived) {
          this.authenticated.next(false);
          this.currentUserSubject.next(null);
          return;
        }
        this.authenticated.next(true);
        this.getUserInfo();
      })
    );
  }

  // Fonction LOGOUT
  logout(): void {
    localStorage.removeItem('token');
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

  // Get les infos de base de l'utilisateur courant
  getUserInfo() {
    return this.http.get(`${this.baseUrl}/me`).subscribe({
      next: (user) => this.currentUserSubject.next(user),
      error: () => this.currentUserSubject.next(null),
    });
  }
}