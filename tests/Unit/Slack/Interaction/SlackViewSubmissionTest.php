<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Interaction;

use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\SlackViewSubmission;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(SlackViewSubmission::class)]
class SlackViewSubmissionTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnPassedProperties(): void
    {
        $viewSubmission = new SlackViewSubmission(
            $interaction = Interaction::EDIT_QUEUE_ACTION,
            $teamId = 'teamId',
            $userId = 'userId',
            $userName = 'userName',
            $arguments = [
                $argumentName = 'argument' => $argumentValue = 'argumentValue',
                $argumentNullName = 'argumentNull' => null,
                $argumentIntName = 'argumentInt' => '1',
                $argumentArrayName = 'argumentArray' => [1],
            ],
            $triggerId = 'triggerId',
            $responseUrl = 'responseUrl',
        );

        $this->assertEquals($interaction, $viewSubmission->getInteraction());
        $this->assertEquals($teamId, $viewSubmission->getTeamId());
        $this->assertEquals($userId, $viewSubmission->getUserId());
        $this->assertEquals($userName, $viewSubmission->getUserName());
        $this->assertEquals($arguments, $viewSubmission->getArguments());
        $this->assertEquals($triggerId, $viewSubmission->getTriggerId());
        $this->assertEmpty($viewSubmission->getValue());
        $this->assertEquals($argumentValue, $viewSubmission->getArgument($argumentName));
        $this->assertTrue($viewSubmission->isPending());
        $viewSubmission->setHandled();
        $this->assertFalse($viewSubmission->isPending());

        $this->assertNull($viewSubmission->getResponse());
        $this->assertEquals(1, $viewSubmission->getArgumentInteger($argumentIntName));
        $this->assertEquals($argumentValue, $viewSubmission->getArgumentString($argumentName));
        $this->assertNull($viewSubmission->getArgumentString($argumentNullName));
        $this->assertNull($viewSubmission->getArgumentInteger($argumentNullName));
        $this->assertNull($viewSubmission->getArgumentInteger($argumentArrayName));
        $this->assertNull($viewSubmission->getArgumentString($argumentArrayName));
        $this->assertTrue($viewSubmission->isArgumentProvided($argumentIntName));
        $this->assertNull($viewSubmission->getArgumentIntArray('non-existent-argument'));
        $this->assertNull($viewSubmission->getArgumentIntArray($argumentIntName));
        $this->assertEquals([1], $viewSubmission->getArgumentIntArray($argumentArrayName));
        $this->assertNull($viewSubmission->getArgumentStringArray($argumentIntName));
        $this->assertNull($viewSubmission->getArgumentStringArray('non-existent-argument'));
        $this->assertEquals(['1'], $viewSubmission->getArgumentStringArray($argumentArrayName));

        $response = $this->createStub(SlackInteractionResponse::class);

        $viewSubmission->setResponse($response);

        $this->assertSame($response, $viewSubmission->getResponse());

        $this->assertEquals($responseUrl, $viewSubmission->getResponseUrl());
    }
}
