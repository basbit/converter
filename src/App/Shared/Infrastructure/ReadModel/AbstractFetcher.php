<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\ReadModel;

use Doctrine\DBAL\Connection;
use Knp\Component\Pager\PaginatorInterface;

abstract class AbstractFetcher
{
    protected Connection $connection;
    protected PaginatorInterface $paginator;
    protected ViewFactory $viewFactory;

    protected Sort $sort;

    protected int $page;
    protected int $pageSize;

    public function __construct(
        Connection $connection,
        PaginatorInterface $paginator,
        ViewFactory $viewFactory
    ) {
        $this->connection = $connection;
        $this->paginator = $paginator;
        $this->viewFactory = $viewFactory;
        $this->initViewFactory();
    }

    abstract public function initViewFactory(): void;

    public function setSort(Sort $sort): void
    {
        $this->sort = $sort;
    }

    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    public function setPageSize(int $pageSize): void
    {
        $this->pageSize = $pageSize;
    }

    public function getViewFactory(): ViewFactory
    {
        return $this->viewFactory;
    }
}
