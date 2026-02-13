<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Interaction\Resolver;

use App\Slack\Interaction\Exception\ValueUnchangedException;
use App\Slack\Interaction\Resolver\InteractionStateArgumentResolver;
use App\Slack\Surface\Component\Exception\UnrecognisedInputElementException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(InteractionStateArgumentResolver::class)]
class InteractionStateArgumentResolverTest extends KernelTestCase
{
    #[Test]
    public function itShouldThrowValueUnchangedExceptionIfValueFieldNotPresentOnState(): void
    {
        $request = new Request(request: [
            'view' => [
                'state' => [
                    'values' => [
                        [
                            $argumentKey = 'argument' => [
                                'type' => 'plain_text_input',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $resolver = new InteractionStateArgumentResolver();

        $this->expectException(ValueUnchangedException::class);

        $resolver->resolve($request, $argumentKey);
    }

    #[Test]
    public function itShouldThrowValueUnchangedExceptionIfValueFieldNotPresentOnStateForNumberInput(): void
    {
        $request = new Request(request: [
            'view' => [
                'state' => [
                    'values' => [
                        [
                            $argumentKey = 'argument' => [
                                'type' => 'number_input',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $resolver = new InteractionStateArgumentResolver();

        $this->expectException(ValueUnchangedException::class);

        $resolver->resolve($request, $argumentKey);
    }

    #[Test]
    public function itShouldReturnNullIfArgumentIsNull(): void
    {
        $request = new Request(request: [
            'view' => [
                'state' => [
                    'values' => [
                        [
                            $argumentKey = 'argument' => [
                                'value' => null,
                                'type' => 'plain_text_input',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $resolver = new InteractionStateArgumentResolver();

        $result = $resolver->resolve($request, $argumentKey);

        $this->assertNull($result);
    }

    #[Test]
    public function itShouldReturnNullIfArgumentIsNullForNumberInput(): void
    {
        $request = new Request(request: [
            'view' => [
                'state' => [
                    'values' => [
                        [
                            $argumentKey = 'argument' => [
                                'value' => null,
                                'type' => 'number_input',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $resolver = new InteractionStateArgumentResolver();

        $result = $resolver->resolve($request, $argumentKey);

        $this->assertNull($result);
    }

    #[Test]
    public function itShouldReturnIntegerIfTypeIsNumberInput(): void
    {
        $request = new Request(request: [
            'view' => [
                'state' => [
                    'values' => [
                        [
                            $argumentKey = 'argument' => [
                                'value' => (string) ($value = 1),
                                'type' => 'number_input',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $resolver = new InteractionStateArgumentResolver();

        $result = $resolver->resolve($request, $argumentKey);

        $this->assertIsInt($result);
        $this->assertEquals($value, $result);
    }

    #[Test, DataProvider('provideApplicableStringFieldTypes')]
    public function itShouldReturnStringOnApplicableFieldTypes(string $type): void
    {
        $request = new Request(request: [
            'view' => [
                'state' => [
                    'values' => [
                        [
                            $argumentKey = 'argument' => [
                                'value' => $value = 'value',
                                'type' => $type,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $resolver = new InteractionStateArgumentResolver();

        $result = $resolver->resolve($request, $argumentKey);

        $this->assertIsString($result);
        $this->assertEquals($value, $result);
    }

    public static function provideApplicableStringFieldTypes(): array
    {
        return [
            ['plain_text_input'],
            ['email_input'],
        ];
    }

    public function itShouldThrowUnrecognisedInputElementExceptionOnUnknownFieldType(): void
    {
        $request = new Request(request: [
            'view' => [
                'state' => [
                    'values' => [
                        [
                            $argumentKey = 'argument' => [
                                'value' => 'value',
                                'type' => 'unknown_field_type',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $resolver = new InteractionStateArgumentResolver();

        $this->expectException(UnrecognisedInputElementException::class);

        $resolver->resolve($request, $argumentKey);
    }

    #[Test]
    public function itShouldReturnNullIfMultiStaticSelectValuesNotPresent(): void
    {
        $request = new Request(request: [
            'view' => [
                'state' => [
                    'values' => [
                        [
                            $argumentKey = 'argument' => [
                                'type' => 'multi_static_select',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $resolver = new InteractionStateArgumentResolver();

        $this->assertNull($resolver->resolve($request, $argumentKey));
    }

    #[Test]
    public function itShouldReturnNullIfMultiSelectSelectedOptionsIsNotArray(): void
    {
        $request = new Request(request: [
            'view' => [
                'state' => [
                    'values' => [
                        [
                            $argumentKey = 'argument' => [
                                'type' => 'multi_static_select',
                                'selected_options' => 'not-an-array',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $resolver = new InteractionStateArgumentResolver();

        $this->assertNull($resolver->resolve($request, $argumentKey));
    }

    #[Test]
    public function itShouldReturnEmptyArrayIfMultiSelectSelectedOptionsIsEmpty(): void
    {
        $request = new Request(request: [
            'view' => [
                'state' => [
                    'values' => [
                        [
                            $argumentKey = 'argument' => [
                                'type' => 'multi_static_select',
                                'selected_options' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $resolver = new InteractionStateArgumentResolver();

        $this->assertIsArray($result = $resolver->resolve($request, $argumentKey));
        $this->assertEmpty($result);
    }

    #[Test]
    public function itShouldFilterIfSelectedOptionCannotBeResolved(): void
    {
        $request = new Request(request: [
            'view' => [
                'state' => [
                    'values' => [
                        [
                            $argumentKey = 'argument' => [
                                'type' => 'multi_static_select',
                                'selected_options' => [
                                    'invalid-option',
                                    [
                                        'something' => 'another-invalid-option',
                                    ], [
                                        'something' => 'value-should-be-string',
                                        'value' => 1,
                                    ], [
                                        'value' => $expectedValue = 'expectedValue',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $resolver = new InteractionStateArgumentResolver();

        $this->assertIsArray($result = $resolver->resolve($request, $argumentKey));
        $this->assertEquals([$expectedValue], $result);
    }

    #[Test]
    public function itShouldReturnNullIfMultiUsersSelectValuesNotPresent(): void
    {
        $request = new Request(request: [
            'view' => [
                'state' => [
                    'values' => [
                        [
                            $argumentKey = 'argument' => [
                                'type' => 'multi_users_select',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $resolver = new InteractionStateArgumentResolver();

        $this->assertNull($resolver->resolve($request, $argumentKey));
    }

    #[Test]
    public function itShouldReturnNullIfMultiUsersSelectedUsersIsNotArray(): void
    {
        $request = new Request(request: [
            'view' => [
                'state' => [
                    'values' => [
                        [
                            $argumentKey = 'argument' => [
                                'type' => 'multi_users_select',
                                'selected_users' => 'not-an-array',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $resolver = new InteractionStateArgumentResolver();

        $this->assertNull($resolver->resolve($request, $argumentKey));
    }

    #[Test]
    public function itShouldReturnEmptyArrayIfMultiUsersSelectSelectedUsersIsEmpty(): void
    {
        $request = new Request(request: [
            'view' => [
                'state' => [
                    'values' => [
                        [
                            $argumentKey = 'argument' => [
                                'type' => 'multi_users_select',
                                'selected_users' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $resolver = new InteractionStateArgumentResolver();

        $this->assertNull($resolver->resolve($request, $argumentKey));
    }

    #[Test]
    public function itShouldResolveSelectedUsers(): void
    {
        $request = new Request(request: [
            'view' => [
                'state' => [
                    'values' => [
                        [
                            $argumentKey = 'argument' => [
                                'type' => 'multi_users_select',
                                'selected_users' => $selectedUsers = [
                                    'user-1', 'user-2',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $resolver = new InteractionStateArgumentResolver();

        $this->assertIsArray($result = $resolver->resolve($request, $argumentKey));
        $this->assertEquals($selectedUsers, $result);
    }

    #[Test]
    public function itShouldReturnNullIfStateEmpty(): void
    {
        $request = new Request(request: [
            'view' => [
                'state' => [
                    'values' => [],
                ],
            ],
        ]);

        $resolver = new InteractionStateArgumentResolver();

        $this->assertNull($resolver->resolve($request, 'argument'));
    }

    #[Test]
    public function itShouldReturnNullOnSingleSelectIfSelectedOptionNotPresent(): void
    {
        $request = new Request(request: [
            'view' => [
                'state' => [
                    'values' => [
                        [
                            $argumentKey = 'argument' => [
                                'type' => 'static_select',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $resolver = new InteractionStateArgumentResolver();

        $this->assertNull($resolver->resolve($request, $argumentKey));
    }

    #[Test]
    public function itShouldReturnNullIfSelectedOptionIsNotArrayOnStaticSelect(): void
    {
        $request = new Request(request: [
            'view' => [
                'state' => [
                    'values' => [
                        [
                            $argumentKey = 'argument' => [
                                'type' => 'static_select',
                                'selected_option' => 'not-an-array',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $resolver = new InteractionStateArgumentResolver();

        $this->assertNull($resolver->resolve($request, $argumentKey));
    }

    #[Test]
    public function itShouldReturnNullIfSelectedOptionIsEmptyOnStaticSelect(): void
    {
        $request = new Request(request: [
            'view' => [
                'state' => [
                    'values' => [
                        [
                            $argumentKey = 'argument' => [
                                'type' => 'static_select',
                                'selected_option' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $resolver = new InteractionStateArgumentResolver();

        $this->assertNull($resolver->resolve($request, $argumentKey));
    }

    #[Test]
    public function itShouldReturnNullIfValueNotPresentOnSelectedOptionOnStaticSelect(): void
    {
        $request = new Request(request: [
            'view' => [
                'state' => [
                    'values' => [
                        [
                            $argumentKey = 'argument' => [
                                'type' => 'static_select',
                                'selected_option' => [
                                    'not-empty',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $resolver = new InteractionStateArgumentResolver();

        $this->assertNull($resolver->resolve($request, $argumentKey));
    }

    #[Test]
    public function itShouldReturnNullIfValueNotStringOnSelectedOptionOnStaticSelect(): void
    {
        $request = new Request(request: [
            'view' => [
                'state' => [
                    'values' => [
                        [
                            $argumentKey = 'argument' => [
                                'type' => 'static_select',
                                'selected_option' => [
                                    'value' => 1, // not a string
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $resolver = new InteractionStateArgumentResolver();

        $this->assertNull($resolver->resolve($request, $argumentKey));
    }

    #[Test]
    public function itShouldResolveSelectedOptionOnStaticSelect(): void
    {
        $request = new Request(request: [
            'view' => [
                'state' => [
                    'values' => [
                        [
                            $argumentKey = 'argument' => [
                                'type' => 'static_select',
                                'selected_option' => [
                                    'value' => $value = 'value', // not a string
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $resolver = new InteractionStateArgumentResolver();

        $this->assertEquals($value, $resolver->resolve($request, $argumentKey));
    }
}
