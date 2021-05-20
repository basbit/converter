<?php

namespace App\Quote;

use App\Quote\Domain\CurrencyRepositoryInterface;
use App\Quote\Infrastructure\Repository\CurrencyRepository;
use App\Shared\Domain\AggregateRootInterface;
use App\Shared\Infrastructure\Traits\EventsTrait;
use App\Shared\Infrastructure\Traits\ObjectArrayConversionTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CurrencyRepository::class)
 */
class Currency implements AggregateRootInterface
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
     * @ORM\Column(type="string", length=20, unique=true, nullable=false)
     */
    private string $name;

    public function __construct(protected CurrencyRepositoryInterface $repository)
    {

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function fromString(string $name): self
    {
        $currency = $this->repository->get($name);
        if (!$currency) {
            $currency = new self($this->repository);
            $currency->setName(strtoupper($name));
        }

        return $currency;
    }

    public function toString(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function jsonSerialize(): string
    {
        return $this->toString();
    }
}
