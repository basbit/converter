<?php

declare(strict_types=1);

namespace App\Quote\Infrastructure\ReadModel\Currency;

use App\Shared\Infrastructure\ReadModel\AbstractFetcher;
use Knp\Component\Pager\Pagination\PaginationInterface;

class Fetcher extends AbstractFetcher
{
    /**
     * @return PaginationInterface<array>
     */
    public function all(): PaginationInterface
    {
        $qb = $this->connection->createQueryBuilder()
            ->select($this->defaultFieldSet())
            ->from('currency')
            ->orderBy($this->sort->getField(), $this->sort->getDirection());

        return $this->paginator->paginate($qb, $this->page, $this->pageSize, [
            'viewFactory' => $this->viewFactory,
        ]);
    }

    public function initViewFactory(): void
    {
        $this->viewFactory->setItemViewModelClass(CurrencyView::class);
        $this->viewFactory->setListViewModelClass(CurrencyView::class);
    }

    private function defaultFieldSet(): array
    {
        return [
            'id',
            'name'
        ];
    }
}
