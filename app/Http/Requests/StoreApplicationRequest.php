<?php

namespace App\Http\Requests;

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

        $this->merge([
            'memo' => $this->filled('memo') ? $this->input('memo') : null,
            'property_documents_url' => $this->filled('property_documents_url') ? $this->input('property_documents_url') : null,
            'appliance_support_notes' => $this->filled('appliance_support_notes') ? $this->input('appliance_support_notes') : null,
            'broker_fee' => $this->input('has_broker_fee') === '1' ? $this->input('broker_fee') : null,
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'staff_in_charge' => ['required', 'string', 'max:255'],
            'property_name' => ['required', 'string', 'max:255'],
            'room_number' => ['required', 'string', 'max:255'],
            'scheduled_move_in_date' => ['required', 'date'],
            'advertising_fee' => ['required', 'integer', 'min:0'],
            'has_broker_fee' => ['required', Rule::in(['0', '1', 'undecided'])],
            'broker_fee' => ['required_if:has_broker_fee,1', 'nullable', 'integer', 'min:0'],
            'management_company_name' => ['required', 'string', 'max:255'],
            'application_method' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'max:2000'],
            'memo' => ['nullable', 'string', 'max:2000'],
            'property_documents_url' => ['nullable', 'url', 'max:2048'],
            'appliance_support_notes' => ['nullable', 'string', 'max:2000'],
            'customer_id' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            ...\App\Models\Application::columnLabels(),
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
        ];
    }
}
