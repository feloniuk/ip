<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request для оновлення IP адреси
 * В основному використовується для оновлення геолокаційних даних
 */
class UpdateIpAddressRequest extends FormRequest
{
    /**
     * Перевіряє права на оновлення IP адреси
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update ip addresses') ?? false;
    }

    /**
     * Правила валідації для оновлення
     */
    public function rules(): array
    {
        return [
            'force_refresh' => 'sometimes|boolean',
        ];
    }

    /**
     * Підготовка даних перед валідацією
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'force_refresh' => $this->boolean('force_refresh', false),
        ]);
    }
}