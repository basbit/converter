# Symfony 5 Currency converter

## Implementations

- [x] Environment in Docker
- [x] Rest API
- [x] Angular
- [x] Swagger API Doc

## Use Cases

- [x] Import quotes from ECB and Coinbase (
  https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml
  https://api.coindesk.com/v1/bpi/historical/close.json
  )
- [x] CRUD quotes
- [x] Currency converter

## Stack

- PHP 8
- Postgres 11
- Angular 11

## Commands

|    Action        	|     Command    |
|------------------	|---------------	|
|  Setup 	          | `make start`   |
|  Run Tests       	| `make phpunit` |
|  Run Import       	| `make import` |

