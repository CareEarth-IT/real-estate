<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Application;
use App\Models\Customer;
use App\Models\FlowManagement;
use App\Models\SettlementManagement;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    public function show(Application $application): JsonResponse
    {
        if (! $application->screening_ok) {
            return response()->json([
                'message' => '審査ＯＫの申込のみ顧客情報を入力できます。',
            ], 422);
        }

        $customer = $application->customer;

        return response()->json([
            'customer' => [
                'case_number' => $customer?->case_number,
                'display_customer_id' => $customer?->displayCustomerId(),
            ],
            'form' => $this->formValuesForApplication($application, $customer),
        ]);
    }

    public function update(UpdateCustomerRequest $request, Application $application): JsonResponse
    {
        if (! $application->screening_ok) {
            return response()->json([
                'message' => '審査ＯＫの申込のみ顧客情報を更新できます。',
            ], 422);
        }

        $customerData = [
            ...$request->validated(),
            'customer_info_completed' => true,
        ];

        $customer = $application->customer;

        if ($customer !== null) {
            $customer->update($customerData);
        } else {
            $customer = Customer::create($customerData);
        }

        $this->syncCustomerLinks($application, $customer);

        return response()->json([
            'success' => true,
            'message' => '顧客情報を保存しました。',
            'case_number' => $customer->case_number,
            'display_customer_id' => $customer->displayCustomerId(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formValuesForApplication(Application $application, ?Customer $customer): array
    {
        if ($customer?->customer_info_completed) {
            return $this->customerToFormValues($customer);
        }

        $propertyFields = $this->propertyFieldsFromApplication($application);

        return [
            'property_name' => $propertyFields['property_name'],
            'room_number' => $propertyFields['room_number'],
            'management_company' => (string) ($application->management_company_name ?? ''),
            'address' => '',
            'move_in_date' => '',
            'contract_period' => '',
            'contract_period_type' => '',
            'name' => '',
            'date_of_birth' => '',
            'is_married' => '',
            'mobile_number' => '',
            'email' => '',
            'occupation' => '',
            'company_or_school_name' => '',
            'company_or_school_phone' => '',
            'company_or_school_address' => '',
            'emergency_contact_name' => '',
            'emergency_contact_relationship' => '',
            'emergency_contact_date_of_birth' => '',
            'emergency_contact_address' => '',
            'emergency_contact_mobile' => '',
            'emergency_contact_email' => '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function customerToFormValues(Customer $customer): array
    {
        return [
            'property_name' => $customer->property_name,
            'room_number' => $customer->room_number,
            'management_company' => $customer->management_company,
            'address' => $customer->address,
            'move_in_date' => $customer->move_in_date?->format('Y-m-d') ?? '',
            'contract_period' => preg_replace('/年$/u', '', (string) $customer->contract_period),
            'contract_period_type' => $customer->contract_period_type ? '1' : '0',
            'name' => $customer->name,
            'date_of_birth' => $customer->date_of_birth?->format('Y-m-d') ?? '',
            'is_married' => $customer->is_married ? '1' : '0',
            'mobile_number' => $customer->mobile_number,
            'email' => $customer->email,
            'occupation' => $customer->occupation,
            'company_or_school_name' => $customer->company_or_school_name,
            'company_or_school_phone' => $customer->company_or_school_phone,
            'company_or_school_address' => $customer->company_or_school_address,
            'emergency_contact_name' => $customer->emergency_contact_name,
            'emergency_contact_relationship' => $customer->emergency_contact_relationship,
            'emergency_contact_date_of_birth' => $customer->emergency_contact_date_of_birth?->format('Y-m-d') ?? '',
            'emergency_contact_address' => $customer->emergency_contact_address,
            'emergency_contact_mobile' => $customer->emergency_contact_mobile,
            'emergency_contact_email' => $customer->emergency_contact_email,
        ];
    }

    private function syncCustomerLinks(Application $application, Customer $customer): void
    {
        $application->update(['customer_id' => $customer->id]);

        FlowManagement::query()
            ->where('application_id', $application->id)
            ->update(['customer_id' => $customer->id]);

        SettlementManagement::query()
            ->whereHas('flowManagement', fn ($query) => $query->where('application_id', $application->id))
            ->update(['customer_id' => $customer->id]);
    }

    /**
     * @return array{property_name: string, room_number: string}
     */
    private function propertyFieldsFromApplication(Application $application): array
    {
        return [
            'property_name' => (string) ($application->property_name ?? ''),
            'room_number' => (string) ($application->room_number ?? ''),
        ];
    }
}
