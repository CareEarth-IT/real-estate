<?php

namespace App\Support;

use App\Models\Application;

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

        return Application::create([
            ...collect($validated)->except(['has_broker_fee', 'broker_fee', 'customer_id'])->all(),
            'customer_id' => null,
            'has_broker_fee' => $hasBrokerFee,
            'broker_fee' => $hasBrokerFee === true ? $validated['broker_fee'] : null,
            'sales_action_required' => false,
            'screening_ok' => false,
            'is_cancelled' => false,
        ]);
    }
}
