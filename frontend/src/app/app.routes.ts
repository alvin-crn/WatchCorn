import { provideRouter, Routes } from '@angular/router';
import { Home } from './pages/home/home';
import { Search } from './pages/search/search';


export const routes: Routes = [
  { path: '', component: Home },
  { path: 'recherche', component: Search },
];

export const appRouter = provideRouter(routes);