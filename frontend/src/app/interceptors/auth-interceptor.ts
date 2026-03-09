import { HttpInterceptorFn, HttpRequest, HttpHandlerFn, HttpEvent } from '@angular/common/http';
import { inject } from '@angular/core';
import { Observable, throwError } from 'rxjs';
import { catchError, switchMap } from 'rxjs/operators';
import { AuthService } from '../services/auth';

export const AuthInterceptor: HttpInterceptorFn = (req: HttpRequest<unknown>, next: HttpHandlerFn): Observable<HttpEvent<unknown>> => {
  const authService = inject(AuthService);
  const token = localStorage.getItem('token');

  // Cloner la requête pour ajouter le JWT si présent
  const authReq = token
    ? req.clone({ setHeaders: { Authorization: `Bearer ${token}` } })
    : req;

  return next(authReq).pipe(
    catchError(err => {

      const isRefreshCall = req.url.includes('/auth/refreshToken') || req.headers.has('x-refresh-call');
      if (isRefreshCall) return next(req);

      if (err.status === 401 && !req.headers.has('x-refresh-retry')) {
        const refreshToken = localStorage.getItem('refresh_token');
        if (!refreshToken) {
          authService.logout();
          return throwError(() => err);
        }

        return authService.refreshToken(refreshToken).pipe(
          switchMap((response: any) => {
            // Créer une nouvelle requête avec le nouveau JWT
            const retryReq = req.clone({
              setHeaders: {
                Authorization: `Bearer ${response.token}`,
                'x-refresh-retry': 'true'
              }
            });
            return next(retryReq);
          }),
          catchError(() => {
            // Si le refresh échoue → logout
            authService.logout();
            return throwError(() => err);
          })
        );
      } else {
        return throwError(() => err);
      }

      // Toutes les autres erreurs → on passe
      return throwError(() => err);
    })
  );
};
