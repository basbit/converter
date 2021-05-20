import {Component} from '@angular/core';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent {
  title = 'converter';
}

export class Quote {
  constructor(
    public currencyFrom: string | null,
    public currencyTo: string | null,
    public rate: number | null,
    public date: number | null
  ) {
  }
}
