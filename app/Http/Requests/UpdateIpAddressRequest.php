<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIpAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'id' => (int) $this->route('id')
        ]);
    }

    public function rules(): array
    {
        return [
            'ip_address' => ['sometimes', 'string', 'ip'],
            'id' => ['required', 'integer']
        ];
    }

    public function getValidatedId(): int
    {
        return $this->validated()['id'];
    }
}