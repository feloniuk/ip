<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request для створення нової IP адреси
 * Валідує дані та перевіряє права доступу
 */
class StoreIpAddressRequest extends FormRequest
{
    /**
     * Перевіряє, чи має користувач право на створення IP адрес
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create ip addresses') ?? false;
    }

    /**
     * Правила валідації для створення IP адреси
     */
    public function rules(): array
    {
        return [
            'ip_address' => [
                'required',
                'string',
                'ip',
                Rule::unique('ip_addresses', 'ip_address'),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (!is_string($value)) {
                        $fail('IP address must be a string.');
                        return;
                    }
                    
                    // Перевіряємо що це публічна IP адреса
                    if (!filter_var(
                        $value, 
                        FILTER_VALIDATE_IP, 
                        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
                    )) {
                        $fail('IP address must be a valid public IP address.');
                    }
                },
            ],
        ];
    }

    /**
     * Кастомні повідомлення про помилки
     */
    public function messages(): array
    {
        return [
            'ip_address.required' => 'IP address is required.',
            'ip_address.ip' => 'Please provide a valid IP address format.',
            'ip_address.unique' => 'This IP address already exists in the database.',
        ];
    }

    /**
     * Підготовка даних перед валідацією
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'ip_address' => trim((string) $this->input('ip_address', '')),
        ]);
    }
}

