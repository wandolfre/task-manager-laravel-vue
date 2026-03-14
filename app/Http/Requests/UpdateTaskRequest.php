<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates incoming data for updating an existing task.
 *
 * Uses 'sometimes' on all fields to support partial updates (PATCH-style),
 * allowing the client to send only the fields they want to change.
 */
class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Ownership is checked in the controller via abort(403).
     * Route-level auth:sanctum middleware ensures the user is authenticated.
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
            // Title can be updated but must remain non-empty when provided
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            // Description can be cleared (null) or updated
            'description' => ['nullable', 'string'],
            // Completed status toggle — must be a boolean when provided
            'completed' => ['sometimes', 'boolean'],
            // Due date can be changed or cleared
            'due_date' => ['nullable', 'date'],
        ];
    }
}
