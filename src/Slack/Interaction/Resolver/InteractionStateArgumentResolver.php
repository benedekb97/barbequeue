<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Resolver;

use App\Slack\Interaction\Exception\ValueUnchangedException;
use App\Slack\Surface\Component\Exception\UnrecognisedInputElementException;
use Symfony\Component\HttpFoundation\Request;

readonly class InteractionStateArgumentResolver
{
    /**
     * @return string|int|string[]|int[]|null
     *
     * @throws ValueUnchangedException|UnrecognisedInputElementException
     */
    public function resolve(Request $request, string $argumentKey): string|int|array|null
    {
        /** @var array{state: array} $view */
        $view = $request->request->all('view');

        /** @var array{values: array[]} $state */
        $state = $view['state'];

        foreach ($state['values'] as $value) {
            if (array_key_exists($argumentKey, $value)) {
                /** @var array{type: string, value?:string|null} $argumentValue */
                $argumentValue = $value[$argumentKey];

                return match ($type = $argumentValue['type']) {
                    'multi_static_select', 'checkboxes' => $this->resolveMultipleSelect($argumentValue),
                    'multi_users_select' => $this->resolveMultipleUserSelect($argumentValue),
                    'static_select' => $this->resolveSingleSelect($argumentValue),
                    'number_input' => $this->resolveSingleValueInteger($argumentValue),
                    'plain_text_input', 'email_input', 'url_text_input' => $this->resolveSingleValueString($argumentValue),
                    default => throw new UnrecognisedInputElementException($type),
                };
            }
        }

        return null;
    }

    /** @return int[]|string[]|null */
    private function resolveMultipleSelect(array $argumentValue): ?array
    {
        if (!array_key_exists('selected_options', $argumentValue)) {
            return null;
        }

        if (!is_array($argumentValue['selected_options'])) {
            return null;
        }

        if (empty($argumentValue['selected_options'])) {
            return [];
        }

        return array_values(array_filter(array_map(function ($selectedOption) {
            if (!is_array($selectedOption)) {
                return null;
            }

            if (!array_key_exists('value', $selectedOption)) {
                return null;
            }

            if (!is_string($selectedOption['value'])) {
                return null;
            }

            return $selectedOption['value'];
        }, $argumentValue['selected_options'])));
    }

    /** @return string[]|null */
    private function resolveMultipleUserSelect(array $argumentValue): ?array
    {
        if (!array_key_exists('selected_users', $argumentValue)) {
            return null;
        }

        /** @var string[]|null $selectedUsers */
        $selectedUsers = $argumentValue['selected_users'];

        if (!is_array($selectedUsers)) {
            return null;
        }

        if (empty($selectedUsers)) {
            return null;
        }

        return $selectedUsers;
    }

    private function resolveSingleSelect(array $argumentValue): ?string
    {
        if (!array_key_exists('selected_option', $argumentValue)) {
            return null;
        }

        if (!is_array($selectedOption = $argumentValue['selected_option'])) {
            return null;
        }

        if (empty($selectedOption)) {
            return null;
        }

        if (!array_key_exists('value', $selectedOption)) {
            return null;
        }

        if (!is_string($value = $selectedOption['value'])) {
            return null;
        }

        return $value;
    }

    /** @throws ValueUnchangedException */
    private function resolveSingleValueInteger(array $argumentValue): ?int
    {
        if (!array_key_exists('value', $argumentValue)) {
            throw new ValueUnchangedException();
        }

        /** @var string|null $value */
        $value = $argumentValue['value'];

        if (null === $value) {
            return null;
        }

        return intval($value);
    }

    /** @throws ValueUnchangedException */
    private function resolveSingleValueString(array $argumentValue): ?string
    {
        if (!array_key_exists('value', $argumentValue)) {
            throw new ValueUnchangedException();
        }

        /** @var string|null $value */
        $value = $argumentValue['value'];

        if (null === $value) {
            return null;
        }

        return $value;
    }
}
