import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable } from 'rxjs';
import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class AuthService {

  private baseUrl = environment.apiUrl;

  // état connecté ou non
  private loggedIn = new BehaviorSubject<boolean>(this.hasToken());
  public isLoggedIn$ = this.loggedIn.asObservable();

  constructor(private http: HttpClient) { }

  // LOGIN
  login(username: string, password: string): Observable<any> {
    return new Observable(observer => {
      this.http.post(`${this.baseUrl}/login_check`, {
        username,
        password
      }).subscribe({
        next: (response: any) => {
          localStorage.setItem('token', response.token);
          this.loggedIn.next(true);
          observer.next(response);
          observer.complete();
        },
        error: err => observer.error(err)
      });
    });
  }

  // LOGOUT
  logout(): void {
    localStorage.removeItem('token');
    this.loggedIn.next(false);
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