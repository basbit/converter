<?php

namespace App\Quote\Infrastructure\Repository;

use App\Quote\Currency;
use App\Quote\Quote;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @method Quote|null find($id, $lockMode = null, $lockVersion = null)
 * @method Quote|null findOneBy(array $criteria, array $orderBy = null)
 * @method Quote[]    findAll()
 * @method Quote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuoteRepository extends ServiceEntityRepository
{
    private const DEFAULT_MEDIATOR_CURRENCY = 'USD';

    public function __construct(
        ManagerRegistry $registry,
        protected Currency $currency,
        protected EntityManagerInterface $em
    ) {
        parent::__construct($registry, Quote::class);
    }

    public function save(Quote $action): void
    {
        $this->em->persist($action);
    }

    public function remove(int $id): bool
    {
        $quote = $this->find($id);

        if ($quote) {
            $this->em->remove($quote);
            $this->em->flush();
            return true;
        }

        return false;
    }

    public function prepareFindBySqlQuery(string $sql): NativeQuery
    {
        $rsm = new ResultSetMapping;

        $rsm->addEntityResult(Quote::class, 'q');
        $rsm->addFieldResult('q', 'id', 'id');
        $rsm->addFieldResult('q', 'rate', 'rate');
        $rsm->addMetaResult('q', 'currency_from_id', 'currency_from_id');
        $rsm->addMetaResult('q', 'currency_to_id', 'currency_to_id');

        return $this->em->createNativeQuery($sql, $rsm);
    }

    public function findAllQuotes(array $currencies, DateTime $dateTime): array
    {
        $dateTime = $dateTime ?: new DateTime();

        $sql = 'SELECT * FROM ' .
            '(SELECT DISTINCT ON (q.currency_from_id, q.currency_to_id) q.id, q.rate, q.currency_to_id, q.currency_from_id ' .
            'FROM quote q LEFT JOIN currency tc ON q.currency_to_id = tc.id ' .
            'WHERE q.created_at <= :dateTime AND tc.name IN (:currencies) ' .
            'ORDER BY q.currency_from_id DESC, q.currency_to_id DESC, q.created_at DESC) fc' .
            ' UNION SELECT * FROM ' .
            '(SELECT DISTINCT ON (q.currency_from_id, q.currency_to_id) q.id, (1 / q.rate) as rate, q.currency_from_id, q.currency_to_id ' .
            'FROM quote q LEFT JOIN currency tc ON q.currency_from_id = tc.id ' .
            'WHERE q.created_at <= :dateTime AND tc.name IN (:currencies) ' .
            'ORDER BY q.currency_from_id DESC, q.currency_to_id DESC, q.created_at DESC) tc';

        $query = $this->prepareFindBySqlQuery($sql);

        $query->setParameter('dateTime', $dateTime->format('Y-m-d H:i:s'));
        $query->setParameter('currencies', $currencies, Connection::PARAM_STR_ARRAY);

        $quotes = $query->getResult();
        $result = [];
        /** @var Quote $quote */
        foreach ($quotes as $quote) {
            $result[$quote->getCurrencyFrom()->getName()][$quote->getCurrencyTo()->getName()] = $quote;
            if (!isset($result[$quote->getCurrencyTo()->getName()][$quote->getCurrencyFrom()->getName()])) {
                $result[$quote->getCurrencyTo()->getName()][$quote->getCurrencyFrom()->getName()] = $quote->getReverseQuote();
            }
        }

        return $result;
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws Exception
     */
    public function findQuote(string $currencyFrom, string $currencyTo, DateTime $dateTime): ?Quote
    {
        /** @var Quote|null $quote */
        $quote = $this->createQueryBuilder('q')
            ->select('q')
            ->leftJoin('q.currencyFrom', 'fc')
            ->leftJoin('q.currencyTo', 'tc')
            ->where('q.createdAt <= :date')
            ->andWhere('fc.name = :from')
            ->andWhere('tc.name = :to')
            ->setParameters(['date' => $dateTime, 'from' => $currencyFrom, 'to' => $currencyTo])
            ->orderBy('q.createdAt', 'DESC')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();

        return $quote ?? $this->findQuoteThroughtAnotherCurrency($currencyFrom, $currencyTo, $dateTime);
    }

    /**
     * @throws Exception
     */
    public function findQuoteThroughtAnotherCurrency(
        string $currencyFrom,
        string $currencyTo,
        DateTime $dateTime
    ): ?Quote {

        /** @var Quote[] $quotes */
        $quotes = $this->findAllQuotes([$currencyFrom, $currencyTo, self::DEFAULT_MEDIATOR_CURRENCY], $dateTime);

        return $this->calculateRate($currencyFrom, $currencyTo, $quotes);
    }

    /**
     * @throws Exception
     */
    private function calculateRate(string $currencyFrom, string $currencyTo, array $quotes): Quote
    {
        if (!isset($quotes[$currencyFrom])) {
            throw new Exception('Currency "FROM" not found');
        }
        if (!isset($quotes[$currencyTo])) {
            throw new Exception('Currency "TO" not found');
        }

        if (isset($quotes[$currencyFrom][$currencyTo])) {
            return $quotes[$currencyFrom][$currencyTo];
        }

        if (isset($quotes[$currencyTo][$currencyFrom])) {
            return $quotes[$currencyTo][$currencyFrom]->getReverseQuote();
        }

        $resultQuote = null;
        /** @var Quote $fromQuote */
        foreach ($quotes[$currencyFrom] as $key => $fromQuote) {
            if (isset($quotes[$key][$currencyTo])) {
                $resultQuote = $quotes[$key][$currencyTo]->calcRateFromQuote($fromQuote);
                break;
            }
        }

        if (!$resultQuote) {
            $fromQuote = $this->calculateRate($currencyFrom, self::DEFAULT_MEDIATOR_CURRENCY, $quotes);
            $toQuote = $this->calculateRate(self::DEFAULT_MEDIATOR_CURRENCY, $currencyTo, $quotes);
            $resultQuote = $toQuote->calcRateFromQuote($fromQuote);
        }

        return $resultQuote;
    }
}
