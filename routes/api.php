<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\IntegrationController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\CashRegisterController;
use App\Http\Controllers\Api\CashRegisterShiftController;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OnboardingController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\PaymentMethodController;

// Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Authenticated Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Onboarding / Company Setup
    Route::post('/onboarding/company', [OnboardingController::class, 'createCompany']);
    Route::post('/onboarding/skip', [OnboardingController::class, 'skipWait']);
    Route::get('/user/companies', [OnboardingController::class, 'listCompanies']);

    // Switch Company
    Route::post('/switch-company', [OnboardingController::class, 'switchCompany']);

    // Tenant Context Routes (Requires configured company)
    Route::middleware(['tenant', 'company.configured'])->group(function () {
        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('branches', BranchController::class);
        Route::apiResource('payment-methods', PaymentMethodController::class);

        Route::get('/products/export', [ProductController::class, 'export']);
        Route::get('/products/template', [ProductController::class, 'template']);
        Route::post('/products/import', [ProductController::class, 'import']);
        Route::apiResource('products', ProductController::class);

        Route::post('/sales', [SaleController::class, 'store']);
        Route::get('/sales/report', [ReportController::class, 'daily']);

        // Cash Registers (CRUD)
        Route::get('/cash-registers', [CashRegisterController::class, 'index']);
        Route::post('/cash-registers', [CashRegisterController::class, 'store']);
        Route::put('/cash-registers/{cashRegister}', [CashRegisterController::class, 'update']);
        Route::delete('/cash-registers/{cashRegister}', [CashRegisterController::class, 'destroy']);

        // Cash Register Shifts
        Route::get('/shifts', [CashRegisterShiftController::class, 'index']);
        Route::get('/shifts/my-shift', [CashRegisterShiftController::class, 'myShift']);
        Route::post('/shifts/open', [CashRegisterShiftController::class, 'open']);
        Route::post('/shifts/{shift}/close', [CashRegisterShiftController::class, 'close']);

        Route::get('/integrations', [IntegrationController::class, 'index']);
        Route::put('/integrations/{id}', [IntegrationController::class, 'update']);

        // Team Management
        Route::get('/team/users', [\App\Http\Controllers\Api\TeamController::class, 'indexUsers']);
        Route::post('/team/users', [\App\Http\Controllers\Api\TeamController::class, 'storeUser']);
        Route::put('/team/users/{user}', [\App\Http\Controllers\Api\TeamController::class, 'updateUser']);

        Route::get('/team/roles', [\App\Http\Controllers\Api\TeamController::class, 'indexRoles']);
        Route::post('/team/roles', [\App\Http\Controllers\Api\TeamController::class, 'storeRole']);
        Route::put('/team/roles/{role}', [\App\Http\Controllers\Api\TeamController::class, 'updateRole']);

        Route::get('/team/permissions', [\App\Http\Controllers\Api\TeamController::class, 'indexPermissions']);
    });
});

