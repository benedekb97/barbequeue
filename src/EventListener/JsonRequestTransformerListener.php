<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::REQUEST, method: 'onKernelRequest', priority: 100)]
class JsonRequestTransformerListener
{
    private const string CONTENT_TYPE_JSON = 'json';
    private const string CONTENT_TYPE_JSON_LD = 'jsonld';

    private const array CONTENT_TYPES = [
        self::CONTENT_TYPE_JSON,
        self::CONTENT_TYPE_JSON_LD,
    ];

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$this->supports($request)) {
            return;
        }

        try {
            /** @var array<string, mixed> $data */
            $data = json_decode((string) $request->getContent(), true, 512, JSON_THROW_ON_ERROR);

            $request->request->replace($data);
        } catch (\JsonException $exception) {
            $event->setResponse(new JsonResponse(['message' => $exception->getMessage()], Response::HTTP_BAD_REQUEST));
        }
    }

    private function supports(Request $request): bool
    {
        return in_array($request->getContentTypeFormat(), self::CONTENT_TYPES, true)
            && $request->getContent();
    }
}
