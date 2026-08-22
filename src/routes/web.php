<?php

use Illuminate\Support\Facades\Route;
use ME\MerchandisingTrace\Http\Controllers\BomController;
use ME\MerchandisingTrace\Http\Controllers\BrandController;
use ME\MerchandisingTrace\Http\Controllers\BuyerController;
use ME\MerchandisingTrace\Http\Controllers\ColorController;
use ME\MerchandisingTrace\Http\Controllers\CostingController;
use ME\MerchandisingTrace\Http\Controllers\CurrencyController;
use ME\MerchandisingTrace\Http\Controllers\DashboardController;
use ME\MerchandisingTrace\Http\Controllers\DepartmentController;
use ME\MerchandisingTrace\Http\Controllers\DocumentController;
use ME\MerchandisingTrace\Http\Controllers\FactoryController;
use ME\MerchandisingTrace\Http\Controllers\IncotermController;
use ME\MerchandisingTrace\Http\Controllers\InquiryController;
use ME\MerchandisingTrace\Http\Controllers\ItemCategoryController;
use ME\MerchandisingTrace\Http\Controllers\ItemController;
use ME\MerchandisingTrace\Http\Controllers\MaterialBookingController;
use ME\MerchandisingTrace\Http\Controllers\PaymentTermController;
use ME\MerchandisingTrace\Http\Controllers\OrderController;
use ME\MerchandisingTrace\Http\Controllers\PortController;
use ME\MerchandisingTrace\Http\Controllers\ProductionCoordinationController;
use ME\MerchandisingTrace\Http\Controllers\ProductTypeController;
use ME\MerchandisingTrace\Http\Controllers\ReportController;
use ME\MerchandisingTrace\Http\Controllers\SalesContractController;
use ME\MerchandisingTrace\Http\Controllers\ShipModeController;
use ME\MerchandisingTrace\Http\Controllers\ShipmentPlanController;
use ME\MerchandisingTrace\Http\Controllers\SampleController;
use ME\MerchandisingTrace\Http\Controllers\SampleTypeController;
use ME\MerchandisingTrace\Http\Controllers\SeasonController;
use ME\MerchandisingTrace\Http\Controllers\SizeController;
use ME\MerchandisingTrace\Http\Controllers\StyleController;
use ME\MerchandisingTrace\Http\Controllers\SupplierController;
use ME\MerchandisingTrace\Http\Controllers\TnaMilestoneController;
use ME\MerchandisingTrace\Http\Controllers\UomController;
use ME\MerchandisingTrace\Http\Controllers\WashTypeController;

$route = config('merchandising-trace.route');

Route::middleware($route['middleware'] ?? ['web', 'auth'])
    ->prefix($route['prefix'] ?? 'admin/merchandising')
    ->name($route['as'] ?? 'merchandising-trace.')
    ->group(function () {
        // Dashboard / Reports
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/order-summary', [ReportController::class, 'orderSummary'])->name('reports.order-summary');
        Route::get('reports/costing-report', [ReportController::class, 'costingReport'])->name('reports.costing-report');
        Route::get('reports/sample-status', [ReportController::class, 'sampleStatus'])->name('reports.sample-status');
        Route::get('reports/tna-report', [ReportController::class, 'tnaReport'])->name('reports.tna-report');
        Route::get('reports/shipment-report', [ReportController::class, 'shipmentReport'])->name('reports.shipment-report');
        Route::get('reports/buyer-wise', [ReportController::class, 'buyerWise'])->name('reports.buyer-wise');
        Route::get('reports/style-wise', [ReportController::class, 'styleWise'])->name('reports.style-wise');

        // Master Setup (Section 1)
        Route::resource('buyers', BuyerController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('buyers/print', [BuyerController::class, 'print'])->name('buyers.print');
        Route::resource('brands', BrandController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('brands/print', [BrandController::class, 'print'])->name('brands.print');
        Route::resource('suppliers', SupplierController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('suppliers/print', [SupplierController::class, 'print'])->name('suppliers.print');
        Route::resource('seasons', SeasonController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('seasons/print', [SeasonController::class, 'print'])->name('seasons.print');
        Route::resource('currencies', CurrencyController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('currencies/print', [CurrencyController::class, 'print'])->name('currencies.print');
        Route::resource('payment-terms', PaymentTermController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['payment-terms' => 'payment_term']);
        Route::get('payment-terms/print', [PaymentTermController::class, 'print'])->name('payment-terms.print');
        Route::resource('incoterms', IncotermController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('incoterms/print', [IncotermController::class, 'print'])->name('incoterms.print');
        Route::resource('ports', PortController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('ports/print', [PortController::class, 'print'])->name('ports.print');
        Route::resource('uoms', UomController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('uoms/print', [UomController::class, 'print'])->name('uoms.print');
        Route::resource('product-types', ProductTypeController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['product-types' => 'product_type']);
        Route::get('product-types/print', [ProductTypeController::class, 'print'])->name('product-types.print');
        Route::resource('wash-types', WashTypeController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['wash-types' => 'wash_type']);
        Route::get('wash-types/print', [WashTypeController::class, 'print'])->name('wash-types.print');
        Route::resource('ship-modes', ShipModeController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['ship-modes' => 'ship_mode']);
        Route::get('ship-modes/print', [ShipModeController::class, 'print'])->name('ship-modes.print');
        Route::resource('factories', FactoryController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('factories/print', [FactoryController::class, 'print'])->name('factories.print');
        Route::resource('departments', DepartmentController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('departments/print', [DepartmentController::class, 'print'])->name('departments.print');
        Route::resource('item-categories', ItemCategoryController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['item-categories' => 'item_category']);
        Route::get('item-categories/print', [ItemCategoryController::class, 'print'])->name('item-categories.print');
        Route::resource('items', ItemController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('items/print', [ItemController::class, 'print'])->name('items.print');

        // Inquiry Management (Section M02)
        Route::resource('inquiries', InquiryController::class);
        Route::post('inquiries/{inquiry}/convert-to-style', [InquiryController::class, 'convertToStyle'])->name('inquiries.convert-to-style');

        // Style Development (Section 2)
        Route::resource('styles', StyleController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('styles/print', [StyleController::class, 'print'])->name('styles.print');
        Route::resource('colors', ColorController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('colors/print', [ColorController::class, 'print'])->name('colors.print');
        Route::resource('sizes', SizeController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('sizes/print', [SizeController::class, 'print'])->name('sizes.print');

        // Sample Management (Section 3)
        Route::get('samples/print-list', [SampleController::class, 'printList'])->name('samples.print-list');
        Route::resource('samples', SampleController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
        Route::get('samples/{sample}/print', [SampleController::class, 'print'])->name('samples.print');
        Route::post('samples/{sample}/submit', [SampleController::class, 'submit'])->name('samples.submit');
        Route::post('samples/{sample}/approve', [SampleController::class, 'approve'])->name('samples.approve');
        Route::post('samples/{sample}/reject', [SampleController::class, 'reject'])->name('samples.reject');
        Route::post('samples/{sample}/resubmit', [SampleController::class, 'resubmit'])->name('samples.resubmit');
        Route::post('samples/{sample}/comments', [SampleController::class, 'addComment'])->name('samples.comments.store');
        Route::resource('sample-types', SampleTypeController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['sample-types' => 'sample_type']);

        // BOM (Section 4)
        Route::get('boms/print-list', [BomController::class, 'printList'])->name('boms.print-list');
        Route::resource('boms', BomController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
        Route::get('boms/{bom}/print', [BomController::class, 'print'])->name('boms.print');
        Route::post('boms/{bom}/new-version', [BomController::class, 'newVersion'])->name('boms.new-version');

        // Order Management (Section 7)
        Route::get('orders/print-list', [OrderController::class, 'printList'])->name('orders.print-list');
        Route::resource('orders', OrderController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
        Route::get('orders/{order}/print', [OrderController::class, 'print'])->name('orders.print');
        Route::resource('sales-contracts', SalesContractController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['sales-contracts' => 'sales_contract']);
        Route::get('sales-contracts/print', [SalesContractController::class, 'print'])->name('sales-contracts.print');

        // Costing (Section 6)
        Route::resource('costings', CostingController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('costings/print', [CostingController::class, 'print'])->name('costings.print');

        // TNA (Section 8)
        Route::resource('tna-milestones', TnaMilestoneController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['tna-milestones' => 'tna_milestone']);
        Route::get('tna-milestones/print', [TnaMilestoneController::class, 'print'])->name('tna-milestones.print');

        // Material Booking (Section 9)
        Route::resource('material-bookings', MaterialBookingController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['material-bookings' => 'material_booking']);
        Route::get('material-bookings/print', [MaterialBookingController::class, 'print'])->name('material-bookings.print');

        // Production Coordination (Section 10) — read-only
        Route::get('production-coordination', [ProductionCoordinationController::class, 'index'])->name('production-coordination.index');

        // Shipment Management (Section 11)
        Route::resource('shipment-plans', ShipmentPlanController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['shipment-plans' => 'shipment_plan']);
        Route::get('shipment-plans/print', [ShipmentPlanController::class, 'print'])->name('shipment-plans.print');

        // Document Management (Section 12)
        Route::resource('documents', DocumentController::class)->only(['index', 'store', 'update', 'destroy']);
    });
