<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Resolver;

use App\Slack\Surface\Component\ModalArgument;
use App\Slack\Surface\Factory\Exception\NoOptionsAvailableException;

interface OptionsResolverInterface
{
    public function getSupportedArgument(): ModalArgument;

    /** @throws NoOptionsAvailableException */
    public function resolve(): array;
}
