<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest extends FormRequest
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
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'status' => 'sometimes|in:pending,in_progress,answered,closed',
        ];
    }

    public function prepareForValidation()
    {
        $data = [];

        if ($this->filled('title')) {
            $data['title'] = trim($this->title);
        }

        if ($this->filled('description')) {
            $data['description'] = trim($this->description);
        }

        if (!empty($data)) {
            $this->merge($data);
        }
    }
}
