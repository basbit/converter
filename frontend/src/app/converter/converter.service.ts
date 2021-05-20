import {Injectable} from '@angular/core';
import {catchError, retry} from 'rxjs/operators';
import {IConverterForm} from "./converter.model";
import {Service} from "../app.service";
import {HttpClient} from "@angular/common/http";
import {MatSnackBar} from "@angular/material/snack-bar";

@Injectable({
  providedIn: 'root'
})

export class ConverterService extends Service {

  constructor(protected http: HttpClient, public snackBar: MatSnackBar) {
    super();
  }

  public convert(model: IConverterForm) {
    // this.items.push(product);
    return this.http.post<any>(`${this.url}/exchange`, {
      from: model.is_reverse ? model.to_currency : model.from_currency,
      to: model.is_reverse ? model.from_currency : model.to_currency,
      amount: model.is_reverse ? model.to_amount : model.from_amount
    }, this.httpOptions).pipe(
      retry(3),
      catchError((error) => {
        if(undefined !== error.error.error) {
          this.snackBar.open(error.error.error, 'ok');
        }
        return Service.handleError(error);
      })
    );
  }

  public getCurrencies() {
    return this.http.get<any>(`${this.url}/currencies`)
      .pipe(
        retry(3),
        catchError((error) => {
          if(undefined !== error.error.error) {
            this.snackBar.open(error.error.error, 'ok');
          }
          return Service.handleError(error);
        })
      );
  }
}
