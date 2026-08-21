<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\OrderRequest;
use ME\MerchandisingTrace\Models\Buyer;
use ME\MerchandisingTrace\Models\Color;
use ME\MerchandisingTrace\Models\Order;
use ME\MerchandisingTrace\Models\Size;
use ME\MerchandisingTrace\Models\Style;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_order.list');

        $orders = Order::query()
            ->with(['buyer', 'style'])
            ->when($request->filled('search'), fn ($q) => $q->where('po_number', 'like', '%' . $request->search . '%'))
            ->when($request->filled('buyer_id'), fn ($q) => $q->where('buyer_id', $request->buyer_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $buyersOptions = Buyer::query()->active()->orderBy('name')->get();

        return view('merchandising-trace::admin.orders.index', compact('orders', 'buyersOptions'));
    }

    public function printList(Request $request): View
    {
        $this->authorize('merch_order.list');

        $orders = Order::query()
            ->with(['buyer', 'style'])
            ->when($request->filled('search'), fn ($q) => $q->where('po_number', 'like', '%' . $request->search . '%'))
            ->when($request->filled('buyer_id'), fn ($q) => $q->where('buyer_id', $request->buyer_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('id')->get();

        return view('merchandising-trace::admin.partials.print-table', [
            'title'   => 'Orders',
            'columns' => ['po_number' => 'PO Number', 'buyer.name' => 'Buyer', 'style.name' => 'Style', 'order_qty' => 'Qty', 'status' => 'Status'],
            'rows'    => $orders,
        ]);
    }

    public function create(): View
    {
        $this->authorize('merch_order.add');

        return view('merchandising-trace::admin.orders.create', $this->formOptions());
    }

    public function store(OrderRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $order = DB::transaction(function () use ($data) {
            $order = Order::create([
                'buyer_id'      => $data['buyer_id'],
                'style_id'      => $data['style_id'],
                'description'   => $data['description'] ?? null,
                'delivery_date' => $data['delivery_date'] ?? null,
                'price'         => $data['price'] ?? null,
                'currency'      => $data['currency'],
                'status'        => $data['status'],
                'remarks'       => $data['remarks'] ?? null,
                'created_by'    => auth()->id(),
            ]);

            foreach ($data['items'] as $line) {
                $order->items()->create($line);
            }

            $order->refreshOrderQty();

            return $order;
        });

        return redirect()->route('merchandising-trace.orders.index')->with('success', "Order {$order->po_number} created successfully.");
    }

    public function show(Order $order): View
    {
        $this->authorize('merch_order.view');

        $order->load(['buyer', 'style.brand', 'items.color', 'items.size', 'salesContracts']);

        return view('merchandising-trace::admin.orders.show', ['order' => $order, 'bom' => $order->bom()]);
    }

    public function print(Order $order): View
    {
        $this->authorize('merch_order.view');

        $order->load(['buyer', 'style', 'items.color', 'items.size']);

        return view('merchandising-trace::admin.partials.print-detail', [
            'title'     => 'Buyer Order',
            'docNumber' => $order->po_number,
            'meta'      => [
                'Buyer'         => $order->buyer->name ?? '-',
                'Style'         => ($order->style->style_no ?? '-') . ' — ' . ($order->style->name ?? ''),
                'Order Qty'     => number_format($order->order_qty),
                'Delivery Date' => optional($order->delivery_date)->format('d M Y') ?? '-',
                'Price'         => $order->price !== null ? number_format($order->price, 2) . ' ' . $order->currency : '-',
                'Status'        => ucfirst(str_replace('_', ' ', $order->status)),
            ],
            'sections' => [[
                'title'   => 'Color / Size Breakdown',
                'columns' => ['color.name' => 'Color', 'size.name' => 'Size', 'qty' => 'Qty'],
                'rows'    => $order->items,
                'summaryRow' => ['color.name' => 'Total', 'qty' => number_format($order->order_qty)],
            ]],
            'remarks' => $order->remarks,
        ]);
    }

    public function edit(Order $order): View
    {
        $this->authorize('merch_order.edit');

        $order->load('items');

        return view('merchandising-trace::admin.orders.edit', ['order' => $order] + $this->formOptions());
    }

    public function update(OrderRequest $request, Order $order): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $order) {
            $order->update([
                'buyer_id'      => $data['buyer_id'],
                'style_id'      => $data['style_id'],
                'description'   => $data['description'] ?? null,
                'delivery_date' => $data['delivery_date'] ?? null,
                'price'         => $data['price'] ?? null,
                'currency'      => $data['currency'],
                'status'        => $data['status'],
                'remarks'       => $data['remarks'] ?? null,
            ]);

            $order->items()->delete();
            foreach ($data['items'] as $line) {
                $order->items()->create($line);
            }

            $order->refreshOrderQty();
        });

        return redirect()->route('merchandising-trace.orders.index')->with('success', "Order {$order->po_number} updated successfully.");
    }

    public function destroy(Order $order): RedirectResponse
    {
        $this->authorize('merch_order.delete');

        if ($order->salesContracts()->exists()) {
            return back()->with('error', 'This order already has a sales contract and cannot be deleted.');
        }

        $order->delete();

        return back()->with('success', 'Order deleted successfully.');
    }

    private function formOptions(): array
    {
        return [
            'buyersOptions' => Buyer::query()->active()->orderBy('name')->get(),
            'stylesOptions' => Style::query()->active()->orderBy('name')->get(),
            'colorsOptions' => Color::query()->active()->orderBy('name')->get(),
            'sizesOptions'  => Size::query()->active()->orderBy('sort_order')->orderBy('name')->get(),
        ];
    }
}
