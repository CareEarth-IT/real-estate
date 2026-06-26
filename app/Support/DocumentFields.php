<?php

namespace App\Support;

final class DocumentFields
{
    /** @var array<string, array{label: string, short: string, prefix: string}> */
    private const FIELDS = [
        'purchase_certificate' => [
            'label' => '買付証明書',
            'short' => '買付',
            'prefix' => 'purchase_cert',
        ],
        'seal_certificate' => [
            'label' => '印鑑証明書',
            'short' => '印鑑',
            'prefix' => 'seal_cert',
        ],
        'registry_certificate' => [
            'label' => '登記事項証明書',
            'short' => '登記',
            'prefix' => 'registry_cert',
        ],
        'property_registry' => [
            'label' => '不動産登記謄本',
            'short' => '謄本',
            'prefix' => 'property_reg',
        ],
    ];

    /** @return list<string> */
    public static function keys(): array
    {
        return array_keys(self::FIELDS);
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return array_map(
            fn (array $field): string => $field['label'],
            self::FIELDS,
        );
    }

    /** @return array<string, string> */
    public static function shortLabels(): array
    {
        return array_map(
            fn (array $field): string => $field['short'],
            self::FIELDS,
        );
    }

    /** @return array<string, string> */
    public static function uploadPrefixes(): array
    {
        return array_map(
            fn (array $field): string => $field['prefix'],
            self::FIELDS,
        );
    }

    public static function isValid(string $field): bool
    {
        return isset(self::FIELDS[$field]);
    }

    public static function uploadPrefix(string $field): string
    {
        return self::FIELDS[$field]['prefix'];
    }
}
