<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApplicationRequest;
use App\Models\Application;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ApplicationFormController extends Controller
{
    public function create(Customer $customer): View
    {
        $defaults = [
            'property_name_room' => trim($customer->property_name.' '.$customer->room_number),
            'scheduled_move_in_date' => $customer->move_in_date?->format('Y-m-d'),
            'management_company_name' => $customer->management_company,
        ];

        return view('applications.create', compact('customer', 'defaults'));
    }

    public function store(StoreApplicationRequest $request, Customer $customer): RedirectResponse
    {
        $application = Application::create([
            ...$request->validated(),
            'customer_id' => $customer->id,
            'sales_action_required' => false,
            'screening_ok' => false,
            'is_cancelled' => false,
        ]);

        return redirect()
            ->route('applications.complete', $application)
            ->with('success', '申込情報を登録しました。');
    }

    public function complete(Application $application): View
    {
        $application->load('customer');

        return view('applications.complete', compact('application'));
    }
}
