<?php

declare(strict_types=1);

namespace App\Slack\Surface\Component\Exception;

class UnrecognisedInputElementException extends \Exception
{
    public function __construct(private readonly string $inputElementType)
    {
        parent::__construct();
    }

    public function getInputElementType(): string
    {
        return $this->inputElementType;
    }
}
