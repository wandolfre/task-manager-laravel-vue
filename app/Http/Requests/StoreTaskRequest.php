<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates incoming data for creating a new task.
 *
 * Authorization is handled at the route level via auth:sanctum middleware,
 * so authorize() always returns true here.
 */
class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Any authenticated user can create tasks; route middleware enforces auth.
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
            // Title is mandatory — every task needs at minimum a short summary
            'title' => ['required', 'string', 'max:255'],
            // Description is optional — allows detailed notes but isn't required
            'description' => ['nullable', 'string'],
            // Completed defaults to false on the model; accept boolean if provided
            'completed' => ['sometimes', 'boolean'],
            // Due date is optional — must be a valid date format when provided
            'due_date' => ['nullable', 'date'],
        ];
    }
}
