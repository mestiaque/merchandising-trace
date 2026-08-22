<?php

use Illuminate\Support\Facades\Route;
use ME\MerchandisingTrace\Http\Controllers\BomController;
use ME\MerchandisingTrace\Http\Controllers\BuyerController;
use ME\MerchandisingTrace\Http\Controllers\ColorController;
use ME\MerchandisingTrace\Http\Controllers\CostSheetController;
use ME\MerchandisingTrace\Http\Controllers\CurrencyController;
use ME\MerchandisingTrace\Http\Controllers\DepartmentController;
use ME\MerchandisingTrace\Http\Controllers\FactoryController;
use ME\MerchandisingTrace\Http\Controllers\InquiryController;
use ME\MerchandisingTrace\Http\Controllers\ItemCategoryController;
use ME\MerchandisingTrace\Http\Controllers\ItemController;
use ME\MerchandisingTrace\Http\Controllers\ProductTypeController;
use ME\MerchandisingTrace\Http\Controllers\SalesContractController;
use ME\MerchandisingTrace\Http\Controllers\SalesContractPoController;
use ME\MerchandisingTrace\Http\Controllers\SampleController;
use ME\MerchandisingTrace\Http\Controllers\SampleTypeController;
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

        // Sample Management (§M04)
        Route::resource('samples', SampleController::class);
        Route::post('samples/{sample}/submit', [SampleController::class, 'submit'])->name('samples.submit');
        Route::post('samples/{sample}/approve', [SampleController::class, 'approve'])->name('samples.approve');
        Route::post('samples/{sample}/reject', [SampleController::class, 'reject'])->name('samples.reject');
        Route::post('samples/{sample}/comments', [SampleController::class, 'addComment'])->name('samples.comments.store');
        Route::resource('sample-types', SampleTypeController::class)
            ->only(['index', 'store', 'update', 'destroy'])->parameters(['sample-types' => 'sample_type']);

        // BOM & Consumption (§M05)
        Route::resource('boms', BomController::class);
        Route::post('boms/{bom}/approve', [BomController::class, 'approve'])->name('boms.approve');

        // Costing (§M06)
        Route::resource('cost-sheets', CostSheetController::class)->parameters(['cost-sheets' => 'cost_sheet']);
        Route::post('cost-sheets/{cost_sheet}/approve', [CostSheetController::class, 'approve'])->name('cost-sheets.approve');

        // Sales Contract / Order Confirmation (§M07)
        Route::resource('sales-contracts', SalesContractController::class)->parameters(['sales-contracts' => 'sales_contract']);
        Route::post('sales-contracts/{sales_contract}/confirm', [SalesContractController::class, 'confirm'])->name('sales-contracts.confirm');
        Route::get('sales-contracts/{sales_contract}/pos/create', [SalesContractPoController::class, 'create'])->name('sales-contracts.pos.create');
        Route::post('sales-contracts/{sales_contract}/pos', [SalesContractPoController::class, 'store'])->name('sales-contracts.pos.store');
        Route::get('sales-contracts/{sales_contract}/pos/{sales_contract_po}/edit', [SalesContractPoController::class, 'edit'])->name('sales-contracts.pos.edit');
        Route::put('sales-contracts/{sales_contract}/pos/{sales_contract_po}', [SalesContractPoController::class, 'update'])->name('sales-contracts.pos.update');
        Route::delete('sales-contracts/{sales_contract}/pos/{sales_contract_po}', [SalesContractPoController::class, 'destroy'])->name('sales-contracts.pos.destroy');
        Route::post('sales-contracts/{sales_contract}/pos/{sales_contract_po}/revise', [SalesContractPoController::class, 'revise'])->name('sales-contracts.pos.revise');
    });
