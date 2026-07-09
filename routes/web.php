<?php

use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\FlowManagementController as AdminFlowManagementController;
use App\Http\Controllers\Admin\SettlementManagementController as AdminSettlementManagementController;
use App\Http\Controllers\ApplicationFormController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\Master\MasterDataController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PropertyDealDraftController;
use App\Http\Controllers\PropertyRentalIncomeController;
use App\Http\Controllers\ReferenceController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/applications/create', [ApplicationFormController::class, 'create'])->name('applications.create');
Route::get('/applications/management-company-suggestions', [ApplicationFormController::class, 'managementCompanySuggestions'])->name('applications.management-company-suggestions');
Route::post('/applications', [ApplicationFormController::class, 'store'])->name('applications.store');
Route::get('/applications/complete/{application}', [ApplicationFormController::class, 'complete'])->name('applications.complete');

Route::redirect('/customers/create', '/applications/create');
Route::redirect('/customers/{customer}/applications/create', '/applications/create');

Route::prefix('admin')->name('admin.')->middleware('careearth.auth')->group(function () {
    Route::get('/applications', [AdminApplicationController::class, 'index'])->name('applications.index');
    Route::get('/flow-managements', [AdminFlowManagementController::class, 'index'])->name('flow-managements.index');
    Route::get('/settlement-managements', [AdminSettlementManagementController::class, 'index'])->name('settlement-managements.index');
    Route::get('/customers', [MasterDataController::class, 'index'])
        ->defaults('table', 'customers')
        ->name('customers.index');

    Route::middleware('careearth.edit')->group(function () {
        Route::get('/applications/create', [AdminApplicationController::class, 'create'])->name('applications.create');
        Route::post('/applications', [AdminApplicationController::class, 'store'])->name('applications.store');
        Route::patch('/applications/{application}/flags', [AdminApplicationController::class, 'updateFlags'])->name('applications.update-flags');
        Route::patch('/applications/{application}/fields', [AdminApplicationController::class, 'updateField'])->name('applications.update-field');
        Route::get('/applications/{application}/customer', [AdminCustomerController::class, 'show'])->name('applications.customer.show');
        Route::patch('/applications/{application}/customer', [AdminCustomerController::class, 'update'])->name('applications.customer.update');
        Route::patch('/flow-managements/{flowManagement}/fields', [AdminFlowManagementController::class, 'updateField'])->name('flow-managements.update-field');
        Route::patch('/settlement-managements/{settlementManagement}/fields', [AdminSettlementManagementController::class, 'updateField'])->name('settlement-managements.update-field');
    });
});

Route::prefix('master')->name('master.')->middleware('careearth.auth')->group(function () {
    Route::redirect('/', '/master/data');
    Route::get('/data', [MasterDataController::class, 'index'])->name('data.index');
    Route::middleware('careearth.edit')->group(function () {
        Route::patch('/data/{table}/{record}/fields', [MasterDataController::class, 'updateField'])->name('data.update-field');
    });
});

Route::redirect('login', '/')->name('login');

Route::middleware('careearth.auth')->group(function () {
    Route::middleware('careearth.admin')->group(function () {
        Route::get('/', [PropertyController::class, 'index'])->name('properties.index');
        Route::get('properties/create', [PropertyController::class, 'create'])->name('properties.create');
        Route::post('properties', [PropertyController::class, 'store'])->name('properties.store');
        Route::get('properties/{property}/edit', [PropertyController::class, 'edit'])->name('properties.edit');
        Route::put('properties/{property}', [PropertyController::class, 'update'])->name('properties.update');

        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
    });

    Route::get('properties/{property}', [PropertyController::class, 'show'])->name('properties.show');
    Route::get('reference', [ReferenceController::class, 'index'])->name('reference.index');
    Route::get('property/deal-drafts', [PropertyDealDraftController::class, 'index'])->name('property.deal-drafts.index');
    Route::get('property/rental-income', [PropertyRentalIncomeController::class, 'index'])->name('property.rental-income.index');
    Route::get('property/rental-income/all', [PropertyRentalIncomeController::class, 'all'])->name('property.rental-income.all');
    Route::get('property/rental-income/contract', [PropertyRentalIncomeController::class, 'showContract'])->name('property.rental-income.contract.show');
    Route::get('files/{property}/{field}', [FileController::class, 'show'])->name('files.show');

    Route::middleware('careearth.edit')->group(function () {
        Route::get('property/deal-drafts/create', [PropertyDealDraftController::class, 'create'])->name('property.deal-drafts.create');
        Route::post('property/deal-drafts', [PropertyDealDraftController::class, 'store'])->name('property.deal-drafts.store');
        Route::get('property/deal-drafts/{propertyDealDraft}/edit', [PropertyDealDraftController::class, 'edit'])->name('property.deal-drafts.edit');
        Route::put('property/deal-drafts/{propertyDealDraft}', [PropertyDealDraftController::class, 'update'])->name('property.deal-drafts.update');
        Route::patch('property/deal-drafts/{propertyDealDraft}/fields', [PropertyDealDraftController::class, 'updateField'])->name('property.deal-drafts.update-field');
        Route::post('property/deal-drafts/{propertyDealDraft}/ad-fees', [PropertyDealDraftController::class, 'storeAdFee'])->name('property.deal-drafts.ad-fees.store');
        Route::patch('property/deal-drafts/{propertyDealDraft}/ad-fees/{adFee}', [PropertyDealDraftController::class, 'updateAdFee'])->name('property.deal-drafts.ad-fees.update');
        Route::delete('property/deal-drafts/{propertyDealDraft}/ad-fees/{adFee}', [PropertyDealDraftController::class, 'destroyAdFee'])->name('property.deal-drafts.ad-fees.destroy');
        Route::get('property/rental-income/create', [PropertyRentalIncomeController::class, 'create'])->name('property.rental-income.create');
        Route::post('property/rental-income', [PropertyRentalIncomeController::class, 'store'])->name('property.rental-income.store');
        Route::get('property/rental-income/{propertyRentalIncome}/edit', [PropertyRentalIncomeController::class, 'edit'])->name('property.rental-income.edit');
        Route::put('property/rental-income/{propertyRentalIncome}', [PropertyRentalIncomeController::class, 'update'])->name('property.rental-income.update');
        Route::patch('property/rental-income/{propertyRentalIncome}/fields', [PropertyRentalIncomeController::class, 'updateField'])->name('property.rental-income.update-field');
        Route::post('property/rental-income/{propertyRentalIncome}/copy-to-next-month', [PropertyRentalIncomeController::class, 'copyToNextMonth'])->name('property.rental-income.copy-to-next-month');
        Route::delete('property/rental-income/{propertyRentalIncome}', [PropertyRentalIncomeController::class, 'destroy'])->name('property.rental-income.destroy');
    });
});
