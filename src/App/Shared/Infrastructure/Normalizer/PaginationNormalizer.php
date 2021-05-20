<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Normalizer;

use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class PaginationNormalizer implements ContextAwareNormalizerInterface
{
    private ObjectNormalizer $normalizer;

    public function __construct(ObjectNormalizer $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * @param PaginationInterface $pagination
     * @param array<mixed> $context
     *
     * @return array<mixed>|\ArrayObject<mixed, mixed>|bool|float|int|string|void|null
     *
     * @throws ExceptionInterface
     */
    public function normalize($pagination, string $format = null, array $context = [])
    {
        $items = [];

        foreach ((array)$pagination->getItems() as $item) {
            $items[] = $this->normalizer->normalize($item, $format, $context);
        }

        return [
            'items' => $items,
            'pagination' => [
                'count' => $pagination->count(),
                'total' => $pagination->getTotalItemCount(),
                'perPage' => $pagination->getItemNumberPerPage(),
                'page' => $pagination->getCurrentPageNumber(),
                'pages' => ceil($pagination->getTotalItemCount() / $pagination->getItemNumberPerPage()),
            ],
        ];
    }

    /**
     * @param mixed $data
     * @param array<mixed> $context
     */
    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof PaginationInterface;
    }
}
