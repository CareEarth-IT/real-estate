@extends('layouts.rental')

@section('title', '申込情報入力 - ' . config('app.name'))

@section('content')
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-900">申込情報入力</h2>
        <p class="mt-2 text-sm text-slate-600"><span class="text-red-500">*</span> の付いた項目は必須です。</p>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-red-800 text-sm">
            <p class="font-medium">入力内容に誤りがあります。各項目をご確認ください。</p>
        </div>
    @endif

    <form method="POST" action="{{ route('applications.store', $customer) }}" class="space-y-8" novalidate>
        @csrf

        <section class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-5">
            <h3 class="text-base font-semibold text-slate-900 border-b border-slate-100 pb-3">物件・申込情報</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <x-form-field label="担当者" name="staff_in_charge" required class="md:col-span-2" />
                <x-form-field
                    label="物件名＋部屋番号"
                    name="property_name_room"
                    :value="$defaults['property_name_room']"
                    class="md:col-span-2"
                    required
                />
                <x-form-field
                    label="入居予定日"
                    name="scheduled_move_in_date"
                    type="date"
                    :value="$defaults['scheduled_move_in_date']"
                    required
                />
                <x-form-field label="広告料" name="advertising_fee" type="number" min="0" required />
                <div class="md:col-span-2 space-y-3">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input
                            type="checkbox"
                            name="has_broker_fee"
                            value="1"
                            class="rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                            @checked(old('has_broker_fee'))
                        >
                        仲介手数料あり
                    </label>
                    <x-form-field label="仲介手数料（金額）" name="broker_fee" type="number" min="0" />
                </div>
                <x-form-field
                    label="管理会社名"
                    name="management_company_name"
                    :value="$defaults['management_company_name']"
                    class="md:col-span-2"
                    required
                />
                <x-form-field label="申込方法" name="application_method" required />
                <x-form-field label="状況" name="status" type="textarea" rows="5" class="md:col-span-2" required />
            </div>
        </section>

        <section class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-5">
            <h3 class="text-base font-semibold text-slate-900 border-b border-slate-100 pb-3">その他</h3>
            <div class="grid grid-cols-1 gap-5">
                <x-form-field label="MEMO" name="memo" type="textarea" />
                <x-form-field label="物件資料" name="property_documents_url" type="url" placeholder="https://" />
                <x-form-field label="家電サポート・CB等" name="appliance_support_notes" type="textarea" />
            </div>
        </section>

        <div class="flex justify-end">
            <button
                type="submit"
                class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-6 py-3 text-sm font-semibold text-white hover:bg-primary-700 transition-colors"
            >
                送信する
            </button>
        </div>
    </form>
@endsection
