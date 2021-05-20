<?php

namespace App\Quote\Infrastructure\Providers;

use App\Quote\Currency;
use App\Quote\Domain\QuotesProviderSpecification;
use App\Quote\Quote;
use DateTime;
use Exception;
use GuzzleHttp\Client;
use SimpleXMLElement;
use function PHPUnit\Framework\assertEquals;

class Ecb implements QuotesProviderSpecification
{
    private const DEFAULT_CURRENCY_TO = 'EUR';

    public function __construct(protected Currency $currency, protected string $ecbUrl)
    {

    }

    public static function getName(): string
    {
        return 'ecb';
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws Exception
     */
    public function getQuotes(): array
    {
        $client = new Client();
        $response = $client->request('GET', $this->ecbUrl);

        assertEquals(200, $response->getStatusCode());

        $responseXml = simplexml_load_string($response->getBody()->getContents());

        $errors = [];
        foreach (libxml_get_errors() as $error) {
            $errors[] = $error->message;
        }

        if (count($errors) > 0) {
            throw new Exception("Error parsing XML: " . implode(", ", $errors));
        }

        if ($responseXml instanceof SimpleXMLElement) {
            return $this->parseXml($responseXml);
        }

        return [];
    }

    /**
     * @throws Exception
     */
    public function parseXml(SimpleXMLElement $xml): array
    {
        $quotes = [];

        $dateTime = new DateTime((string)$xml?->Cube?->Cube->attributes()->{'time'});

        $currencyFrom = $this->currency->fromString(self::DEFAULT_CURRENCY_TO);
        foreach ($xml?->Cube?->Cube?->Cube as $quote) {
            $currencyTo = $this->currency->fromString((string)$quote?->attributes()->{'currency'});
            $quotes[] = Quote::create($dateTime, $currencyFrom, $currencyTo,
                (float)$quote?->attributes()->{'rate'});
        }

        return $quotes;
    }
}
