<?php

use Illuminate\Support\Facades\Route;
use ME\MerchandisingTrace\Http\Controllers\BuyerController;
use ME\MerchandisingTrace\Http\Controllers\ColorController;
use ME\MerchandisingTrace\Http\Controllers\CurrencyController;
use ME\MerchandisingTrace\Http\Controllers\DepartmentController;
use ME\MerchandisingTrace\Http\Controllers\FactoryController;
use ME\MerchandisingTrace\Http\Controllers\InquiryController;
use ME\MerchandisingTrace\Http\Controllers\ItemCategoryController;
use ME\MerchandisingTrace\Http\Controllers\ItemController;
use ME\MerchandisingTrace\Http\Controllers\ProductTypeController;
use ME\MerchandisingTrace\Http\Controllers\SeasonController;
use ME\MerchandisingTrace\Http\Controllers\ShipModeController;
use ME\MerchandisingTrace\Http\Controllers\SizeController;
use ME\MerchandisingTrace\Http\Controllers\StyleController;
use ME\MerchandisingTrace\Http\Controllers\SupplierController;
use ME\MerchandisingTrace\Http\Controllers\UomController;
use ME\MerchandisingTrace\Http\Controllers\WashTypeController;

$route = config('merchandising-trace.route');

Route::middleware($route['middleware'] ?? ['web', 'auth'])
    ->prefix($route['prefix'] ?? 'admin/merchandising-trace')
    ->name($route['as'] ?? 'merchandising-trace.')
    ->group(function () {
        // Masters (§4.1)
        Route::resource('buyers', BuyerController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('seasons', SeasonController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('product-types', ProductTypeController::class)
            ->only(['index', 'store', 'update', 'destroy'])->parameters(['product-types' => 'product_type']);
        Route::resource('colors', ColorController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('sizes', SizeController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('wash-types', WashTypeController::class)
            ->only(['index', 'store', 'update', 'destroy'])->parameters(['wash-types' => 'wash_type']);
        Route::resource('ship-modes', ShipModeController::class)
            ->only(['index', 'store', 'update', 'destroy'])->parameters(['ship-modes' => 'ship_mode']);
        Route::resource('factories', FactoryController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('suppliers', SupplierController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('currencies', CurrencyController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('item-categories', ItemCategoryController::class)
            ->only(['index', 'store', 'update', 'destroy'])->parameters(['item-categories' => 'item_category']);
        Route::resource('items', ItemController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('uoms', UomController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('departments', DepartmentController::class)->only(['index', 'store', 'update', 'destroy']);

        // Inquiry Management (§M02)
        Route::resource('inquiries', InquiryController::class);
        Route::post('inquiries/{inquiry}/convert-to-style', [InquiryController::class, 'convertToStyle'])->name('inquiries.convert-to-style');

        // Style Development (§M03)
        Route::resource('styles', StyleController::class)->only(['index', 'store', 'update', 'destroy']);
    });
