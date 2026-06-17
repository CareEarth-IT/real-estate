<?php

use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;
use App\Http\Controllers\Admin\FlowManagementController as AdminFlowManagementController;
use App\Http\Controllers\Admin\ScreeningCompletionController as AdminScreeningCompletionController;
use App\Http\Controllers\Admin\SettlementManagementController as AdminSettlementManagementController;
use App\Http\Controllers\ApplicationFormController;
use App\Http\Controllers\CustomerFormController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/customers/create');

Route::get('/customers/create', [CustomerFormController::class, 'create'])->name('customers.create');
Route::post('/customers', [CustomerFormController::class, 'store'])->name('customers.store');

Route::get('/customers/{customer}/applications/create', [ApplicationFormController::class, 'create'])->name('applications.create');
Route::post('/customers/{customer}/applications', [ApplicationFormController::class, 'store'])->name('applications.store');
Route::get('/applications/complete/{application}', [ApplicationFormController::class, 'complete'])->name('applications.complete');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/applications', [AdminApplicationController::class, 'index'])->name('applications.index');
    Route::patch('/applications/{application}/flags', [AdminApplicationController::class, 'updateFlags'])->name('applications.update-flags');

    Route::get('/screening-completions', [AdminScreeningCompletionController::class, 'index'])->name('screening-completions.index');
    Route::patch('/screening-completions/{screeningCompletion}/flow-transition', [AdminScreeningCompletionController::class, 'updateFlowTransition'])->name('screening-completions.update-flow-transition');

    Route::get('/flow-managements', [AdminFlowManagementController::class, 'index'])->name('flow-managements.index');
    Route::patch('/flow-managements/{flowManagement}/fields', [AdminFlowManagementController::class, 'updateField'])->name('flow-managements.update-field');

    Route::get('/settlement-managements', [AdminSettlementManagementController::class, 'index'])->name('settlement-managements.index');
    Route::patch('/settlement-managements/{settlementManagement}/fields', [AdminSettlementManagementController::class, 'updateField'])->name('settlement-managements.update-field');
});
