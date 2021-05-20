import {Component, OnInit} from '@angular/core';
import {ConverterService} from './converter.service';
import {Subject} from "rxjs";
import {debounceTime} from "rxjs/operators";
import {IConverterForm} from "./converter.model";

@Component({
  selector: 'app-converter',
  templateUrl: './converter.component.html',
  styleUrls: ['./converter.component.css']
})

export class ConverterComponent implements OnInit {

  model: IConverterForm = {};

  modelChanged: Subject<IConverterForm> = new Subject<IConverterForm>();
  currencies: any = [];

  constructor(private converterService: ConverterService) {
    this.modelChanged.pipe(
      debounceTime(500))
      .subscribe(model => {
        if (model.from_currency && model.to_currency && (model.from_amount || model.to_amount)) {
          this.converterService.convert(model).subscribe(data => {
            model.is_reverse ? model.from_amount = data.result : model.to_amount = data.result;
            model.rate = model.is_reverse ? 1 / data.rate : data.rate;
          });
        }
      });
  }

  ngOnInit(): void {
    this.converterService.getCurrencies().subscribe((data) => this.currencies = data.items !== undefined ? data.items : []);
  }

  setCurrency(field: string, value: number): void {
    this.model[field] = value;
    this.modelChanged.next(this.model);
  }

  setAmount(field: string, value: number): void {
    this.model['is_reverse'] = field === 'to_amount';
    this.model[field] = value;
    this.modelChanged.next(this.model);
  }
}
