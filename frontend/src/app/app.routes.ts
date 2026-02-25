import { provideRouter, Routes } from '@angular/router';
import { Home } from './pages/home/home';
import { Login } from './pages/login/login';
import { Register } from './pages/register/register';
import { Search } from './pages/search/search';
import { EmailVerification } from './pages/email-verification/email-verification';
import { SuccesRegister } from './pages/succes-register/succes-register';
import { UnactivedAccount } from './pages/unactived-account/unactived-account';

export const routes: Routes = [
  { path: '', component: Home },
  { path: 'connexion', component: Login },
  { path: 'inscription', component: Register },
  { path: 'recherche', component: Search },
  { path: 'compte-active', component: EmailVerification },
  { path: 'verifier-mon-compte', component: UnactivedAccount },
  { path: 'inscription-reussie', component: SuccesRegister },
];

export const appRouter = provideRouter(routes);