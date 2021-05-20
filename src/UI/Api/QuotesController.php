<?php

namespace UI\Api;

use App\Quote\Application\Create\Command as Create;
use App\Quote\Application\Delete\Command as Delete;
use App\Quote\Application\Delete\Handler as DeleteHandler;
use App\Quote\Application\Update\Command as Update;
use App\Quote\Infrastructure\ReadModel\Quote\Fetcher as QuoteFetcher;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class QuotesController extends BaseController
{
    /**
     * @Route(
     *     "/quotes",
     *     name="list",
     *     methods={"GET"},
     * )
     * @OA\Response(
     *     response=200,
     *     description="Converted successfully",
     *     @OA\JsonContent(
     *        type="object",
     *        @OA\Property(
     *          property="rate", type="float"
     *        ),
     *        @OA\Property(
     *          property="amount", type="float"
     *        )
     *     )
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad request"
     * )
     * @OA\Response(
     *     response=401,
     *     description="Bad credentials"
     * )
     * @OA\RequestBody(
     *     @OA\JsonContent(
     *        type="object",
     *        @OA\Property(
     *          property="items",
     *          type="array",
     *          @OA\Items(
     *              type="object",
     *              @OA\Property(property="id", type="int"),
     *              @OA\Property(property="currencyFrom", type="string"),
     *              @OA\Property(property="currencyTo", type="string"),
     *              @OA\Property(property="rate", type="float"),
     *              @OA\Property(property="createdAt", type="string")
     *          ),
     *          description="currencies"
     *        ),
     *        @OA\Property(
     *          property="pagination",
     *          type="object",
     *          @OA\Property(
     *                  property="count", type="int"
     *           ),
     *           @OA\Property(
     *                  property="page", type="int"
     *           ),
     *           @OA\Property(
     *                  property="pages", type="int"
     *           ),
     *           @OA\Property(
     *                  property="perPage", type="int"
     *           ),
     *           @OA\Property(
     *                  property="total", type="int"
     *           )
     *        )
     *     )
     * )
     *
     * @OA\Tag(name="Quotes")
     *
     * @throws Throwable
     */
    public function list(QuoteFetcher $fetcher): JsonResponse
    {
        return $this->json($fetcher->all());
    }

    /**
     * @Route(
     *     "/quotes",
     *     name="create",
     *     methods={"PUT"},
     * )
     * @OA\Response(
     *     response=200,
     *     description="Converted successfully",
     *     @OA\JsonContent(
     *        type="object",
     *        @OA\Property(
     *          property="rate", type="float"
     *        ),
     *        @OA\Property(
     *          property="amount", type="float"
     *        )
     *     )
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad request"
     * )
     * @OA\Response(
     *     response=401,
     *     description="Bad credentials"
     * )
     * @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref=@Model(type=Create::class, groups={"request-deserializable"}))
     * )
     *
     * @OA\Tag(name="Quotes")
     *
     * @throws Throwable
     */
    public function create(Create $command): JsonResponse
    {
        $envelope = $this->dispatchMessage($command);

        $handledStamp = $envelope->last(HandledStamp::class);
        return $this->json($handledStamp?->getResult());
    }

    /**
     * @Route(
     *     "/quotes/{id<\d+>}",
     *     name="update",
     *     methods={"PUT"},
     * )
     * @OA\Parameter(name="id", in="path", required=true)
     * @OA\Put(operationId="UpdateQuotes")
     * @OA\Response(
     *     response=200,
     *     description="Converted successfully",
     *     @OA\JsonContent(
     *        type="object",
     *        @OA\Property(
     *          property="rate", type="float"
     *        )
     *     )
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad request"
     * )
     * @OA\Response(
     *     response=401,
     *     description="Bad credentials"
     * )
     * @OA\RequestBody(
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="rate", type="float"),
     *     )
     * )
     *
     * @OA\Tag(name="Quotes")
     *
     * @throws Throwable
     */
    public function update(Update $command): JsonResponse
    {
        $envelope = $this->dispatchMessage($command);
        $handledStamp = $envelope->last(HandledStamp::class);
        return $this->json($handledStamp?->getResult());
    }

    /**
     * @OA\Parameter(name="id", in="path", required=true)
     * @Route(
     *     "/quotes/{id<\d+>}",
     *     name="delete",
     *     methods={"DELETE"},
     * )
     * @OA\Response(
     *     response=200,
     *     description="Deleted successfully",
     *     @OA\JsonContent(
     *        type="object",
     *     )
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad request"
     * )
     * @OA\Response(
     *     response=401,
     *     description="Bad credentials"
     * )
     *
     * @OA\Tag(name="Quotes")
     *
     * @throws Throwable
     */
    public function delete(int $id, DeleteHandler $handler): JsonResponse
    {
        $command = new Delete($id);
        $result = $handler->handler($command);
        return $this->json([$id => $result]);
    }
}
