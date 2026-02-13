<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Resolver;

use App\Slack\Interaction\Exception\ValueUnchangedException;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionArgumentLocation;
use App\Slack\Surface\Component\Exception\UnrecognisedInputElementException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

readonly class InteractionArgumentsResolver
{
    public function __construct(
        private LoggerInterface $logger,
        private InteractionStateArgumentResolver $stateArgumentResolver,
        private InteractionPrivateMetadataArgumentResolver $privateMetadataArgumentResolver,
    ) {
    }

    /** @return (int|string|int[]|string[]|null)[] */
    public function resolve(Interaction $interaction, Request $request): array
    {
        $argumentKeys = $interaction->getArguments();

        $arguments = [];

        foreach ($argumentKeys as $argumentKey) {
            try {
                $arguments[$argumentKey] = match ($interaction->getArgumentLocation($argumentKey)) {
                    InteractionArgumentLocation::STATE => $this->stateArgumentResolver
                        ->resolve($request, $argumentKey),

                    InteractionArgumentLocation::PRIVATE_METADATA => $this->privateMetadataArgumentResolver
                        ->resolve($request, $argumentKey),
                };
            } catch (ValueUnchangedException) {
                continue;
            } catch (UnrecognisedInputElementException $exception) {
                $this->logger->debug('Received unrecognised input element '.$exception->getInputElementType());

                continue;
            }
        }

        return $arguments;
    }
}
