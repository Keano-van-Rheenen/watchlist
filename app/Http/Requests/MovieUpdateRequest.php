<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MovieUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['required', 'string'],
            'duration' => ['required', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
            'picture' => ['nullable', 'image', 'max:5120'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $duration = $this->input('duration');

        if (is_string($duration) && strlen($duration) === 5) {
            $this->merge([
                'duration' => $duration.':00',
            ]);
        }
    }
}
