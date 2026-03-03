<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
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
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('projects', 'name')->ignore($this->project),
            ],
            'description' => 'sometimes|nullable|string',
        ];
    }

    public function prepareForValidation()
    {
        $data = [];

        if ($this->filled('name')) {
            $data['name'] = trim($this->name);
        }

        if ($this->filled('description')) {
            $data['description'] = trim($this->description);
        }

        if (!empty($data)) {
            $this->merge($data);
        }
    }
}
