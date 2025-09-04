<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteIpAddressRequest extends FormRequest
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
            'id' => ['required', 'integer', 'min:1', 'exists:ip_addresses,id']
        ];
    }

    public function getValidatedId(): int
    {
        return $this->validated()['id'];
    }
}