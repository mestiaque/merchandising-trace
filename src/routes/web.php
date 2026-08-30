<?php

use Illuminate\Support\Facades\Route;
use ME\MerchandisingTrace\Http\Controllers\BomController;
use ME\MerchandisingTrace\Http\Controllers\BuyerController;
use ME\MerchandisingTrace\Http\Controllers\ColorController;
use ME\MerchandisingTrace\Http\Controllers\CommunicationLogController;
use ME\MerchandisingTrace\Http\Controllers\CostSheetController;
use ME\MerchandisingTrace\Http\Controllers\CurrencyController;
use ME\MerchandisingTrace\Http\Controllers\DashboardController;
use ME\MerchandisingTrace\Http\Controllers\DepartmentController;
use ME\MerchandisingTrace\Http\Controllers\FactoryController;
use ME\MerchandisingTrace\Http\Controllers\InquiryController;
use ME\MerchandisingTrace\Http\Controllers\ItemCategoryController;
use ME\MerchandisingTrace\Http\Controllers\ItemController;
use ME\MerchandisingTrace\Http\Controllers\MasterExcelController;
use ME\MerchandisingTrace\Http\Controllers\MaterialBookingController;
use ME\MerchandisingTrace\Http\Controllers\OrderDocumentController;
use ME\MerchandisingTrace\Http\Controllers\ProductionHandoverController;
use ME\MerchandisingTrace\Http\Controllers\ProductTypeController;
use ME\MerchandisingTrace\Http\Controllers\ReportController;
use ME\MerchandisingTrace\Http\Controllers\RiskAssessmentController;
use ME\MerchandisingTrace\Http\Controllers\SalesContractController;
use ME\MerchandisingTrace\Http\Controllers\SalesContractPoController;
use ME\MerchandisingTrace\Http\Controllers\SampleController;
use ME\MerchandisingTrace\Http\Controllers\SampleTypeController;
use ME\MerchandisingTrace\Http\Controllers\SeasonController;
use ME\MerchandisingTrace\Http\Controllers\ShipmentPlanController;
use ME\MerchandisingTrace\Http\Controllers\ShipModeController;
use ME\MerchandisingTrace\Http\Controllers\SizeController;
use ME\MerchandisingTrace\Http\Controllers\StyleController;
use ME\MerchandisingTrace\Http\Controllers\StyleImageController;
use ME\MerchandisingTrace\Http\Controllers\StyleImageTypeController;
use ME\MerchandisingTrace\Http\Controllers\StyleMeasurementController;
use ME\MerchandisingTrace\Http\Controllers\StyleOperationController;
use ME\MerchandisingTrace\Http\Controllers\StylePartController;
use ME\MerchandisingTrace\Http\Controllers\SupplierController;
use ME\MerchandisingTrace\Http\Controllers\TnaAlertController;
use ME\MerchandisingTrace\Http\Controllers\TnaPlanController;
use ME\MerchandisingTrace\Http\Controllers\TnaSubPlanController;
use ME\MerchandisingTrace\Http\Controllers\TnaTemplateController;
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
        Route::resource('style-image-types', StyleImageTypeController::class)
            ->only(['index', 'store', 'update', 'destroy'])->parameters(['style-image-types' => 'style_image_type']);

        // §M01 AC: Excel import/export for every simple master above.
        Route::get('masters/{master}/export', [MasterExcelController::class, 'export'])
            ->whereIn('master', array_keys(config('merchandising-trace-master-excel', [])))
            ->name('masters.export');
        Route::post('masters/{master}/import', [MasterExcelController::class, 'import'])
            ->whereIn('master', array_keys(config('merchandising-trace-master-excel', [])))
            ->name('masters.import');

        // Inquiry Management (§M02)
        Route::resource('inquiries', InquiryController::class);
        Route::post('inquiries/{inquiry}/convert-to-style', [InquiryController::class, 'convertToStyle'])->name('inquiries.convert-to-style');

        // Style Development (§M03)
        Route::resource('styles', StyleController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
        Route::resource('risk-assessments', RiskAssessmentController::class)
            ->only(['index', 'store', 'update', 'destroy'])->parameters(['risk-assessments' => 'risk_assessment']);
        Route::post('styles/{style}/images', [StyleImageController::class, 'store'])->name('styles.images.store');
        Route::delete('styles/{style}/images/{image}', [StyleImageController::class, 'destroy'])->name('styles.images.destroy');
        Route::post('styles/{style}/measurements', [StyleMeasurementController::class, 'store'])->name('styles.measurements.store');
        Route::delete('styles/{style}/measurements/{measurement}', [StyleMeasurementController::class, 'destroy'])->name('styles.measurements.destroy');
        Route::get('styles/{style}/measurements/export', [StyleMeasurementController::class, 'exportExcel'])->name('styles.measurements.export');
        Route::post('styles/{style}/measurements/import', [StyleMeasurementController::class, 'importExcel'])->name('styles.measurements.import');
        Route::post('styles/{style}/parts', [StylePartController::class, 'store'])->name('styles.parts.store');
        Route::delete('styles/{style}/parts/{part}', [StylePartController::class, 'destroy'])->name('styles.parts.destroy');
        Route::post('styles/{style}/operations', [StyleOperationController::class, 'store'])->name('styles.operations.store');
        Route::delete('styles/{style}/operations/{operation}', [StyleOperationController::class, 'destroy'])->name('styles.operations.destroy');

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
        Route::get('boms/{bom}/export', [BomController::class, 'exportExcel'])->name('boms.export');
        Route::post('boms/{bom}/import', [BomController::class, 'importExcel'])->name('boms.import');

        // Costing (§M06)
        Route::resource('cost-sheets', CostSheetController::class)->parameters(['cost-sheets' => 'cost_sheet']);
        Route::post('cost-sheets/{cost_sheet}/approve', [CostSheetController::class, 'approve'])->name('cost-sheets.approve');
        Route::get('cost-sheets/{cost_sheet}/pdf', [CostSheetController::class, 'pdf'])->name('cost-sheets.pdf');

        // Sales Contract / Order Confirmation (§M07)
        Route::resource('sales-contracts', SalesContractController::class)->parameters(['sales-contracts' => 'sales_contract']);
        Route::post('sales-contracts/{sales_contract}/confirm', [SalesContractController::class, 'confirm'])->name('sales-contracts.confirm');
        Route::post('sales-contracts/{sales_contract}/close', [SalesContractController::class, 'close'])->name('sales-contracts.close');
        Route::get('sales-contracts/{sales_contract}/pos/create', [SalesContractPoController::class, 'create'])->name('sales-contracts.pos.create');
        Route::post('sales-contracts/{sales_contract}/pos', [SalesContractPoController::class, 'store'])->name('sales-contracts.pos.store');
        Route::get('sales-contracts/{sales_contract}/pos/{sales_contract_po}/edit', [SalesContractPoController::class, 'edit'])->name('sales-contracts.pos.edit');
        Route::put('sales-contracts/{sales_contract}/pos/{sales_contract_po}', [SalesContractPoController::class, 'update'])->name('sales-contracts.pos.update');
        Route::delete('sales-contracts/{sales_contract}/pos/{sales_contract_po}', [SalesContractPoController::class, 'destroy'])->name('sales-contracts.pos.destroy');
        Route::get('sales-contracts/{sales_contract}/pos/{sales_contract_po}/pdf', [SalesContractPoController::class, 'pdf'])->name('sales-contracts.pos.pdf');
        Route::post('sales-contracts/{sales_contract}/pos/{sales_contract_po}/revise', [SalesContractPoController::class, 'revise'])->name('sales-contracts.pos.revise');
        Route::post('sales-contracts/{sales_contract}/pos/import', [SalesContractPoController::class, 'importExcel'])->name('sales-contracts.pos.import');

        // T&A (§M08 — the core module)
        Route::resource('tna-templates', TnaTemplateController::class)->only(['index', 'store', 'show']);
        Route::post('tna-templates/{tna_template}/clone', [TnaTemplateController::class, 'clone'])->name('tna-templates.clone');
        Route::post('tna-templates/{tna_template}/tasks', [TnaTemplateController::class, 'storeTask'])->name('tna-templates.tasks.store');
        Route::put('tna-templates/{tna_template}/tasks/{task}', [TnaTemplateController::class, 'updateTask'])->name('tna-templates.tasks.update');
        Route::delete('tna-templates/{tna_template}/tasks/{task}', [TnaTemplateController::class, 'destroyTask'])->name('tna-templates.tasks.destroy');

        Route::get('tna-plans-grid', [TnaPlanController::class, 'grid'])->name('tna-plans.grid');
        Route::resource('tna-plans', TnaPlanController::class)->only(['index', 'show']);
        Route::put('tna-plans/{tna_plan}/tasks/{task}', [TnaPlanController::class, 'updateTask'])->name('tna-plans.tasks.update');
        Route::post('tna-plans/{tna_plan}/evaluate-pcd', [TnaPlanController::class, 'evaluatePcd'])->name('tna-plans.evaluate-pcd');
        Route::post('tna-plans/{tna_plan}/override-pcd', [TnaPlanController::class, 'overridePcd'])->name('tna-plans.override-pcd');
        Route::get('tna-plans-export/excel', [TnaPlanController::class, 'exportExcel'])->name('tna-plans.export.excel');
        Route::post('tna-plans-import/excel', [TnaPlanController::class, 'importExcel'])->name('tna-plans.import.excel');

        Route::get('tna-alerts', [TnaAlertController::class, 'index'])->name('tna-alerts.index');
        Route::post('tna-alerts/{tna_alert}/mark-read', [TnaAlertController::class, 'markRead'])->name('tna-alerts.mark-read');

        // Sub-T&A (§M09)
        Route::resource('tna-sub-plans', TnaSubPlanController::class)->only(['index', 'create', 'store', 'show']);
        Route::post('tna-sub-plans/{tna_sub_plan}/logs', [TnaSubPlanController::class, 'addLog'])->name('tna-sub-plans.logs.store');

        // Material Booking (§M10)
        Route::resource('material-bookings', MaterialBookingController::class)->only(['index', 'create', 'store', 'show']);
        Route::post('boms/{bom}/create-booking', [MaterialBookingController::class, 'createFromBom'])->name('material-bookings.create-from-bom');
        Route::put('material-bookings/{material_booking}/dates', [MaterialBookingController::class, 'updateDates'])->name('material-bookings.update-dates');
        Route::post('material-bookings/{material_booking}/consignments', [MaterialBookingController::class, 'addConsignment'])->name('material-bookings.consignments.store');
        Route::post('material-bookings/{material_booking}/consignments/{consignment}/receive', [MaterialBookingController::class, 'receiveConsignment'])->name('material-bookings.consignments.receive');
        Route::post('material-bookings/{material_booking}/receive-item', [MaterialBookingController::class, 'receiveItem'])->name('material-bookings.receive-item');

        // Production Handover Bridge (§M11)
        Route::get('production-handovers', [ProductionHandoverController::class, 'index'])->name('production-handovers.index');
        Route::get('production-handovers/{sales_contract_po}', [ProductionHandoverController::class, 'show'])->name('production-handovers.show');
        Route::post('production-handovers/{sales_contract_po}/push', [ProductionHandoverController::class, 'push'])->name('production-handovers.push');
        Route::post('production-handovers/{sales_contract_po}/rollback', [ProductionHandoverController::class, 'rollback'])->name('production-handovers.rollback');
        Route::post('styles/{style}/map-production', [ProductionHandoverController::class, 'mapStyle'])->name('styles.map-production');

        // Shipment Plan (§M12)
        Route::get('shipment-plans', [ShipmentPlanController::class, 'index'])->name('shipment-plans.index');
        Route::get('shipment-plans/{sales_contract_po}', [ShipmentPlanController::class, 'show'])->name('shipment-plans.show');
        Route::post('shipment-plans/{sales_contract_po}/bookings', [ShipmentPlanController::class, 'storeBooking'])->name('shipment-plans.bookings.store');

        // Documentation (§M13)
        Route::get('sales-contracts/{sales_contract}/documents', [OrderDocumentController::class, 'index'])->name('order-documents.index');
        Route::post('sales-contracts/{sales_contract}/documents/{document}/upload', [OrderDocumentController::class, 'upload'])->name('order-documents.upload');
        Route::post('sales-contracts/{sales_contract}/documents/{document}/approve', [OrderDocumentController::class, 'approve'])->name('order-documents.approve');

        // Buyer Communication (§M14)
        Route::get('communication-logs', [CommunicationLogController::class, 'index'])->name('communication-logs.index');
        Route::post('communication-logs', [CommunicationLogController::class, 'store'])->name('communication-logs.store');

        // Dashboard & Reports (§M15) — one dashboard, matching every sibling package's convention.
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/{key}', [ReportController::class, 'show'])->name('reports.show');
        Route::get('reports/{key}/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
        Route::get('reports/{key}/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
    });
