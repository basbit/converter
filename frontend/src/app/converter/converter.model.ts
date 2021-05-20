export interface Map {
  [key: string]: number | string | undefined | null | boolean
}

export interface IConverterForm extends Map {
  to_amount?: number;
  from_currency?: string;
  from_amount?: number;
  to_currency?: string;
  is_reverse?: boolean;
  rate?: number;
}
