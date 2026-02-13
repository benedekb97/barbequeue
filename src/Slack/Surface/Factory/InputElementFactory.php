<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory;

use App\Slack\BlockElement\Component\CheckboxesElement;
use App\Slack\BlockElement\Component\EmailInputElement;
use App\Slack\BlockElement\Component\MultiStaticSelectElement;
use App\Slack\BlockElement\Component\MultiUsersSelectElement;
use App\Slack\BlockElement\Component\NumberInputElement;
use App\Slack\BlockElement\Component\PlainTextInputElement;
use App\Slack\BlockElement\Component\SlackBlockElement;
use App\Slack\BlockElement\Component\StaticSelectElement;
use App\Slack\BlockElement\Component\UrlInputElement;
use App\Slack\Surface\Component\Exception\UnrecognisedInputElementException;
use App\Slack\Surface\Component\ModalArgument;
use App\Slack\Surface\Factory\Exception\NoOptionsAvailableException;
use App\Slack\Surface\Factory\Resolver\DefaultValueResolverInterface;
use App\Slack\Surface\Factory\Resolver\OptionsResolverInterface;

class InputElementFactory
{
    private ?DefaultValueResolverInterface $defaultValueResolver = null;

    private ?OptionsResolverInterface $optionsResolver = null;

    public function setDefaultValueResolver(?DefaultValueResolverInterface $defaultValueResolver): static
    {
        $this->defaultValueResolver = $defaultValueResolver;

        return $this;
    }

    public function setOptionsResolver(?OptionsResolverInterface $optionsResolver): static
    {
        $this->optionsResolver = $optionsResolver;

        return $this;
    }

    /**
     * @throws UnrecognisedInputElementException
     * @throws NoOptionsAvailableException
     */
    public function create(ModalArgument $argument): SlackBlockElement
    {
        return match ($fieldType = $argument->getFieldType()) {
            EmailInputElement::class => new EmailInputElement(
                actionId: $argument->value,
                initialValue: $this->defaultValueResolver?->resolveString(),
                placeholder: $argument->getPlaceholder(),
            ),
            UrlInputElement::class => new UrlInputElement(
                actionId: $argument->value,
                initialValue: $this->defaultValueResolver?->resolveString(),
                placeholder: $argument->getPlaceholder(),
            ),
            NumberInputElement::class => new NumberInputElement(
                isDecimalAllowed: false,
                actionId: $argument->value,
                initialValue: $this->defaultValueResolver?->resolveString(),
                placeholder: $argument->getPlaceholder(),
            ),
            PlainTextInputElement::class => new PlainTextInputElement(
                actionId: $argument->value,
                initialValue: $this->defaultValueResolver?->resolveString(),
                placeholder: $argument->getPlaceholder(),
            ),
            MultiStaticSelectElement::class => new MultiStaticSelectElement(
                actionId: $argument->value,
                placeholder: $argument->getPlaceholder(),
                options: $this->optionsResolver?->resolve() ?? [],
                initialOptions: $this->defaultValueResolver?->resolveArray() ?? [],
            ),
            StaticSelectElement::class => new StaticSelectElement(
                actionId: $argument->value,
                placeholder: $argument->getPlaceholder(),
                options: $this->optionsResolver?->resolve() ?? [],
                initialOption: $this->defaultValueResolver?->resolveArray() ?? [],
            ),
            MultiUsersSelectElement::class => new MultiUsersSelectElement(
                actionId: $argument->value,
                placeholder: $argument->getPlaceholder(),
            ),
            CheckboxesElement::class => new CheckboxesElement(
                actionId: $argument->value,
                options: $this->optionsResolver?->resolve() ?? [],
                initialOptions: $this->defaultValueResolver?->resolveArray() ?? [],
            ),
            default => throw new UnrecognisedInputElementException($fieldType),
        };
    }
}
