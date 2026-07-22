<?php

namespace App\Http\Requests;

use App\Models\Application;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('has_broker_fee') !== '1') {
            $this->merge(['broker_fee' => null]);
        }

        $nullableFields = [
            'memo',
            'property_documents_url',
            'appliance_support_notes',
            'contractor_english_name',
            'overseas_screening',
            ...array_keys(Application::contractDocumentFields()),
        ];

        $nullable = [];
        foreach ($nullableFields as $field) {
            $nullable[$field] = $this->filled($field) ? $this->input($field) : null;
        }

        $this->merge([
            ...$nullable,
            'broker_fee' => $this->input('has_broker_fee') === '1' ? $this->input('broker_fee') : null,
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $staffRules = ['required', 'string', 'max:255'];

        if ($this->routeIs('admin.applications.store')) {
            $staffRules[] = Rule::exists('careearth_users', 'name');
        }

        $contractDocumentRules = [];
        foreach (array_keys(Application::contractDocumentFields()) as $field) {
            $contractDocumentRules[$field] = ['nullable', 'url', 'max:2048'];
        }

        return [
            'staff_in_charge' => $staffRules,
            'contractor' => ['required', 'string', 'max:255'],
            'contractor_furigana' => ['required', 'string', 'max:255'],
            'contractor_english_name' => ['nullable', 'string', 'max:255'],
            'overseas_screening' => ['nullable', 'string', 'max:2000'],
            'property_name' => ['required', 'string', 'max:255'],
            'room_number' => ['required', 'string', 'max:255'],
            'scheduled_move_in_date' => ['required', 'date'],
            'advertising_fee' => ['required', 'integer', 'min:0'],
            'has_broker_fee' => ['required', Rule::in(['0', '1', 'undecided'])],
            'broker_fee' => ['required_if:has_broker_fee,1', 'nullable', 'integer', 'min:0'],
            'management_company_name' => ['required', 'string', 'max:255'],
            'application_method' => ['required', 'string', 'max:255'],
            'entry_method' => ['required', Rule::in(array_keys(Application::entryMethodOptions()))],
            'status' => ['required', 'string', 'max:2000'],
            'memo' => ['nullable', 'string', 'max:2000'],
            'property_documents_url' => ['nullable', 'url', 'max:2048'],
            'appliance_support_notes' => ['nullable', 'string', 'max:2000'],
            ...$contractDocumentRules,
            'customer_id' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            ...Application::columnLabels(),
            'property_name' => '物件名',
            'room_number' => '部屋番号',
        ];
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
            'staff_in_charge.exists' => '登録されている担当者を選択してください。',
        ];
    }
}
