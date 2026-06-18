<?php

declare(strict_types=1);

namespace App\Http\Requests\Marketplace;

use Illuminate\Foundation\Http\FormRequest;

class SubmitBidRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'proposed_rate' => ['required', 'numeric', 'min:1'],
            'cover_letter' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
