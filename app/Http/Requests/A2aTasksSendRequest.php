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
                'unique:tasks,a2a_task_id,NULL,id,deleted_at,NULL', // Check for unique task ID
            ],
            'message' => ['required', 'array'],
            'message.role' => ['required', Rule::in(['user'])],
            'message.parts' => ['required', 'array', 'min:1'],
            'message.parts.*' => ['required', 'array'],
            'message.parts.*.type' => ['required', Rule::in(['text', 'file', 'data'])],
            'message.parts.*.text' => ['required_if:message.parts.*.type,text', 'string'],
            'message.parts.*.file' => ['required_if:message.parts.*.type,file', 'array'],
            'message.parts.*.file.mimeType' => ['required', 'string'],
            'message.parts.*.file.uri' => ['required_without:message.parts.*.file.bytes', 'string', 'url'],
            'message.parts.*.file.bytes' => ['required_without:message.parts.*.file.uri', 'string'],
            'message.parts.*.data' => ['required_if:message.parts.*.type,data', 'array'],
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
