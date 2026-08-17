<?php

namespace App\Services;

use App\Models\SettlementManagement;
use App\Support\XlsxTemplateFiller;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use RuntimeException;

class SettlementInvoiceCsvService
{
    public function __construct(
        private readonly XlsxTemplateFiller $templateFiller = new XlsxTemplateFiller,
    ) {}

    public function build(SettlementManagement $settlement): string
    {
        $templatePath = (string) config('settlement-invoice.reference');
        $cells = (array) config('settlement-invoice.cells', []);

        if ($templatePath === '' || ! is_file($templatePath)) {
            throw new RuntimeException('請求書テンプレートが見つかりません。');
        }

        $payload = [];
        foreach ($this->valuesFor($settlement) as $key => $value) {
            $address = $cells[$key] ?? null;
            if (! is_string($address) || $address === '') {
                continue;
            }

            $payload[$address] = [
                'value' => $value,
                'numeric' => $key === 'estimated_sales' && $value !== '',
            ];
        }

        return $this->templateFiller->fill($templatePath, $payload);
    }

    public function downloadFilename(SettlementManagement $settlement): string
    {
        $property = $this->safeFilenamePart($settlement->property_name ?: '請求書');

        return sprintf('請求書_%s_%d_%s.xlsx', $property, (int) $settlement->id, now()->format('Ymd'));
    }

    /**
     * @return array<string, string>
     */
    public function valuesFor(SettlementManagement $settlement): array
    {
        $flow = $this->related($settlement, 'flowManagement');
        $customer = $this->related($settlement, 'customer');

        $propertyName = trim((string) ($settlement->property_name ?: $flow?->property_name ?: ''));
        $roomNumber = trim((string) ($settlement->room_number ?: $flow?->room_number ?: ''));
        if ($propertyName !== '' && $roomNumber !== '') {
            $propertyName .= ' '.$roomNumber;
        } elseif ($propertyName === '') {
            $propertyName = $roomNumber;
        }

        $estimatedSales = $settlement->estimated_sales;

        return [
            'management_number' => trim((string) ($settlement->management_number ?: '')),
            'issue_date' => now()->format('Y/m/d'),
            'contractor' => trim((string) ($settlement->contractor ?: $flow?->contractor ?: $customer?->name ?: '')),
            'estimated_sales' => $estimatedSales === null || $estimatedSales === ''
                ? ''
                : (string) (int) $estimatedSales,
            'property_name' => $propertyName,
            'property_address' => trim((string) ($customer?->address ?: '')),
            'settlement_transfer_date' => $this->formatDate($settlement->settlement_transfer_date),
        ];
    }

    private function related(SettlementManagement $settlement, string $relation): mixed
    {
        if ($settlement->relationLoaded($relation)) {
            return $settlement->getRelation($relation);
        }

        if (! $settlement->exists) {
            return null;
        }

        $settlement->loadMissing($relation);

        return $settlement->getRelation($relation);
    }

    private function formatDate(mixed $date): string
    {
        if ($date instanceof CarbonInterface) {
            return $date->format('Y/m/d');
        }

        if (is_string($date) && trim($date) !== '') {
            return Carbon::parse($date)->format('Y/m/d');
        }

        return '';
    }

    private function safeFilenamePart(string $value): string
    {
        $value = preg_replace('/[\\\\\/:\*\?"<>\|]+/u', '_', $value) ?? $value;
        $value = trim($value);

        return $value !== '' ? $value : '請求書';
    }
}
