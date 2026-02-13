<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Repository;

use App\Entity\Repository;
use App\Repository\RepositoryRepositoryInterface;
use App\Slack\Block\Component\HeaderBlock;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\SlackInteractionResponse;

readonly class UnrecognisedRepositoryResponseFactory
{
    public function __construct(
        private RepositoryRepositoryInterface $repository,
    ) {
    }

    public function create(string $name, string $teamId): SlackInteractionResponse
    {
        return new SlackInteractionResponse(
            [
                new HeaderBlock(sprintf('Repository `%s` does not exist', $name)),
                new SectionBlock(
                    sprintf(
                        'Available repositories: %s',
                        implode(', ', $this->getAvailableRepositories($teamId))
                    )
                ),
            ],
        );
    }

    /** @return string[] */
    private function getAvailableRepositories(string $teamId): array
    {
        $repositories = $this->repository->findByTeamId($teamId);

        return array_map(function (Repository $repository) {
            return ($name = $repository->getName()) === null
                ? ''
                : '`'.$name.'`';
        }, $repositories);
    }
}
