<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Interaction;

use App\Entity\Administrator;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use App\Slack\Interaction\SlackInteraction;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(SlackInteraction::class)]
class SlackInteractionTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnProvidedProperties(): void
    {
        $slackInteraction = new SlackInteraction(
            $type = InteractionType::BLOCK_ACTIONS,
            $interaction = Interaction::EDIT_QUEUE,
            $teamId = 'teamId',
            $userId = 'userId',
            $userName = 'userName',
            $responseUrl = 'responseUrl',
            $value = 'value',
            $triggerId = 'triggerId',
            $viewId = 'viewId',
        );

        $slackInteraction->setAdministrator(
            $administrator = $this->createStub(Administrator::class),
        );

        $response = $this->createStub(SlackInteractionResponse::class);

        $slackInteraction->setResponse($response);

        $this->assertSame($type, $slackInteraction->getType());
        $this->assertSame($interaction, $slackInteraction->getInteraction());
        $this->assertSame($userId, $slackInteraction->getUserId());
        $this->assertSame($userName, $slackInteraction->getUserName());
        $this->assertSame($teamId, $slackInteraction->getTeamId());
        $this->assertSame($responseUrl, $slackInteraction->getResponseUrl());
        $this->assertSame($value, $slackInteraction->getValue());
        $this->assertSame($triggerId, $slackInteraction->getTriggerId());
        $this->assertSame($response, $slackInteraction->getResponse());
        $this->assertSame($administrator, $slackInteraction->getAdministrator());
        $this->assertSame($viewId, $slackInteraction->getViewId());
    }
}
