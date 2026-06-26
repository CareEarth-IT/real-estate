<?php

use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\FlowManagementController as AdminFlowManagementController;
use App\Http\Controllers\Admin\ScreeningCompletionController as AdminScreeningCompletionController;
use App\Http\Controllers\Admin\SettlementManagementController as AdminSettlementManagementController;
use App\Http\Controllers\ApplicationFormController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerFormController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\ReferenceController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ── 賃貸申込（公開フォーム） ──
Route::get('/rental/customers/create', [CustomerFormController::class, 'create'])->name('customers.create');
Route::post('/rental/customers', [CustomerFormController::class, 'store'])->name('customers.store');
Route::get('/rental/customers/{customer}/applications/create', [ApplicationFormController::class, 'create'])->name('applications.create');
Route::post('/rental/customers/{customer}/applications', [ApplicationFormController::class, 'store'])->name('applications.store');
Route::get('/rental/applications/complete/{application}', [ApplicationFormController::class, 'complete'])->name('applications.complete');

// ── 賃貸管理（Google OAuth） ──
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::get('/auth/google', [AdminAuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [AdminAuthController::class, 'handleGoogleCallback']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware('admin.auth')->group(function () {
        Route::get('/applications', [AdminApplicationController::class, 'index'])->name('applications.index');
        Route::patch('/applications/{application}/flags', [AdminApplicationController::class, 'updateFlags'])->name('applications.update-flags');

        Route::get('/screening-completions', [AdminScreeningCompletionController::class, 'index'])->name('screening-completions.index');
        Route::patch('/screening-completions/{screeningCompletion}/flow-transition', [AdminScreeningCompletionController::class, 'updateFlowTransition'])->name('screening-completions.update-flow-transition');

        Route::get('/flow-managements', [AdminFlowManagementController::class, 'index'])->name('flow-managements.index');
        Route::patch('/flow-managements/{flowManagement}/fields', [AdminFlowManagementController::class, 'updateField'])->name('flow-managements.update-field');

        Route::get('/settlement-managements', [AdminSettlementManagementController::class, 'index'])->name('settlement-managements.index');
        Route::patch('/settlement-managements/{settlementManagement}/fields', [AdminSettlementManagementController::class, 'updateField'])->name('settlement-managements.update-field');
    });
});

// ── 物件マスター（CareEarthHome 認証） ──
Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('careearth.auth')->group(function () {
    Route::get('/', [PropertyController::class, 'index'])->name('properties.index');
    Route::get('properties/create', [PropertyController::class, 'create'])->name('properties.create');
    Route::post('properties', [PropertyController::class, 'store'])->name('properties.store');
    Route::get('properties/{property}', [PropertyController::class, 'show'])->name('properties.show');
    Route::get('properties/{property}/edit', [PropertyController::class, 'edit'])->name('properties.edit');
    Route::put('properties/{property}', [PropertyController::class, 'update'])->name('properties.update');
    Route::get('reference', [ReferenceController::class, 'index'])->name('reference.index');
    Route::get('files/{property}/{field}', [FileController::class, 'show'])->name('files.show');

    Route::middleware('careearth.admin')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
    });
});
