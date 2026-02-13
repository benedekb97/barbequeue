<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\PrivateMetadata;

use App\Slack\Surface\Factory\PrivateMetadata\Exception\JsonEncodingException;

interface PrivateMetadataFactoryInterface
{
    /** @throws JsonEncodingException */
    public function create(): string;
}
