<?php

declare(strict_types=1);

namespace App\Validator;

class UrlValidator
{
    public function validate(string $url): ?string
    {
        return filter_var($url, FILTER_VALIDATE_URL) ?: null;
    }
}
