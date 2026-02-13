<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use PHPUnit\Framework\MockObject\Stub;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LoggerAwareTestCase extends KernelTestCase
{
    protected LoggerInterface|Stub|null $logger = null;

    /** @var array[] */
    private array $expectedMessages = [];

    /** @var array[] */
    private array $receivedMessages = [];

    public function tearDown(): void
    {
        /**
         * @var string                                      $level
         * @var array{message: string, context: string[]}[] $messages
         */
        foreach ($this->expectedMessages as $level => $messages) {
            foreach ($messages as $message) {
                $this->assertMessageReceived($level, $message['message'], $message['context']);
            }
        }

        /**
         * @var string                                      $level
         * @var array{message: string, context: string[]}[] $messages
         */
        foreach ($this->receivedMessages as $level => $messages) {
            foreach ($messages as $message) {
                $this->assertMessageExpected($level, $message['message'], $message['context']);
            }
        }

        parent::tearDown();
    }

    protected function expectsLog(string $level, string $message, array $context = []): static
    {
        if (!array_key_exists($level, $this->expectedMessages)) {
            $this->expectedMessages[$level] = [];
        }

        $this->expectedMessages[$level][] = [
            'message' => $message,
            'context' => $context,
        ];

        return $this;
    }

    protected function expectsDebug(string $message, array $context = []): static
    {
        return $this->expectsLog(LogLevel::DEBUG, $message, $context);
    }

    protected function expectsInfo(string $message, array $context = []): static
    {
        return $this->expectsLog(LogLevel::INFO, $message, $context);
    }

    protected function expectsNotice(string $message, array $context = []): static
    {
        return $this->expectsLog(LogLevel::NOTICE, $message, $context);
    }

    protected function expectsWarning(string $message, array $context = []): static
    {
        return $this->expectsLog(LogLevel::WARNING, $message, $context);
    }

    protected function expectsError(string $message, array $context = []): static
    {
        return $this->expectsLog(LogLevel::ERROR, $message, $context);
    }

    protected function expectsCritical(string $message, array $context = []): static
    {
        return $this->expectsLog(LogLevel::CRITICAL, $message, $context);
    }

    protected function expectsAlert(string $message, array $context = []): static
    {
        return $this->expectsLog(LogLevel::ALERT, $message, $context);
    }

    protected function expectsEmergency(string $message, array $context = []): static
    {
        return $this->expectsLog(LogLevel::EMERGENCY, $message, $context);
    }

    protected function getLogger(): LoggerInterface
    {
        $this->logger = $this->createStub(LoggerInterface::class);

        $this->logger->method('debug')
            ->willReturnCallback(function (string $message, array $context = []) {
                if (!array_key_exists('debug', $this->receivedMessages)) {
                    $this->receivedMessages['debug'] = [];
                }

                $this->receivedMessages['debug'][] = [
                    'message' => $message,
                    'context' => $context,
                ];
            });

        $this->logger->method('info')
            ->willReturnCallback(function (string $message, array $context = []) {
                if (!array_key_exists('info', $this->receivedMessages)) {
                    $this->receivedMessages['info'] = [];
                }

                $this->receivedMessages['info'][] = [
                    'message' => $message,
                    'context' => $context,
                ];
            });

        $this->logger->method('notice')
            ->willReturnCallback(function (string $message, array $context = []) {
                if (!array_key_exists('notice', $this->receivedMessages)) {
                    $this->receivedMessages['notice'] = [];
                }

                $this->receivedMessages['notice'][] = [
                    'message' => $message,
                    'context' => $context,
                ];
            });

        $this->logger->method('warning')
            ->willReturnCallback(function (string $message, array $context = []) {
                if (!array_key_exists('warning', $this->receivedMessages)) {
                    $this->receivedMessages['warning'] = [];
                }

                $this->receivedMessages['warning'][] = [
                    'message' => $message,
                    'context' => $context,
                ];
            });

        $this->logger->method('error')
            ->willReturnCallback(function (string $message, array $context = []) {
                if (!array_key_exists('error', $this->receivedMessages)) {
                    $this->receivedMessages['error'] = [];
                }

                $this->receivedMessages['error'][] = [
                    'message' => $message,
                    'context' => $context,
                ];
            });

        $this->logger->method('alert')
            ->willReturnCallback(function (string $message, array $context = []) {
                if (!array_key_exists('alert', $this->receivedMessages)) {
                    $this->receivedMessages['alert'] = [];
                }

                $this->receivedMessages['alert'][] = [
                    'message' => $message,
                    'context' => $context,
                ];
            });

        $this->logger->method('critical')
            ->willReturnCallback(function (string $message, array $context = []) {
                if (!array_key_exists('critical', $this->receivedMessages)) {
                    $this->receivedMessages['critical'] = [];
                }

                $this->receivedMessages['critical'][] = [
                    'message' => $message,
                    'context' => $context,
                ];
            });

        $this->logger->method('emergency')
            ->willReturnCallback(function (string $message, array $context = []) {
                if (!array_key_exists('emergency', $this->receivedMessages)) {
                    $this->receivedMessages['emergency'] = [];
                }

                $this->receivedMessages['emergency'][] = [
                    'message' => $message,
                    'context' => $context,
                ];
            });

        $this->logger->method('log')
            ->willReturnCallback(function (string $level, string $message, array $context = []) {
                if (!array_key_exists($level, $this->receivedMessages)) {
                    $this->receivedMessages[$level] = [];
                }

                $this->receivedMessages[$level][] = [
                    'message' => $message,
                    'context' => $context,
                ];
            });

        return $this->logger;
    }

    private function assertMessageReceived(string $level, string $message, array $context): void
    {
        $received = false;

        $this->assertArrayHasKey(
            $level,
            $this->receivedMessages,
            sprintf('Logger was expecting message %s with level %s, but did not receive it.', $message, $level),
        );

        /** @var array{message: string, context: string[]} $receivedMessage */
        foreach ($this->receivedMessages[$level] as $receivedMessage) {
            if ($receivedMessage['message'] !== $message) {
                continue;
            }

            if ($receivedMessage['context'] !== $context) {
                continue;
            }

            $received = true;
            break;
        }

        $this->assertTrue(
            $received,
            sprintf('Logger was expecting message %s with level %s, but did not receive it.', $message, $level)
        );
    }

    private function assertMessageExpected(string $level, string $message, array $context): void
    {
        $expected = false;

        $this->assertArrayHasKey(
            $level,
            $this->expectedMessages,
            sprintf('Logger received message %s with level %s, but did not expect it.', $message, $level),
        );

        /** @var array{message: string, context: string[]} $expectedMessage */
        foreach ($this->expectedMessages[$level] as $expectedMessage) {
            if ($expectedMessage['message'] !== $message) {
                continue;
            }

            if ($expectedMessage['context'] !== $context) {
                continue;
            }

            $expected = true;
            break;
        }

        $this->assertTrue(
            $expected,
            sprintf('Logger received message %s with level %s, but did not expect it.', $message, $level)
        );
    }
}
