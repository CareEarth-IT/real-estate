<?php

namespace App\Services;

use App\Models\SettlementManagement;
use App\Support\XlsxTemplateFiller;
use Carbon\CarbonInterface;
use RuntimeException;

class SettlementReceiptService
{
    public function __construct(
        private readonly XlsxTemplateFiller $templateFiller = new XlsxTemplateFiller,
    ) {}

    public function build(SettlementManagement $settlement): string
    {
        $templatePath = (string) config('settlement-receipt.reference');
        $cells = (array) config('settlement-receipt.cells', []);

        if ($templatePath === '' || ! is_file($templatePath)) {
            throw new RuntimeException('領収書テンプレートが見つかりません。');
        }

        $values = $this->valuesFor($settlement);
        $payload = [];

        foreach ($values as $key => $spec) {
            $address = $cells[$key] ?? null;
            if (! is_string($address) || $address === '') {
                continue;
            }

            $payload[$address] = [
                'value' => $spec['value'],
                'numeric' => (bool) ($spec['numeric'] ?? false),
            ];
        }

        return $this->templateFiller->fill($templatePath, $payload);
    }

    public function downloadFilename(SettlementManagement $settlement): string
    {
        $company = $this->safeFilenamePart($this->managementCompanyName($settlement) ?: '領収書');

        return sprintf('領収書_%s_%d_%s.xlsx', $company, (int) $settlement->id, now()->format('Ymd'));
    }

    /**
     * @return array<string, array{value: string, numeric?: bool}>
     */
    public function valuesFor(SettlementManagement $settlement): array
    {
        $bodyAmount = $this->bodyAmount($settlement);
        $taxAmount = $bodyAmount === null ? null : (int) round($bodyAmount * 0.10);

        return [
            'management_company_name' => [
                'value' => $this->billTo($settlement),
            ],
            'amount' => [
                'value' => '',
                'numeric' => true,
            ],
            'blank' => [
                'value' => '',
            ],
            'issue_date' => [
                'value' => $this->reiwaReceiptLine(now()),
            ],
            'body_amount' => [
                'value' => $bodyAmount === null ? '' : (string) $bodyAmount,
                'numeric' => $bodyAmount !== null,
            ],
            'tax_amount' => [
                'value' => $taxAmount === null ? '' : (string) $taxAmount,
                'numeric' => $taxAmount !== null,
            ],
        ];
    }

    private function bodyAmount(SettlementManagement $settlement): ?int
    {
        if ($settlement->sales_excluding_tax !== null) {
            return (int) $settlement->sales_excluding_tax;
        }

        if ($settlement->estimated_sales !== null && (int) $settlement->estimated_sales > 0) {
            return (int) round(((int) $settlement->estimated_sales) / 1.1);
        }

        if ($settlement->sales_including_tax !== null && (int) $settlement->sales_including_tax > 0) {
            return (int) round(((int) $settlement->sales_including_tax) / 1.1);
        }

        return null;
    }

    private function managementCompanyName(SettlementManagement $settlement): string
    {
        $flow = $this->related($settlement, 'flowManagement');
        if ($flow !== null && ! $flow->relationLoaded('application')) {
            if ($flow->exists) {
                $flow->loadMissing('application.customer');
            }
        }

        $name = trim((string) (
            $flow?->application?->management_company_name
            ?: $flow?->application?->customer?->management_company
            ?: $settlement->customer?->management_company
            ?: ''
        ));

        return $name;
    }

    private function billTo(SettlementManagement $settlement): string
    {
        $name = $this->managementCompanyName($settlement);
        if ($name === '') {
            return '';
        }

        if (str_ends_with($name, '御中') || str_ends_with($name, '様')) {
            return $name;
        }

        return $name.'　御中';
    }

    private function reiwaReceiptLine(CarbonInterface $date): string
    {
        $date = $date->copy()->timezone('Asia/Tokyo')->startOfDay();
        $reiwaYear = $date->year - 2018;

        return sprintf(
            '　　　　　令和　　%d年　　%d月　　%d日　上記正に領収いたしました',
            $reiwaYear,
            $date->month,
            $date->day
        );
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

    private function safeFilenamePart(string $value): string
    {
        $value = preg_replace('/[\\\\\/:\*\?"<>\|]+/u', '_', $value) ?? $value;
        $value = trim($value);

        return $value !== '' ? $value : '領収書';
    }
}
