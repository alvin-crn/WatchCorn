import { provideRouter, Routes } from '@angular/router';
// Pages
import { Home } from './pages/home/home';
import { Login } from './pages/login/login';
import { Register } from './pages/register/register';
import { Search } from './pages/search/search';
import { EmailVerification } from './pages/email-verification/email-verification';
import { SuccesRegister } from './pages/succes-register/succes-register';
import { UnactivedAccount } from './pages/unactived-account/unactived-account';
import { Profile } from './pages/profile/profile';
// Guards
import { guestGuard } from './guards/guest-guard';

export const routes: Routes = [
  { path: '', component: Home },
  { path: 'connexion', component: Login, canActivate: [guestGuard] },
  { path: 'inscription', component: Register },
  { path: 'compte-active', component: EmailVerification },
  { path: 'verifier-mon-compte', component: UnactivedAccount, canActivate: [guestGuard] },
  { path: 'inscription-reussie', component: SuccesRegister },
  { path: 'recherche', component: Search },
  
  { path: ':username', component: Profile },
];

export const appRouter = provideRouter(routes);