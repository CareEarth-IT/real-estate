@extends('layouts.app')

@section('title', '申込フォーム - ' . config('app.name'))

@section('content')
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-900">入居者・申込情報入力</h2>
        <p class="mt-2 text-sm text-slate-600">すべての項目が必須です。<span class="text-red-500">*</span> の付いた項目をご入力のうえ、送信してください。</p>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-red-800 text-sm">
            <p class="font-medium">入力内容に誤りがあります。各項目をご確認ください。</p>
        </div>
    @endif

    <form method="POST" action="{{ route('customers.store') }}" class="space-y-8" novalidate>
        @csrf

        <section class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-5">
            <h3 class="text-base font-semibold text-slate-900 border-b border-slate-100 pb-3">物件・契約情報</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <x-form-field label="物件名" name="property_name" required />
                <x-form-field label="部屋番号" name="room_number" required />
                <x-form-field label="住所" name="address" class="md:col-span-2" required />
                <x-form-field label="管理会社" name="management_company" required />
                <x-form-field label="入居日/保険加入日" name="move_in_date" type="date" required />
                <x-form-field label="契約期間" name="contract_period" type="date" required />
                <x-form-field
                    label="種類（契約期間）"
                    name="contract_period_type"
                    type="select"
                    :options="['' => '選択してください', '0' => '普通', '1' => '定期']"
                    required
                />
            </div>
        </section>

        <section class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-5">
            <h3 class="text-base font-semibold text-slate-900 border-b border-slate-100 pb-3">申込者情報</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <x-form-field label="氏名" name="name" class="md:col-span-2" required />
                <x-form-field label="生年月日" name="date_of_birth" type="date" required />
                <x-form-field
                    label="既婚/未婚"
                    name="is_married"
                    type="select"
                    :options="['' => '選択してください', '1' => '既婚', '0' => '未婚']"
                    required
                />
                <x-form-field label="携帯番号" name="mobile_number" type="tel" required />
                <x-form-field label="メールアドレス" name="email" type="email" required />
                <x-form-field label="職業" name="occupation" required />
            </div>
        </section>

        <section class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-5">
            <h3 class="text-base font-semibold text-slate-900 border-b border-slate-100 pb-3">会社・学校情報</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <x-form-field label="会社名/学校名" name="company_or_school_name" class="md:col-span-2" required />
                <x-form-field label="電話番号（会社名/学校名）" name="company_or_school_phone" type="tel" required />
                <x-form-field label="住所（会社/学校）" name="company_or_school_address" type="textarea" class="md:col-span-2" required />
            </div>
        </section>

        <section class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-5">
            <h3 class="text-base font-semibold text-slate-900 border-b border-slate-100 pb-3">緊急連絡先</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <x-form-field label="緊急連絡先の氏名" name="emergency_contact_name" required />
                <x-form-field label="続柄" name="emergency_contact_relationship" required />
                <x-form-field label="生年月日（緊急連絡先）" name="emergency_contact_date_of_birth" type="date" required />
                <x-form-field label="携帯番号（緊急連絡先）" name="emergency_contact_mobile" type="tel" required />
                <x-form-field label="メールアドレス（緊急連絡先）" name="emergency_contact_email" type="email" required />
                <x-form-field label="現住所（緊急連絡先）" name="emergency_contact_address" type="textarea" class="md:col-span-2" required />
            </div>
        </section>

        <div class="flex justify-end">
            <button
                type="submit"
                class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-6 py-3 text-sm font-semibold text-white hover:bg-primary-700 transition-colors"
            >
                次へ
            </button>
        </div>
    </form>
@endsection
