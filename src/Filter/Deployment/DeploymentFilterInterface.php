<?php

declare(strict_types=1);

namespace App\Filter\Deployment;

use App\Entity\Deployment;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(self::TAG)]
interface DeploymentFilterInterface
{
    public const string TAG = 'app.filter.deployment';

    /**
     * @param Deployment[] $deployments
     *
     * @return Deployment[]
     */
    public function filter(array $deployments): array;
}
