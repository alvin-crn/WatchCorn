import { provideRouter, Routes } from '@angular/router';
import { Home } from './pages/home/home';
import { Login } from './pages/login/login';
import { Register } from './pages/register/register';
import { Search } from './pages/search/search';


export const routes: Routes = [
  { path: '', component: Home },
  { path: 'connexion', component: Login },
  { path: 'inscription', component: Register },
  { path: 'recherche', component: Search },
];

export const appRouter = provideRouter(routes);