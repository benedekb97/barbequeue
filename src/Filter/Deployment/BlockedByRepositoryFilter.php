<?php

declare(strict_types=1);

namespace App\Filter\Deployment;

use App\Entity\Deployment;

class BlockedByRepositoryFilter implements DeploymentFilterInterface
{
    public function filter(array $deployments): array
    {
        return array_values(array_filter($deployments, static function (Deployment $deployment): bool {
            return !$deployment->isBlockedByRepository();
        }));
    }
}
