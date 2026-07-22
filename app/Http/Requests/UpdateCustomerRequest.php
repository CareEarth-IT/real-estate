<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('contract_period_type') && $this->input('contract_period_type') !== '') {
            $this->merge([
                'contract_period_type' => filter_var(
                    $this->input('contract_period_type'),
                    FILTER_VALIDATE_BOOLEAN,
                    FILTER_NULL_ON_FAILURE
                ),
            ]);
        }

        if ($this->has('is_married') && $this->input('is_married') !== '') {
            $this->merge([
                'is_married' => filter_var(
                    $this->input('is_married'),
                    FILTER_VALIDATE_BOOLEAN,
                    FILTER_NULL_ON_FAILURE
                ),
            ]);
        }

        if ($this->filled('contract_period')) {
            $contractPeriod = trim((string) $this->input('contract_period'));
            if (preg_match('/^\d+$/', $contractPeriod)) {
                $contractPeriod .= '年';
            }
            $this->merge(['contract_period' => $contractPeriod]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'move_in_date' => ['required', 'date'],
            'contract_period' => ['required', 'string', 'max:50', 'regex:/^\d+年$/'],
            'contract_period_type' => ['required', 'boolean'],
            'property_name' => ['required', 'string', 'max:255'],
            'room_number' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
            'management_company' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'is_married' => ['required', 'boolean'],
            'mobile_number' => ['required', 'string', 'max:50', 'regex:/^[\d\-+() ]+$/'],
            'email' => ['required', 'email', 'max:255'],
            'occupation' => ['required', 'string', 'max:255'],
            'company_or_school_name' => ['required', 'string', 'max:255'],
            'company_or_school_phone' => ['required', 'string', 'max:50', 'regex:/^[\d\-+() ]+$/'],
            'company_or_school_address' => ['required', 'string', 'max:1000'],
            'emergency_contact_name' => ['required', 'string', 'max:255'],
            'emergency_contact_relationship' => ['required', 'string', 'max:255'],
            'emergency_contact_date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'emergency_contact_address' => ['required', 'string', 'max:1000'],
            'emergency_contact_mobile' => ['required', 'string', 'max:50', 'regex:/^[\d\-+() ]+$/'],
            'emergency_contact_email' => ['required', 'email', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return \App\Models\Customer::columnLabels();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => ':attributeは必須です。',
            'email' => ':attributeの形式が正しくありません。',
            'date' => ':attributeは正しい日付を入力してください。',
            'boolean' => ':attributeを選択してください。',
            'regex' => ':attributeの形式が正しくありません。',
            'before_or_equal' => ':attributeは本日以前の日付を入力してください。',
        ];
    }
}
