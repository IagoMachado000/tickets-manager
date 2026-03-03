<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterUserRequest extends FormRequest
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
            'name' => 'required|string|max:255',

            'email' => 'required|email|unique:users,email',

            'password' => 'required|string|min:6|confirmed',

            'role' => 'required|in:user,support',

            'project_id' => [
                'nullable',
                'exists:projects,id',
                Rule::requiredIf(fn() => $this->role === 'user'),
                Rule::prohibitedIf(fn() => $this->role === 'support'),
            ],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $allowed = [
                'name',
                'email',
                'password',
                'password_confirmation',
                'role',
                'project_id',
            ];

            $extraFields = array_diff(
                array_keys($this->all()),
                $allowed
            );

            if (!empty($extraFields)) {
                $validator->errors()->add(
                    'invalid_fields',
                    'Campos inválidos enviados: ' . implode(', ', $extraFields)
                );
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('name')) {
            $data['name'] = trim($this->name);
        }

        if ($this->has('email')) {
            $data['email'] = mb_strtolower(trim($this->email));
        }

        if ($this->has('password')) {
            $data['password'] = trim($this->password);
        }

        // Define o valor padrão para 'role' como 'user' se não for fornecido
        $data['role'] = $this->filled('role')
            ? $this->role
            : 'user';

        if (!empty($data)) {
            $this->merge($data);
        }
    }
}
