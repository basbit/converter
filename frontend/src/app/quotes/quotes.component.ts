import {Component, OnInit} from '@angular/core';
import {QuotesService} from "./quotes.service";
import {MatTableDataSource} from '@angular/material/table';
import {FormControl, FormGroup, Validators} from "@angular/forms";
import {Quote} from "../app.component";
import {PageEvent} from "@angular/material/paginator";

@Component({
  selector: 'app-quotes',
  templateUrl: './quotes.component.html',
  styleUrls: ['./quotes.component.css']
})

export class QuotesComponent implements OnInit {

  isEdit: any = {};

  quotesCount = 0;
  pageSize = 25;
  pageSizeOptions: number[] = [5, 10, 25, 100];

  quotes = new MatTableDataSource();
  displayedColumns: string[] = ['currencyFrom', 'currencyTo', 'rate', 'date', 'actions'];
  maxDate: number = Date.now();
  newQuote: Quote = {currencyFrom: null, currencyTo: null, rate: null, date: null};
  createFrom: FormGroup = new FormGroup({
    currencyFrom: new FormControl(this.newQuote.currencyFrom, [
      Validators.required,
      Validators.pattern(/^[a-zA-Z]{3,4}$/i)
    ]),
    currencyTo: new FormControl(this.newQuote.currencyTo, [
      Validators.required,
      Validators.pattern(/^[a-zA-Z]{3,4}$/i)
    ]),
    rate: new FormControl(this.newQuote.rate, [
      Validators.required,
      Validators.minLength(1),
      Validators.pattern(/^[0-9,.]$/)
    ]),
    date: new FormControl(this.newQuote.date, [
      Validators.required,
    ])
  });

  constructor(private quotesService: QuotesService) {
  }

  ngOnInit(): void {
    this.quotesService.getQuotes(1, this.pageSize).subscribe((data) => {
      this.quotesCount = data.pagination.total;
      this.pageSize = data.pagination.perPage;
      this.quotes.data = data.items !== undefined ? data.items : []
    });
  }

  handlePageEvent(event: PageEvent) {
    this.quotesService.getQuotes(event.pageIndex, event.pageSize).subscribe((data) => {
      this.quotesCount = data.pagination.total;
      this.pageSize = data.pagination.perPage;
      this.quotes.data = data.items !== undefined ? data.items : []
    });
  }

  applyFilter(event: Event) {
    const filterValue = (event.target as HTMLInputElement).value;
    this.quotes.filter = filterValue.trim().toLowerCase();
  }

  onSave(elementId: number) {
    let rate = (<HTMLInputElement>document.getElementById(`input_rate_${elementId}`)).value;
    this.quotesService.updateQuote(elementId, rate ? parseFloat(rate) : 0).subscribe(result => {
      let index = this.getElementIndexById(elementId);
      // @ts-ignore
      this.quotes.data.slice(index)[0].rate = rate;
      this.quotes._updateChangeSubscription();
      this.isEdit[elementId] = false;
    });
  }

  onEdit(elementId: number) {
    this.isEdit[elementId] = true;
  }

  onCancel(elementId: number) {
    this.isEdit[elementId] = false;
  }

  onDelete(elementId: number) {
    let index = this.getElementIndexById(elementId);

    this.quotesService.deleteQuote(elementId).subscribe(result => {
      if (index !== -1) {
        this.quotes.data.splice(index, 1);
        this.quotes._updateChangeSubscription();
      }
    });

  }

  private getElementIndexById(elementId: number) {

    let elementIndex = -1;
    for (let i = 0; i < this.quotes.data.length; i++) {
      let item = this.quotes.data.slice(i)[0];
      // @ts-ignore
      if (item.id === elementId) {
        elementIndex = i;
      }
    }

    return elementIndex;
  }

  onCreate() {
    /*if (this.createFrom.invalid) {
      this.createFrom.markAllAsTouched();
      return;
    }*/

    this.newQuote.currencyFrom = this.newQuote.currencyFrom ? this.newQuote.currencyFrom.toUpperCase() : null;
    this.newQuote.currencyTo = this.newQuote.currencyTo ? this.newQuote.currencyTo.toUpperCase() : null;
    this.quotesService.createQuote(this.newQuote)
      .subscribe(quote => this.quotes.data.push(quote));
    this.quotes._updateChangeSubscription();
    this.newQuote = {currencyFrom: null, currencyTo: null, rate: null, date: null};

  }

  isFieldValid(field: string) {
    // @ts-ignore
    return !this.createFrom.get(field).valid && this.createFrom.get(field).touched;
  }
}
