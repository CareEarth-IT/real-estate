<?php

namespace App\Services;

use App\Models\Property;
use App\Models\PropertyAddress;
use App\Models\SalesPerson;
use App\Support\DocumentFields;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class PropertyService
{
    public function __construct(
        private readonly DocumentService $documentService,
    ) {}

    public function buildPropertyAddress(string $prefecture, string $city, string $street, string $building): string
    {
        $base = trim($prefecture).trim($city).trim($street);
        $building = trim($building);

        return $building !== '' ? $base.' '.$building : $base;
    }

    /** @param  array<string, mixed>  $formData */
    public function validate(array $formData): ?string
    {
        if (($formData['buyer_name'] ?? '') === '') {
            return '購入者は必須項目です。';
        }

        $addressMode = $formData['address_mode'] ?? 'existing';

        if ($addressMode === 'new') {
            if (($formData['prefecture'] ?? '') === '') {
                return '都道府県を選択してください。';
            }
            if (($formData['city'] ?? '') === '') {
                return '市町村を入力してください。';
            }
            if (($formData['street'] ?? '') === '') {
                return '丁目・番地・号を入力してください。';
            }
        } elseif (($formData['property_address'] ?? '') === '') {
            return '物件住所を選択してください。';
        }

        if (($formData['property_address'] ?? '') === '') {
            return '物件住所を入力してください。';
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $post
     * @param  array<string, mixed>  $defaults
     * @return array<string, mixed>
     */
    public function parseForm(array $post, array $defaults = []): array
    {
        $formData = array_merge($this->emptyFormData(), $defaults);

        $formData = array_merge($formData, [
            'created_at' => $post['created_at'] ?? $formData['created_at'],
            'sales_person' => trim($post['sales_person'] ?? ''),
            'sales_person_mode' => $post['sales_person_mode'] ?? 'existing',
            'new_sales_person' => trim($post['new_sales_person'] ?? ''),
            'buyer_name' => trim($post['buyer_name'] ?? ''),
            'broker_name' => trim($post['broker_name'] ?? ''),
            'owner_name' => trim($post['owner_name'] ?? ''),
            'property_address' => trim($post['property_address'] ?? ''),
            'address_mode' => $post['address_mode'] ?? 'existing',
            'prefecture' => trim($post['prefecture'] ?? ''),
            'city' => trim($post['city'] ?? ''),
            'street' => trim($post['street'] ?? ''),
            'building_name' => trim($post['building_name'] ?? ''),
            'building_price' => $this->parseAmount($post['building_price'] ?? '0'),
            'land_price' => $this->parseAmount($post['land_price'] ?? '0'),
            'total_price' => $this->parseAmount($post['total_price'] ?? '0'),
            'price_mode' => ($post['price_mode'] ?? 'split') === 'total' ? 'total' : 'split',
            'registration_fee' => $this->parseAmount($post['registration_fee'] ?? '0'),
            'brokerage_fee' => $this->parseAmount($post['brokerage_fee'] ?? '0'),
            'property_tax' => $this->parseAmount($post['property_tax'] ?? '0'),
        ]);

        if ($formData['address_mode'] === 'new') {
            $formData['property_address'] = $this->buildPropertyAddress(
                $formData['prefecture'],
                $formData['city'],
                $formData['street'],
                $formData['building_name'],
            );
        }

        if ($formData['sales_person_mode'] === 'new') {
            $formData['sales_person'] = $formData['new_sales_person'];
        }

        return $formData;
    }

    /** @param  array<string, mixed>|Property  $property */
    public function detectPriceMode(array|Property $property): string
    {
        $data = $property instanceof Property ? $property->toArray() : $property;

        if (($data['price_mode'] ?? '') === 'total') {
            return 'total';
        }

        if ((int) ($data['building_price'] ?? 0) === 0
            && (int) ($data['land_price'] ?? 0) === 0
            && (int) ($data['total_price'] ?? 0) > 0) {
            return 'total';
        }

        return 'split';
    }

    /** @param  array<string, mixed>  $formData
     * @return array{building_price: int, land_price: int, total_price: int, price_mode: string}
     */
    public function resolvePrices(array $formData): array
    {
        if (($formData['price_mode'] ?? 'split') === 'total') {
            return [
                'building_price' => 0,
                'land_price' => 0,
                'total_price' => (int) $formData['total_price'],
                'price_mode' => 'total',
            ];
        }

        $building = (int) $formData['building_price'];
        $land = (int) $formData['land_price'];

        return [
            'building_price' => $building,
            'land_price' => $land,
            'total_price' => $building + $land,
            'price_mode' => 'split',
        ];
    }

    /** @param  array<string, mixed>  $formData
     * @return array<string, mixed>
     */
    public function propertyDataFromForm(array $formData): array
    {
        $prices = $this->resolvePrices($formData);

        return [
            'created_at' => str_replace('T', ' ', $formData['created_at']).':00',
            'buyer_name' => $formData['buyer_name'],
            'broker_name' => $formData['broker_name'],
            'owner_name' => $formData['owner_name'],
            'property_address' => $formData['property_address'],
            'building_price' => $prices['building_price'],
            'land_price' => $prices['land_price'],
            'total_price' => $prices['total_price'],
            'price_mode' => $prices['price_mode'],
            'registration_fee' => $formData['registration_fee'],
            'brokerage_fee' => $formData['brokerage_fee'],
            'property_tax' => $formData['property_tax'],
            'sales_person' => $formData['sales_person'],
        ];
    }

    /** @return array<string, mixed> */
    public function propertyToFormData(Property $property): array
    {
        $priceMode = $this->detectPriceMode($property);

        return [
            'created_at' => $property->created_at?->format('Y-m-d\TH:i') ?? date('Y-m-d\TH:i'),
            'sales_person' => $property->sales_person ?? '',
            'sales_person_mode' => 'existing',
            'new_sales_person' => '',
            'buyer_name' => $property->buyer_name,
            'broker_name' => $property->broker_name ?? '',
            'owner_name' => $property->owner_name ?? '',
            'property_address' => $property->property_address,
            'address_mode' => 'existing',
            'prefecture' => '',
            'city' => '',
            'street' => '',
            'building_name' => '',
            'building_price' => $priceMode === 'total' ? 0 : (int) $property->building_price,
            'land_price' => $priceMode === 'total' ? 0 : (int) $property->land_price,
            'total_price' => (int) $property->total_price,
            'price_mode' => $priceMode,
            'registration_fee' => (int) $property->registration_fee,
            'brokerage_fee' => (int) $property->brokerage_fee,
            'property_tax' => (int) $property->property_tax,
        ];
    }

    /**
     * @param  array<string, mixed>  $formData
     * @param  list<string>  $addresses
     * @return array<string, mixed>
     */
    public function resolveAddressMode(array $formData, array $addresses): array
    {
        if (($formData['address_mode'] ?? '') !== '') {
            return $formData;
        }

        $formData['address_mode'] = empty($addresses) ? 'new' : 'existing';

        return $formData;
    }

    /**
     * @param  array<string, mixed>  $formData
     * @param  list<string>  $salesPersons
     * @return array<string, mixed>
     */
    public function resolveSalesPersonMode(array $formData, array $salesPersons): array
    {
        if (($formData['sales_person_mode'] ?? '') !== '') {
            return $formData;
        }

        $formData['sales_person_mode'] = empty($salesPersons) ? 'new' : 'existing';

        return $formData;
    }

    /**
     * @param  array<string, UploadedFile|null>  $requestFiles
     * @return array<string, UploadedFile|null>
     */
    public function propertyFormFiles(array $requestFiles): array
    {
        $files = [];

        foreach (DocumentFields::keys() as $field) {
            $files[$field] = $requestFiles[$field] ?? null;
        }

        return $files;
    }

    /** @return list<string> */
    public function getAddresses(): array
    {
        return PropertyAddress::query()
            ->orderBy('address')
            ->pluck('address')
            ->all();
    }

    public function addAddress(string $address): void
    {
        $address = trim($address);
        if ($address === '') {
            return;
        }

        PropertyAddress::query()->firstOrCreate(['address' => $address]);
    }

    /** @return list<string> */
    public function getSalesPersons(): array
    {
        return SalesPerson::query()
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    public function addSalesPerson(string $name): void
    {
        $name = trim($name);
        if ($name === '') {
            return;
        }

        SalesPerson::query()->firstOrCreate(['name' => $name]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, UploadedFile|null>  $files
     */
    public function save(array $data, array $files): int
    {
        $paths = $this->documentService->resolvePaths($files);
        $this->addAddress($data['property_address']);
        $this->addSalesPerson($data['sales_person'] ?? '');

        $property = Property::query()->create(array_merge($data, $paths));

        return (int) $property->id;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, UploadedFile|null>  $files
     */
    public function update(int $id, array $data, array $files): void
    {
        $property = Property::query()->find($id);

        if ($property === null) {
            throw new RuntimeException('物件が見つかりません。');
        }

        $existing = $property->only(DocumentFields::keys());
        $paths = $this->documentService->resolvePaths($files, $existing);
        $this->addAddress($data['property_address']);
        $this->addSalesPerson($data['sales_person'] ?? '');

        $property->update(array_merge($data, $paths));
    }

    /** @return Collection<int, Property> */
    public function getAll(): Collection
    {
        return Property::query()
            ->orderByDesc('created_at')
            ->get();
    }

    public function countDocuments(Property $property): int
    {
        $count = 0;

        foreach (DocumentFields::keys() as $field) {
            $path = $property->{$field};
            if ($path && $this->documentService->fileExists($path)) {
                $count++;
            }
        }

        return $count;
    }

    /** @return list<array<string, mixed>> */
    public function getListColumns(): array
    {
        return [
            ['key' => 'id', 'label' => 'ID', 'tdClass' => 'id-cell'],
            ['key' => 'created_at', 'label' => '作成日時', 'tdClass' => 'date-cell'],
            ['key' => 'sales_person', 'label' => '担当営業'],
            ['key' => 'buyer_name', 'label' => '購入者'],
            ['key' => 'broker_name', 'label' => '仲介業者名'],
            ['key' => 'owner_name', 'label' => 'オーナー名'],
            ['key' => 'property_address', 'label' => '物件住所', 'tdClass' => 'address-cell'],
            ['key' => 'building_price', 'label' => '建物取得価格', 'thClass' => 'num', 'tdClass' => 'num'],
            ['key' => 'land_price', 'label' => '土地取得価格', 'thClass' => 'num', 'tdClass' => 'num'],
            ['key' => 'total_price', 'label' => '物件価格(合算)', 'thClass' => 'num', 'tdClass' => 'num total-price'],
            ['key' => 'registration_fee', 'label' => '登記費用', 'thClass' => 'num', 'tdClass' => 'num'],
            ['key' => 'brokerage_fee', 'label' => '仲介手数料', 'thClass' => 'num', 'tdClass' => 'num'],
            ['key' => 'property_tax', 'label' => '固定資産税', 'thClass' => 'num', 'tdClass' => 'num'],
            ['key' => 'documents', 'label' => '書類', 'tdClass' => 'docs-cell'],
            ['key' => 'actions', 'label' => '操作', 'tdClass' => 'actions-cell', 'alwaysVisible' => true],
        ];
    }

    /** @return array<string, mixed> */
    public function defaultFormData(): array
    {
        $addresses = $this->getAddresses();

        return array_merge($this->emptyFormData(), [
            'address_mode' => empty($addresses) ? 'new' : '',
            'sales_person_mode' => '',
        ]);
    }

    /**
     * @param  array<string, mixed>  $formData
     * @return array<string, mixed>
     */
    public function prepareFormContext(array $formData): array
    {
        $addresses = $this->getAddresses();
        $salesPersons = $this->getSalesPersons();

        $formData = $this->resolveAddressMode($formData, $addresses);
        $formData = $this->resolveSalesPersonMode($formData, $salesPersons);

        return [
            'formData' => $formData,
            'addresses' => $addresses,
            'salesPersons' => $salesPersons,
            'hasAddresses' => count($addresses) > 0,
            'hasSalesPersons' => count($salesPersons) > 0,
        ];
    }

    /** @return array<string, mixed> */
    private function emptyFormData(): array
    {
        return [
            'created_at' => date('Y-m-d\TH:i'),
            'sales_person' => '',
            'sales_person_mode' => 'existing',
            'new_sales_person' => '',
            'buyer_name' => '',
            'broker_name' => '',
            'owner_name' => '',
            'property_address' => '',
            'address_mode' => 'existing',
            'prefecture' => '',
            'city' => '',
            'street' => '',
            'building_name' => '',
            'building_price' => 0,
            'land_price' => 0,
            'total_price' => 0,
            'price_mode' => 'split',
            'registration_fee' => 0,
            'brokerage_fee' => 0,
            'property_tax' => 0,
        ];
    }

    private function parseAmount(mixed $value): int
    {
        return (int) preg_replace('/[^0-9]/', '', (string) $value);
    }
}
