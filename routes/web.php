<?php

use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\FlowManagementController as AdminFlowManagementController;
use App\Http\Controllers\Admin\SettlementManagementController as AdminSettlementManagementController;
use App\Http\Controllers\ApplicationFormController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\Master\MasterDataController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PropertyRentalIncomeController;
use App\Http\Controllers\ReferenceController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/applications/create');

Route::get('/applications/create', [ApplicationFormController::class, 'create'])->name('applications.create');
Route::get('/applications/management-company-suggestions', [ApplicationFormController::class, 'managementCompanySuggestions'])->name('applications.management-company-suggestions');
Route::post('/applications', [ApplicationFormController::class, 'store'])->name('applications.store');
Route::get('/applications/complete/{application}', [ApplicationFormController::class, 'complete'])->name('applications.complete');

Route::redirect('/customers/create', '/applications/create');
Route::redirect('/customers/{customer}/applications/create', '/applications/create');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/applications', [AdminApplicationController::class, 'index'])->name('applications.index');
    Route::patch('/applications/{application}/flags', [AdminApplicationController::class, 'updateFlags'])->name('applications.update-flags');
    Route::patch('/applications/{application}/fields', [AdminApplicationController::class, 'updateField'])->name('applications.update-field');
    Route::get('/applications/{application}/customer', [AdminCustomerController::class, 'show'])->name('applications.customer.show');
    Route::patch('/applications/{application}/customer', [AdminCustomerController::class, 'update'])->name('applications.customer.update');

    Route::get('/flow-managements', [MasterDataController::class, 'index'])
        ->defaults('table', 'flow_managements')
        ->name('flow-managements.index');
    Route::get('/customers', [MasterDataController::class, 'index'])
        ->defaults('table', 'customers')
        ->name('customers.index');

    Route::patch('/flow-managements/{flowManagement}/fields', [AdminFlowManagementController::class, 'updateField'])->name('flow-managements.update-field');

    Route::get('/settlement-managements', [AdminSettlementManagementController::class, 'index'])->name('settlement-managements.index');
    Route::patch('/settlement-managements/{settlementManagement}/fields', [AdminSettlementManagementController::class, 'updateField'])->name('settlement-managements.update-field');
});

// ── 物件マスター（CareEarthHome 認証） ──
Route::redirect('login', '/')->name('login');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('careearth.auth')->group(function () {
    Route::get('/', [PropertyController::class, 'index'])->name('properties.index');
    Route::get('properties/create', [PropertyController::class, 'create'])->name('properties.create');
    Route::post('properties', [PropertyController::class, 'store'])->name('properties.store');
    Route::get('properties/{property}', [PropertyController::class, 'show'])->name('properties.show');
    Route::get('properties/{property}/edit', [PropertyController::class, 'edit'])->name('properties.edit');
    Route::put('properties/{property}', [PropertyController::class, 'update'])->name('properties.update');
    Route::get('reference', [ReferenceController::class, 'index'])->name('reference.index');
    Route::get('property/rental-income', [PropertyRentalIncomeController::class, 'index'])->name('property.rental-income.index');
    Route::get('files/{property}/{field}', [FileController::class, 'show'])->name('files.show');

    Route::middleware('careearth.admin')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
    });
});

Route::prefix('master')->name('master.')->group(function () {
    Route::redirect('/', '/master/data');
    Route::get('/data', [MasterDataController::class, 'index'])->name('data.index');
    Route::patch('/data/{table}/{record}/fields', [MasterDataController::class, 'updateField'])->name('data.update-field');
});
