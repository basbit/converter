<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\EventListener;

use App\Shared\Infrastructure\Validator\RequestValidationException;
use Doctrine\ORM\EntityNotFoundException;
use Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExceptionsSubscriber implements EventSubscriberInterface
{
    /** @required */
    public TranslatorInterface $trans;
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @return array<array>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => [
                ['onKernelException'],
            ],
        ];
    }

    private function digForException(\Throwable $exception): \Throwable
    {
        if ($exception instanceof HandlerFailedException) {
            $exception = $exception->getPrevious();
            if ($exception) {
                $exception = $this->digForException($exception);
            }
        }

        return $exception;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $exception = $this->digForException($exception);
        $handledResponse = $event->getResponse();

        if ($exception instanceof RequestValidationException) {
            if (!$handledResponse) {
                $handledResponse = new Response('', 422, [
                    'Content-type' => 'application/json',
                ]);
            }
            $violations = $exception->getViolations();
            $errors = [];
            /** @var ConstraintViolationInterface $violation */
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            $handledResponse->setContent($this->serializer->serialize($errors, 'json'));
            $event->setResponse($handledResponse);
        }

        if ($exception instanceof EntityNotFoundException) {
            if (!$handledResponse) {
                $handledResponse = new Response('', 404, [
                    'Content-type' => 'application/json',
                ]);
            }
            $handledResponse->setContent($this->serializer->serialize([
                'error' => $this->trans->trans($exception->getMessage()),
            ], 'json'));
            $event->setResponse($handledResponse);
        }

        if ($exception instanceof Exception) {
            if (!$handledResponse) {
                $handledResponse = new Response('', 409, [
                    'Content-type' => 'application/json',
                ]);
            }
            $handledResponse->setContent($this->serializer->serialize([
                'error' => $this->trans->trans($exception->getMessage()),
            ], 'json'));
            $event->setResponse($handledResponse);
        }

        if ($exception instanceof NotEncodableValueException) {
            if (!$handledResponse) {
                $handledResponse = new Response('', 400, [
                    'Content-type' => 'application/json',
                ]);
            }
            $handledResponse->setContent($this->serializer->serialize([
                'error' => $exception->getMessage(),
            ], 'json'));
            $event->setResponse($handledResponse);
        }

        if ($exception instanceof \Doctrine\ORM\EntityNotFoundException) {
            if (!$handledResponse) {
                $handledResponse = new Response('', 404, [
                    'Content-type' => 'application/json',
                ]);
            }
            $handledResponse->setContent($this->serializer->serialize([
                'error' => $this->trans->trans($exception->getMessage()),
            ], 'json'));
            $event->setResponse($handledResponse);
        }
    }
}
