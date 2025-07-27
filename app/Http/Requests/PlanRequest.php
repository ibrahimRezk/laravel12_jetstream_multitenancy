<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlanRequest extends FormRequest
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
                'required',
                'string',
                'max:255',
                Rule::unique('plans', 'name')->ignore($this->plan) // Ignore the current plan if updating
            ],
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'interval' => 'required|string|in:daily,weekly,monthly,yearly', // Add other intervals as needed
            'features' => 'nullable|array',
            'features.*' => 'string|max:255', // Each feature should be a string
            'is_active' => 'boolean',
            'trial_days' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:0'
        ];
    }
}
