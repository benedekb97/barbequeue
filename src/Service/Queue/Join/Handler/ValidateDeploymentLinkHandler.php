<?php

declare(strict_types=1);

namespace App\Service\Queue\Join\Handler;

use App\Entity\DeploymentQueue;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Exception\InvalidDeploymentUrlException;
use App\Service\Queue\Join\JoinQueueContext;
use App\Validator\UrlValidator;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 4_850)]
readonly class ValidateDeploymentLinkHandler implements JoinQueueHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private UrlValidator $urlValidator,
    ) {
    }

    public function supports(QueueContextInterface $context): bool
    {
        return $context instanceof JoinQueueContext && $context->getQueue() instanceof DeploymentQueue;
    }

    /** @throws InvalidDeploymentUrlException */
    public function handle(QueueContextInterface $context): void
    {
        if (!$context instanceof JoinQueueContext) {
            return;
        }

        $this->logger->debug('Validating deployment link for {contextId} {contextType}', [
            'contextId' => $context->getId(),
            'contextType' => $context->getType(),
        ]);

        $validatedUrl = $this->urlValidator->validate($providedUrl = $context->getDeploymentLink() ?? '');

        if (null === $validatedUrl) {
            throw new InvalidDeploymentUrlException($providedUrl, $context->getQueue());
        }

        $context->setDeploymentLink($validatedUrl);
    }
}
