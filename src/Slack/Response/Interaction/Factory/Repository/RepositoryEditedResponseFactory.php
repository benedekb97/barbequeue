<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Repository;

use App\Entity\Repository;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Block\Component\Table\ItalicTextCell;
use App\Slack\Block\Component\Table\LinkCell;
use App\Slack\Block\Component\Table\RawTextCell;
use App\Slack\Block\Component\Table\TableCell;
use App\Slack\Block\Component\Table\TableRow;
use App\Slack\Block\Component\TableBlock;
use App\Slack\Response\Interaction\SlackInteractionResponse;

class RepositoryEditedResponseFactory
{
    public function create(Repository $repository): SlackInteractionResponse
    {
        return new SlackInteractionResponse(
            [
                new SectionBlock(sprintf('Repository `%s` edited successfully.', $repository->getName())),
                new TableBlock([
                    new TableRow([
                        new RawTextCell('Name'),
                        new RawTextCell('URL'),
                    ]),
                    new TableRow([
                        new RawTextCell($repository->getName()),
                        $this->getRepositoryUrlCell($repository),
                    ]),
                ]),
            ],
        );
    }

    private function getRepositoryUrlCell(Repository $repository): TableCell
    {
        if (null === $repository->getUrl()) {
            return new ItalicTextCell('URL not set');
        }

        return new LinkCell($repository->getUrl());
    }
}
