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
                // Removed Rule::unique as the controller logic and database constraint handle this
            ],
            'message' => ['required', 'array'],
            'message.role' => ['required', Rule::in(['user'])],
            'message.parts' => [
                'required',
                'array',
                'min:1',
                function ($attribute, $value, $fail) {
                    $hasTextPart = collect($value)->contains(fn ($part) => $part['type'] === 'text' && ! empty($part['text']));
                    if (! $hasTextPart) {
                        $fail('The message must contain at least one text part with non-empty text.');
                    }
                },
            ],
            'message.parts.*' => ['required', 'array'],
            'message.parts.*.type' => ['required', Rule::in(['text', 'file', 'data'])],
            'message.parts.*.text' => ['required_if:message.parts.*.type,text', 'string'],
            'message.parts.*.file' => ['required_if:message.parts.*.type,file', 'array'],
            'message.parts.*.file.mimeType' => ['required_with:message.parts.*.file', 'string'],
            'message.parts.*.file.uri' => ['nullable', 'string', 'url'],
            'message.parts.*.file.bytes' => ['nullable', 'string'],
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
