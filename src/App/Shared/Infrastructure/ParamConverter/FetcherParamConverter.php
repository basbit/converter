<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\ParamConverter;

use App\Shared\Infrastructure\ReadModel\AbstractFetcher;
use App\Shared\Infrastructure\ReadModel\FetchersCollection;
use App\Shared\Infrastructure\ReadModel\Sort;
use App\Shared\Infrastructure\ReadModel\ViewFactory;
use Doctrine\DBAL\Connection;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use ReflectionClass;
use ReflectionException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter as ParamConverterConfig;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class FetcherParamConverter implements ParamConverterInterface
{
    private const PER_PAGE = 25;
    private const DEFAULT_SORT_FIELD = 'id';
    private const DIRECTION_PARAM_NAME = 'direction';
    private const SORT_PARAM_NAME = 'sort';
    private const SIZE_PARAM_NAME = 'pageSize';
    private const PAGE_PARAM_NAME = 'page';
    private const DEFAULT_SORT_DIRECTION = 'desc';

    public function __construct(
        protected DenormalizerInterface $denormalizer,
        protected Connection $connection,
        protected PaginatorInterface $paginator,
        protected ViewFactory $viewFactory,
        protected FetchersCollection $collection
    ) {
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function apply(Request $request, ParamConverterConfig $configuration): bool
    {
        $fetcherClass = $configuration->getClass();
        foreach ($this->collection->getFetchers() as $possibleFetcher) {
            $reflected = new ReflectionClass($possibleFetcher);
            if ($reflected->name === $fetcherClass) {
                $fetcher = $possibleFetcher;
            }
        }

        if (!isset($fetcher)) {
            return false;
        }

        $page = $request->query->getInt(self::PAGE_PARAM_NAME, 1);
        $perPage = $request->query->getInt(self::SIZE_PARAM_NAME, self::PER_PAGE);

        $sort = new Sort($request->query->get(self::SORT_PARAM_NAME) ?: self::DEFAULT_SORT_FIELD,
            $request->query->get(self::DIRECTION_PARAM_NAME) ?: self::DEFAULT_SORT_DIRECTION);
        $fetcher->setPage($page);
        $fetcher->setPageSize($perPage);
        $fetcher->setSort($sort);
        $request->attributes->set($configuration->getName(), $fetcher);

        return true;
    }

    /**
     * @throws ReflectionException
     */
    public function supports(ParamConverterConfig $configuration): bool
    {
        if (!$configuration->getClass()) {
            return false;
        }
        $reflected = new ReflectionClass($configuration->getClass());

        return $reflected->isSubclassOf(AbstractFetcher::class);
    }
}
