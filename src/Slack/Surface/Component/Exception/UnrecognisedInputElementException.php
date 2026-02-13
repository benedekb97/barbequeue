<?php

declare(strict_types=1);

namespace App\Slack\Surface\Component\Exception;

use App\Slack\BlockElement\Component\SlackBlockElement;

class UnrecognisedInputElementException extends \Exception
{
    /** @param class-string<SlackBlockElement>|string|null $inputElementType */
    public function __construct(private readonly ?string $inputElementType)
    {
        parent::__construct();
    }

    /** @return class-string<SlackBlockElement>|string|null */
    public function getInputElementType(): ?string
    {
        return $this->inputElementType;
    }
}
