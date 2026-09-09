<?php

namespace App\Http\Requests;

use App\Models\Assignment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssignmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isKomtingOrAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'type' => ['required', Rule::in([Assignment::TYPE_INDIVIDUAL, Assignment::TYPE_GROUP])],
            'submission_mode' => ['sometimes', 'required', Rule::in(Assignment::submissionModes())],
            'max_files' => ['required_if:submission_mode,file,file_link', 'integer', 'min:1', 'max:20'],
            'allowed_extensions' => ['sometimes', 'array', 'min:1', 'max:20'],
            'allowed_extensions.*' => ['string', Rule::in(array_merge(Assignment::defaultExtensionList(), ['txt', 'zip', 'jpg', 'jpeg', 'png']))],
            'max_file_size_kb' => ['required_if:submission_mode,file,file_link', 'integer', 'min:100', 'max:102400'],
        ];
    }
}
