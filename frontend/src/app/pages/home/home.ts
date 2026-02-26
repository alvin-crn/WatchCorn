import { Component } from '@angular/core';
import { Loader2 } from '../../shared/loader-2/loader-2';

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [Loader2],
  templateUrl: './home.html',
  styleUrls: ['./home.scss'],
})
export class Home {}