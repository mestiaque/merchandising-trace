<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\MaterialBookingRequest;
use ME\MerchandisingTrace\Models\MaterialBooking;
use ME\MerchandisingTrace\Models\Order;
use ME\MerchandisingTrace\Models\Supplier;
use ME\MerchandisingTrace\Models\Uom;

class MaterialBookingController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_material_booking.list');

        $material_bookings = MaterialBooking::query()
            ->with(['order', 'supplier', 'unit'])
            ->when($request->filled('order_id'), fn ($q) => $q->where('order_id', $request->order_id))
            ->when($request->filled('material_type'), fn ($q) => $q->where('material_type', $request->material_type))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.material-bookings.index', ['material_bookings' => $material_bookings] + $this->formOptions());
    }

    public function print(Request $request): View
    {
        $this->authorize('merch_material_booking.list');

        $material_bookings = MaterialBooking::query()
            ->with(['order', 'supplier', 'unit'])
            ->when($request->filled('order_id'), fn ($q) => $q->where('order_id', $request->order_id))
            ->when($request->filled('material_type'), fn ($q) => $q->where('material_type', $request->material_type))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('id')->get();

        return view('merchandising-trace::admin.partials.print-table', [
            'title'   => 'Material Bookings',
            'columns' => ['booking_number' => 'Booking No', 'order.po_number' => 'Order', 'material_type' => 'Type', 'material_name' => 'Material', 'qty' => 'Qty', 'status' => 'Status'],
            'rows'    => $material_bookings,
        ]);
    }

    public function store(MaterialBookingRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        $booking = MaterialBooking::create($data);

        return back()->with('success', "Material booking {$booking->booking_number} created successfully.");
    }

    public function update(MaterialBookingRequest $request, MaterialBooking $material_booking): RedirectResponse
    {
        $material_booking->update($request->validated());

        return back()->with('success', 'Material booking updated successfully.');
    }

    public function destroy(MaterialBooking $material_booking): RedirectResponse
    {
        $this->authorize('merch_material_booking.delete');

        $material_booking->delete();

        return back()->with('success', 'Material booking deleted successfully.');
    }

    private function formOptions(): array
    {
        return [
            'ordersOptions'    => Order::query()->orderByDesc('id')->limit(200)->get(),
            'suppliersOptions' => Supplier::query()->active()->orderBy('name')->get(),
            'unitsOptions'     => Uom::query()->active()->orderBy('name')->get(),
        ];
    }
}
