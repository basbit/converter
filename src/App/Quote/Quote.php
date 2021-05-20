<?php

namespace App\Quote;

use App\Quote\Infrastructure\Repository\QuoteRepository;
use App\Shared\Domain\AggregateRootInterface;
use App\Shared\Infrastructure\Traits\EventsTrait;
use App\Shared\Infrastructure\Traits\ObjectArrayConversionTrait;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=QuoteRepository::class)
 */
class Quote implements AggregateRootInterface
{
    use EventsTrait;
    use ObjectArrayConversionTrait;

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Currency", cascade={"persist"})
     * @ORM\JoinColumn(name="currency_from_id", referencedColumnName="id")
     */
    private Currency $currencyFrom;

    /**
     * @ORM\ManyToOne(targetEntity="Currency", cascade={"persist"})
     * @ORM\JoinColumn(name="currency_to_id", referencedColumnName="id")
     */
    private Currency $currencyTo;

    /**
     * @ORM\Column(type="datetime", name="created_at")
     */
    private ?DateTime $createdAt;

    /**
     * @ORM\Column(type="float")
     */
    private float $rate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRate(): ?float
    {
        return $this->rate;
    }

    public function setRate(float $rate): self
    {
        $this->rate = $rate;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime|null $createdAt
     */
    public function setCreatedAt(?DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return Currency
     */
    public function getCurrencyTo(): Currency
    {
        return $this->currencyTo;
    }

    /**
     * @param Currency $currencyTo
     */
    public function setCurrencyTo(Currency $currencyTo): void
    {
        $this->currencyTo = $currencyTo;
    }

    /**
     * @return Currency
     */
    public function getCurrencyFrom(): Currency
    {
        return $this->currencyFrom;
    }

    /**
     * @param Currency $currencyFrom
     */
    public function setCurrencyFrom(Currency $currencyFrom): void
    {
        $this->currencyFrom = $currencyFrom;
    }

    public static function create(
        DateTime $dateTime,
        Currency $currencyFrom,
        Currency $currencyTo,
        float $rate
    ): self {
        $quote = new self();
        $quote->currencyFrom = $currencyFrom;
        $quote->currencyTo = $currencyTo;
        $quote->createdAt = $dateTime;
        $quote->rate = $rate;

        return $quote;
    }

    public function getReverseQuote(): self
    {
        $revQuote = clone $this;
        $revQuote->setCurrencyFrom($this->getCurrencyTo());
        $revQuote->setCurrencyTo($this->getCurrencyFrom());
        $revQuote->setRate(1 / $this->getRate());

        return $revQuote;
    }

    public function calcRateFromQuote(Quote $fromQuote): self
    {
        $result = clone $this;
        $result->setCurrencyFrom($fromQuote->getCurrencyFrom());
        $result->setRate($fromQuote->getRate() * $result->getRate());

        return $result;
    }
}
