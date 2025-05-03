<?php

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // Import Task model

class A2aTasksSendRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * For A2A, authorization is typically handled by middleware (e.g., auth:sanctum).
     */
    public function authorize(): bool
    {
        return true; // Handled by route middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'taskId' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tasks', 'a2a_task_id')->where(function ($query) {
                    return $query->whereNull('id');
                }),
            ],
            'message' => ['required', 'array'],
            'message.role' => ['required', Rule::in(['user'])],
            'message.parts' => ['required', 'array', 'min:1'],
            'message.parts.*' => ['required', 'array'],
            'message.parts.*.type' => ['required', Rule::in(['text', 'file', 'data'])],
            'message.parts.*.text' => ['required_if:message.parts.*.type,text', 'string'],
            'message.parts.*.mimeType' => ['required_if:message.parts.*.type,file', 'string'],
            'message.parts.*.uri' => ['required_if:message.parts.*.type,file', 'string', 'url'],
            'message.parts.*.jsonData' => ['required_if:message.parts.*.type,data', 'json'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'taskId.unique' => 'A task with this ID already exists or is processing. Use a different ID for new tasks.',
            'message.role.in' => 'The initial message role must be "user".',
        ];
    }
}
