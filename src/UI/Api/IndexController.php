<?php

namespace UI\Api;

use App\Quote\Application\Converter\Command as ConverterCommand;
use App\Quote\Infrastructure\ReadModel\Currency\Fetcher as CurrencyFetcher;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class IndexController extends BaseController
{
    /**
     * @Route(
     *     "/exchange",
     *     name="exchange",
     *     methods={"POST"}
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
     *         type="object",
     *         @OA\Property(property="id", type="int"),
     *         @OA\Property(property="from", type="string"),
     *         @OA\Property(property="to", type="string"),
     *         @OA\Property(property="rate", type="float"),
     *         @OA\Property(property="amount", type="float"),
     *         @OA\Property(property="result", type="float"),
     *         @OA\Property(property="dateTime", type="string|null")
     *     )
     * )
     *
     * @OA\Tag(name="Exchange")
     * @throws Throwable
     */
    public function exchange(ConverterCommand $command): JsonResponse
    {
        $envelope = $this->dispatchMessage($command);

        $handledStamp = $envelope->last(HandledStamp::class);

        return $this->json($handledStamp?->getResult());
    }

    /**
     * @Route(
     *     "/currencies",
     *     name="currencies",
     *     methods={"GET"},
     * )
     * @OA\Response(
     *     response=200,
     *     description="Converted successfully",
     *     @OA\JsonContent(
     *        type="object",
     *        @OA\Property(
     *          property="items",
     *          type="array",
     *          @OA\Items(
     *              type="object",
     *              @OA\Property(
     *                  property="id", type="int"
     *              ),
     *              @OA\Property(
     *                  property="name", type="string"
     *              )
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
     * @OA\Response(
     *     response=400,
     *     description="Bad request"
     * )
     * @OA\Response(
     *     response=401,
     *     description="Bad credentials"
     * )
     *
     * @OA\Tag(name="Currencies")
     * @throws Throwable
     */
    public function currencies(CurrencyFetcher $fetcher): JsonResponse
    {
        return $this->json($fetcher->all());
    }
}
