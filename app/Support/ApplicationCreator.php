<?php

namespace App\Support;

use App\Models\Application;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

final class ApplicationCreator
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public static function create(array $validated): Application
    {
        $hasBrokerFee = match ($validated['has_broker_fee'] ?? null) {
            '1', true => true,
            '0', false => false,
            'undecided', null => null,
            default => null,
        };

        return DB::transaction(function () use ($validated, $hasBrokerFee) {
            $customer = Customer::query()->create([
                'name' => filled($validated['contractor'] ?? null) ? (string) $validated['contractor'] : null,
                'property_name' => filled($validated['property_name'] ?? null) ? (string) $validated['property_name'] : null,
                'room_number' => filled($validated['room_number'] ?? null) ? (string) $validated['room_number'] : null,
                'management_company' => filled($validated['management_company_name'] ?? null)
                    ? (string) $validated['management_company_name']
                    : null,
                'move_in_date' => $validated['scheduled_move_in_date'] ?? null,
                'customer_info_completed' => false,
            ]);

            return Application::query()->create([
                ...collect($validated)->except(['has_broker_fee', 'broker_fee', 'customer_id'])->all(),
                'customer_id' => $customer->id,
                'has_broker_fee' => $hasBrokerFee,
                'broker_fee' => $hasBrokerFee === true ? ($validated['broker_fee'] ?? null) : null,
                'sales_action_required' => false,
                'screening_ok' => false,
                'is_cancelled' => false,
            ]);
        });
    }
}
