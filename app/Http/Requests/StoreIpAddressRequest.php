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
        return true;
    }

    /**
     * Правила валідації для створення IP адреси
     */
    public function rules(): array
    {
        return [
            'ip_address' => ['required', 'string', 'ip'],
        ];
    }
}

