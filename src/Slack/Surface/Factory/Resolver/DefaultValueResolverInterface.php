<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Resolver;

use App\Slack\Surface\Component\ModalArgument;

interface DefaultValueResolverInterface
{
    public function getSupportedArgument(): ModalArgument;

    public function resolveString(): ?string;

    public function resolveArray(): ?array;
}
