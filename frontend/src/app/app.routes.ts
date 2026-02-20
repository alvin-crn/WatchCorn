import { provideRouter, Routes } from '@angular/router';
import { Home } from './pages/home/home';
import { Login } from './pages/login/login';
import { Register } from './pages/register/register';
import { Search } from './pages/search/search';
import { EmailVerification } from './pages/email-verification/email-verification';
import { SuccesRegister } from './pages/succes-register/succes-register';

export const routes: Routes = [
  { path: '', component: Home },
  { path: 'connexion', component: Login },
  { path: 'inscription', component: Register },
  { path: 'recherche', component: Search },
  { path: 'actived-account', component: EmailVerification },
  { path: 'inscription-reussie', component: SuccesRegister },
];

export const appRouter = provideRouter(routes);