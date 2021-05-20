<?php
declare(strict_types=1);

namespace App\Shared\Infrastructure\Pagination;

use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Query\QueryBuilder;
use Knp\Component\Pager\Event\ItemsEvent;
use Knp\Component\Pager\Event\Subscriber\Paginate\Doctrine\DBALQueryBuilderSubscriber as Base;

class DBALQueryBuilderSubscriber extends Base
{
    public function items(ItemsEvent $event): void
    {
        if ($event->target instanceof QueryBuilder) {
            /** @var QueryBuilder $target */
            $target = $event->target;

            // count results
            $qb = clone $target;

            //reset count orderBy since it can break query and slow it down
            $qb->resetQueryPart('orderBy');

            // get the query
            $sql = $qb->getSQL();

            $qb
                ->resetQueryParts()
                ->select('count(*) as cnt')
                ->from('(' . $sql . ')', 'dbal_count_tbl');

            /** @var Statement<array> $statement */
            $statement = $qb->execute();
            $event->count = $statement->fetchColumn(0);

            // if there is results
            $event->items = [];
            if ($event->count) {
                $qb = clone $target;
                $qb
                    ->setFirstResult($event->getOffset())
                    ->setMaxResults($event->getLimit());

                /** @var Statement<array> $statement */
                $statement = $qb->execute();
                $items = $statement->fetchAllAssociative();

                foreach ($items as $item) {
                    $deserializedItem = null;
                    if ($event->options['viewFactory'] ?? false) {
                        $deserializedItem = $event->options['viewFactory']->buildListView($item);
                    }
                    $event->items[] = $deserializedItem ?: $item;
                }
            }

            $event->stopPropagation();
        }
    }
}
