<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Repository;

use App\Entity\Repository;
use App\Entity\Workspace;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Block\Component\Table\ItalicTextCell;
use App\Slack\Block\Component\Table\LinkCell;
use App\Slack\Block\Component\Table\RawTextCell;
use App\Slack\Block\Component\Table\TableRow;
use App\Slack\Block\Component\TableBlock;
use App\Slack\Response\Interaction\SlackInteractionResponse;

class ListRepositoriesResponseFactory
{
    public function create(Workspace $workspace): SlackInteractionResponse
    {
        return new SlackInteractionResponse(
            [
                new SectionBlock('A list of your currently added repositories:'),
                new TableBlock([
                    new TableRow([
                        new RawTextCell('Name'),
                        new RawTextCell('URL'),
                    ]),
                    ...$this->getDataRows($workspace),
                ]),
            ]
        );
    }

    /** @return TableRow[] */
    private function getDataRows(Workspace $workspace): array
    {
        return $workspace->getRepositories()->map(function (Repository $repository) {
            return new TableRow([
                new RawTextCell($repository->getName()),
                ($url = $repository->getUrl())
                    ? new LinkCell($url)
                    : new ItalicTextCell('No URL set'),
            ]);
        })->toArray();
    }
}
