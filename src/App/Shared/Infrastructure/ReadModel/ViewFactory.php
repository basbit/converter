<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\ReadModel;

use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class ViewFactory
{
    public SerializerInterface $serializer;
    protected string $listViewModelClass = \stdClass::class;
    protected string $itemViewModelClass = \stdClass::class;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function buildListView(array $listViewData)
    {
        return $this->build($listViewData, $this->listViewModelClass);
    }

    public function buildItemView(array $itemViewData)
    {
        return $this->build($itemViewData, $this->itemViewModelClass);
    }

    protected function build(array $data, string $viewModel)
    {
        $deserializedItem = null;
        $deserializedItem = $this->serializer->denormalize(
            $data,
            $viewModel,
            'array',
            [
                'object_to_populate' => $deserializedItem,
                AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
            ]
        );

        return $deserializedItem;
    }

    public function setListViewModelClass(string $listViewModelClass): void
    {
        $this->listViewModelClass = $listViewModelClass;
    }

    public function setItemViewModelClass(string $itemViewModelClass): void
    {
        $this->itemViewModelClass = $itemViewModelClass;
    }
}
