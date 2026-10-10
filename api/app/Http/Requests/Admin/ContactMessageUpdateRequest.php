<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;

class ContactMessageUpdateRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_read' => ['nullable', 'boolean'],
        ];
    }
}
