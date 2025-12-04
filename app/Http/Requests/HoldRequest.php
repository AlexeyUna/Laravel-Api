<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class HoldRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }

    protected function prepareForValidation(): void
    {
        $key = $this->header('Idempotency-Key');

        if (!$key || !is_string($key)) {
            throw ValidationException::withMessages([
                'Idempotency-Key' => 'The Idempotency-Key header is required'
            ]);
        }
    }

    public function idempotencyKey(): string
    {
        return $this->header('Idempotency-Key');
    }
}
