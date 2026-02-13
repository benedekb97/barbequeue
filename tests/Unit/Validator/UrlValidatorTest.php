<?php

declare(strict_types=1);

namespace App\Tests\Unit\Validator;

use App\Validator\UrlValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(UrlValidator::class)]
class UrlValidatorTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnNullIfNotValidUrl(): void
    {
        $validator = new UrlValidator();

        $this->assertNull($validator->validate('not a url'));
    }

    #[Test]
    public function itShouldReturnUrlIfValidUrl(): void
    {
        $validator = new UrlValidator();

        $this->assertEquals('https://example.com', $validator->validate('https://example.com'));
    }
}
