<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Inputs;

use App\Slack\Block\Component\SlackBlock;
use App\Slack\Surface\Component\Modal;

interface ModalInputsFactoryInterface
{
    /** @return SlackBlock[] */
    public function create(Modal $modal): array;
}
