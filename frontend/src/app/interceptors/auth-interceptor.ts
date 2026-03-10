import { HttpInterceptorFn, HttpRequest, HttpHandlerFn, HttpEvent } from '@angular/common/http';
import { inject } from '@angular/core';
import { Observable, throwError, BehaviorSubject } from 'rxjs';
import { catchError, switchMap, filter, take } from 'rxjs/operators';
import { AuthService } from '../services/auth';

// Variables globales pour gérer le verrou de refresh
let isRefreshing = false;
const refreshTokenSubject = new BehaviorSubject<string | null>(null);

export const AuthInterceptor: HttpInterceptorFn = (req: HttpRequest<unknown>, next: HttpHandlerFn): Observable<HttpEvent<unknown>> => {
  const authService = inject(AuthService);
  const token = localStorage.getItem('token');

  const isRefreshCall = req.url.includes('/auth/refreshToken') || req.headers.has('x-refresh-call');
  if (isRefreshCall) {
    return next(req);
  }

  // Cloner la requête pour ajouter le JWT si présent
  const authReq = token
    ? req.clone({ setHeaders: { Authorization: `Bearer ${token}` } })
    : req;

  return next(authReq).pipe(
    catchError(err => {

      const isRefreshCall = authReq.url.includes('/auth/refreshToken') || authReq.headers.has('x-refresh-call');
      if (isRefreshCall) return throwError(() => err);

      if (err.status === 401 && !authReq.headers.has('x-refresh-retry')) {
        const refreshToken = localStorage.getItem('refresh_token');

        if (!refreshToken) {
          authService.logout();
          return throwError(() => err);
        }

        // Si un refresh est déjà en cours
        if (isRefreshing) {
          return refreshTokenSubject.pipe(
            filter((token): token is string => token !== null),
            take(1),
            switchMap((newToken) => {
              const retryReq = authReq.clone({
                setHeaders: {
                  Authorization: `Bearer ${newToken}`,
                  'x-refresh-retry': 'true'
                }
              });
              return next(retryReq);
            })
          );
        }

        // Premier refresh
        isRefreshing = true;
        refreshTokenSubject.next(null);

        return authService.refreshToken(refreshToken).pipe(
          switchMap((response: any) => {
            isRefreshing = false;
            refreshTokenSubject.next(response.token);

            // Créer une nouvelle requête avec le nouveau JWT
            const retryReq = authReq.clone({
              setHeaders: {
                Authorization: `Bearer ${response.token}`,
                'x-refresh-retry': 'true'
              }
            });
            return next(retryReq);
          }),
          catchError(() => {
            // Si le refresh échoue → logout
            isRefreshing = false;
            authService.logout();
            return throwError(() => err);
          })
        );
      } else {
        return throwError(() => err);
      }
    })
  );
};
