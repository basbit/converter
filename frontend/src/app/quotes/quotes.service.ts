import {Injectable} from '@angular/core';
import {catchError} from 'rxjs/operators';
import {Service} from "../app.service";
import {HttpClient, HttpParams} from "@angular/common/http";
import {Quote} from "../app.component";
import {MatSnackBar} from "@angular/material/snack-bar";

@Injectable({
  providedIn: 'root'
})

export class QuotesService extends Service {

  constructor(protected http: HttpClient, public snackBar: MatSnackBar) {
    super();
  }

  getQuotes(page: number, pageSize: number) {
    const params = new HttpParams().append('page', (page + 1).toString()).append('pageSize', pageSize.toString());
    return this.http.get<any>(`${this.url}/quotes`, {params: params})
      .pipe(
        catchError((error) => {
          if (undefined !== error.error.error) {
            this.snackBar.open(error.error.error, 'ok');
          }
          return Service.handleError(error);
        })
      );
  }

  updateQuote(elementId: number, rate: number) {
    return this.http.put<any>(`${this.url}/quotes/${elementId}`, {rate: rate})
      .pipe(
        catchError((error) => {
          if (undefined !== error.error.error) {
            this.snackBar.open(error.error.error, 'ok');
          }
          return Service.handleError(error);
        })
      );
  }

  deleteQuote(elementId: number) {
    return this.http.delete<any>(`${this.url}/quotes/${elementId}`)
      .pipe(
        catchError((error) => {
          if (undefined !== error.error.error) {
            this.snackBar.open(error.error.error, 'ok');
          }
          return Service.handleError(error);
        })
      );
  }

  createQuote(quote: Quote) {
    return this.http.put<any>(`${this.url}/quotes`, quote)
      .pipe(
        catchError((error) => {
          if (undefined !== error.error.error) {
            this.snackBar.open(error.error.error, 'ok');
          }
          return Service.handleError(error);
        })
      );
  }
}
