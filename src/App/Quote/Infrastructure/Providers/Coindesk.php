<?php

namespace App\Quote\Infrastructure\Providers;

use App\Quote\Currency;
use App\Quote\Domain\QuotesProviderSpecification;
use App\Quote\Quote;
use DateTime;
use GuzzleHttp\Client;
use function PHPUnit\Framework\assertArrayHasKey;
use function PHPUnit\Framework\assertEquals;

class Coindesk implements QuotesProviderSpecification
{
    public function __construct(protected Currency $currency, protected string $coindeskUrl)
    {

    }

    public static function getName(): string
    {
        return 'coindesk';
    }

    public function getQuotes(): array
    {
        $client = new Client();
        $response = $client->request('GET', $this->coindeskUrl);

        assertEquals(200, $response->getStatusCode(), 'The provider did not return the correct answer');

        $quotes = [];
        $data = json_decode($response->getBody()->getContents(), true);

        assertArrayHasKey('bpi', $data, 'The provider did not return the correct answer');

        $currencyFrom = $this->currency->fromString('BTC');
        $currencyTo = $this->currency->fromString('USD');

        foreach ($data['bpi'] as $date => $rate) {
            $quotes[] = Quote::create(new DateTime($date), $currencyFrom, $currencyTo, (float)$rate);
        }

        return $quotes;
    }
}
