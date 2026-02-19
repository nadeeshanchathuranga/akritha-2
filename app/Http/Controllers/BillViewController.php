<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class BillViewController extends Controller
{
    /**
     * Display a listing of all bills
     */
    public function index(Request $request)
    {
        // Check authorization
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        // Get search query if provided
        $search = $request->input('search', '');

        // Fetch all bills with relationships
        $billsQuery = Sale::with(['customer', 'employee', 'user', 'saleItems.product'])
            ->orderBy('created_at', 'desc');

        // Apply search filter if provided
        if ($search) {
            $billsQuery->where('order_id', 'like', "%{$search}%")
                ->orWhereHas('customer', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
        }

        $bills = $billsQuery->paginate(15);

        // Transform bills for frontend
        $bills->getCollection()->transform(function ($bill) {
            return [
                'id' => $bill->id,
                'order_id' => $bill->order_id,
                'customer_name' => $bill->customer ? $bill->customer->name : 'N/A',
                'customer_phone' => $bill->customer ? $bill->customer->phone : 'N/A',
                'total_amount' => $bill->total_amount,
                'discount' => $bill->discount,
                'custom_discount' => $bill->custom_discount,
                'payment_method' => $bill->payment_method,
                'sale_date' => $bill->sale_date,
                'created_at' => $bill->created_at->format('Y-m-d H:i:s'),
                'employee_name' => $bill->employee ? $bill->employee->name : ($bill->user ? $bill->user->name : 'N/A'),
            ];
        });

        return Inertia::render('BillView/Index', [
            'bills' => $bills,
            'search' => $search,
        ]);
    }

    /**
     * Display a specific bill with details
     */
    public function show($id)
    {
        // Check authorization
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        // Fetch the bill with all relationships
        $bill = Sale::with(['customer', 'employee', 'user', 'saleItems.product'])
            ->findOrFail($id);

        // Transform bill data
        $billData = [
            'id' => $bill->id,
            'order_id' => $bill->order_id,
            'customer' => $bill->customer ? [
                'id' => $bill->customer->id,
                'name' => $bill->customer->name,
                'phone' => $bill->customer->phone,
                'email' => $bill->customer->email ?? 'N/A',
            ] : null,
            'employee' => $bill->employee ? [
                'id' => $bill->employee->id,
                'name' => $bill->employee->name,
            ] : ($bill->user ? [
                'id' => $bill->user->id,
                'name' => $bill->user->name,
            ] : null),
            'items' => $bill->saleItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_name' => $item->product ? $item->product->name : 'N/A',
                    'product_code' => $item->product ? $item->product->code : 'N/A',
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                ];
            })->toArray(),
            'subtotal' => $bill->total_amount + ($bill->discount ?? 0),
            'discount' => $bill->discount ?? 0,
            'custom_discount' => $bill->custom_discount ?? 0,
            'total_amount' => $bill->total_amount,
            'payment_method' => $bill->payment_method,
            'cash' => $bill->cash,
            'sale_date' => $bill->sale_date,
            'created_at' => $bill->created_at->format('Y-m-d H:i:s'),
        ];

        return Inertia::render('BillView/Show', [
            'bill' => $billData,
        ]);
    }

    /**
     * Search bills via API
     */
    public function search(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        $search = $request->input('search', '');

        $bills = Sale::with(['customer', 'employee', 'user'])
            ->where('order_id', 'like', "%{$search}%")
            ->orWhereHas('customer', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get()
            ->map(function ($bill) {
                return [
                    'id' => $bill->id,
                    'order_id' => $bill->order_id,
                    'customer_name' => $bill->customer ? $bill->customer->name : 'N/A',
                    'customer_phone' => $bill->customer ? $bill->customer->phone : 'N/A',
                    'total_amount' => $bill->total_amount,
                    'created_at' => $bill->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $bills,
        ]);
    }
}
