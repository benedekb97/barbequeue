<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Inputs;

use App\Slack\Block\Component\DividerBlock;
use App\Slack\Block\Component\InputBlock;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Surface\Component\Exception\UnrecognisedInputElementException;
use App\Slack\Surface\Component\Modal;
use App\Slack\Surface\Component\ModalArgument;
use App\Slack\Surface\Factory\Exception\NoOptionsAvailableException;
use App\Slack\Surface\Factory\InputElementFactory;
use App\Slack\Surface\Factory\Resolver\DefaultValueResolverInterface;
use App\Slack\Surface\Factory\Resolver\OptionsResolverInterface;
use Psr\Log\LoggerInterface;

class ModalInputsFactory implements ModalInputsFactoryInterface
{
    /** @var DefaultValueResolverInterface[] */
    private iterable $defaultValueResolvers = [];

    /** @var OptionsResolverInterface[] */
    private iterable $optionsResolvers = [];

    public function __construct(
        private readonly InputElementFactory $inputElementFactory,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function create(Modal $modal): array
    {
        $blocks = [];

        foreach ($modal->getFields() as $field) {
            if (!empty($blocks)) {
                $blocks[] = new DividerBlock();
            }

            try {
                $this->logger->debug(
                    sprintf('Creating input block for modal: %s:%s', $modal->value, $field->value)
                );

                $this->inputElementFactory
                    ->setDefaultValueResolver($this->getDefaultValueResolver($field))
                    ->setOptionsResolver($this->getOptionsResolver($field));

                $blocks[] = $this->getBlockForField($field);
                $blocks[] = $this->getExplanationForField($field);
            } catch (UnrecognisedInputElementException $exception) {
                $this->logger->error('Unrecognised input element: '.$exception->getInputElementType());
            } catch (NoOptionsAvailableException) {
                continue;
            }
        }

        return array_values(array_filter($blocks));
    }

    /** @param DefaultValueResolverInterface[] $defaultValueResolvers */
    public function setDefaultValueResolvers(iterable $defaultValueResolvers): static
    {
        $this->defaultValueResolvers = $defaultValueResolvers;

        return $this;
    }

    /** @param OptionsResolverInterface[] $optionsResolvers */
    public function setOptionsResolvers(iterable $optionsResolvers): static
    {
        $this->optionsResolvers = $optionsResolvers;

        return $this;
    }

    private function getDefaultValueResolver(ModalArgument $argument): ?DefaultValueResolverInterface
    {
        foreach ($this->defaultValueResolvers as $resolver) {
            if ($resolver->getSupportedArgument() === $argument) {
                return $resolver;
            }
        }

        return null;
    }

    private function getOptionsResolver(ModalArgument $argument): ?OptionsResolverInterface
    {
        foreach ($this->optionsResolvers as $resolver) {
            if ($resolver->getSupportedArgument() === $argument) {
                return $resolver;
            }
        }

        return null;
    }

    private function getExplanationForField(ModalArgument $argument): ?SectionBlock
    {
        if (null === $argument->getExplanation()) {
            return null;
        }

        return new SectionBlock($argument->getExplanation());
    }

    /** @throws UnrecognisedInputElementException|NoOptionsAvailableException */
    private function getBlockForField(ModalArgument $field): InputBlock
    {
        return new InputBlock(
            $field->getLabel() ?? '',
            $this->inputElementFactory->create($field),
            $field->hasDispatchedAction(),
            hint: $field->getHint(),
            optional: !$field->isRequired(),
        );
    }
}
