<?php

declare(strict_types=1);

namespace App\Quote\Infrastructure\ReadModel\Quote;

use App\Shared\Infrastructure\ReadModel\AbstractFetcher;
use Knp\Component\Pager\Pagination\PaginationInterface;

class Fetcher extends AbstractFetcher
{
    public function all(): PaginationInterface
    {
        $query = $this->connection->createQueryBuilder()
            ->select($this->defaultFieldSet())
            ->from('quote', 'q')
            ->leftJoin('q', 'currency', 'fc', 'q.currency_from_id = fc.id')
            ->leftJoin('q', 'currency', 'tc', 'q.currency_to_id = tc.id')
            ->orderBy('q.' . $this->sort->getField(), $this->sort->getDirection());

        return $this->paginator->paginate($query, $this->page, $this->pageSize, [
            'viewFactory' => $this->viewFactory,
        ]);
    }

    public function initViewFactory(): void
    {
        $this->viewFactory->setItemViewModelClass(QuoteView::class);
        $this->viewFactory->setListViewModelClass(QuoteView::class);
    }

    private function defaultFieldSet(): array
    {
        return [
            'q.id',
            'q.rate',
            'q.created_at as "createdAt"',
            'fc.name as "currencyFrom"',
            'tc.name as "currencyTo"',
        ];
    }
}
