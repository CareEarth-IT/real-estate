<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'memo' => $this->filled('memo') ? $this->input('memo') : null,
            'property_documents_url' => $this->filled('property_documents_url') ? $this->input('property_documents_url') : null,
            'appliance_support_notes' => $this->filled('appliance_support_notes') ? $this->input('appliance_support_notes') : null,
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'staff_in_charge' => ['required', 'string', 'max:255'],
            'property_name_room' => ['required', 'string', 'max:255'],
            'scheduled_move_in_date' => ['required', 'date'],
            'advertising_fee' => ['required', 'integer', 'min:0'],
            'management_company_name' => ['required', 'string', 'max:255'],
            'application_method' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'max:2000'],
            'memo' => ['nullable', 'string', 'max:2000'],
            'property_documents_url' => ['nullable', 'url', 'max:2048'],
            'appliance_support_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return \App\Models\Application::columnLabels();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => ':attributeは必須です。',
            'url' => ':attributeの形式が正しくありません。',
            'date' => ':attributeは正しい日付を入力してください。',
            'integer' => ':attributeは整数で入力してください。',
            'min' => ':attributeは:min以上で入力してください。',
        ];
    }
}
