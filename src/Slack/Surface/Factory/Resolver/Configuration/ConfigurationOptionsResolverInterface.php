<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Resolver\Configuration;

use App\Slack\Surface\Factory\Resolver\OptionsResolverInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag]
interface ConfigurationOptionsResolverInterface extends OptionsResolverInterface
{
}
