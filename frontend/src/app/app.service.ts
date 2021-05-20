import {HttpErrorResponse, HttpHeaders} from "@angular/common/http";
import {throwError} from "rxjs";

export class Service {

  protected url = 'http://localhost:835/api/v1';

  protected httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  };

  protected static handleError(error: HttpErrorResponse) {
    if (error.status === 0) {
      console.error('An error occurred:', error.error);
    } else {
      console.error(
        `Backend returned code ${error.status}, ` +
        `body was: ${error.error}`);
    }
    return throwError('Something bad happened; please try again later.');
  }

}
