<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class SalesReportController extends Controller
{
    /**
     * Display sales report with date filtering
     */
    public function index(Request $request)
    {
        // Check authorization
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        // Get the date from request, default to today
        $date = $request->input('date') 
            ? Carbon::parse($request->input('date'))->toDateString()
            : Carbon::now()->toDateString();

        // Fetch sales items for the given date
        // Group by product and sum quantities
        $salesData = SaleItem::with('product')
            ->whereHas('sale', function ($query) use ($date) {
                $query->whereDate('created_at', $date);
            })
            ->get()
            ->groupBy('product_id')
            ->map(function ($items, $productId) {
                $product = $items->first()->product;
                return [
                    'product_id' => $productId,
                    'product_name' => $product ? $product->name : 'Unknown',
                    'total_quantity' => $items->sum('quantity'),
                    'unit_price' => $items->first()->unit_price ?? 0,
                    'total_amount' => $items->sum('total_price'),
                ];
            })
            ->values()
            ->toArray();

        // Calculate total sales price and number of sales
        $totalSalesPrice = array_sum(array_column($salesData, 'total_amount'));
        $numberOfSales = Sale::whereDate('created_at', $date)->count();

        return Inertia::render('SalesReport/Index', [
            'salesData' => $salesData,
            'selectedDate' => $date,
            'totalSalesPrice' => $totalSalesPrice,
            'numberOfSales' => $numberOfSales,
        ]);
    }

    /**
     * Fetch sales data via API (for date change)
     */
    public function fetchByDate(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        $date = $request->input('date')
            ? Carbon::parse($request->input('date'))->toDateString()
            : Carbon::now()->toDateString();

        $salesData = SaleItem::with('product')
            ->whereHas('sale', function ($query) use ($date) {
                $query->whereDate('created_at', $date);
            })
            ->get()
            ->groupBy('product_id')
            ->map(function ($items) {
                $product = $items->first()->product;
                return [
                    'product_id' => $items->first()->product_id,
                    'product_name' => $product ? $product->name : 'Unknown',
                    'total_quantity' => $items->sum('quantity'),
                    'unit_price' => $items->first()->unit_price ?? 0,
                    'total_amount' => $items->sum('total_price'),
                ];
            })
            ->values()
            ->toArray();

        // Calculate total sales price and number of sales
        $totalSalesPrice = array_sum(array_column($salesData, 'total_amount'));
        $numberOfSales = Sale::whereDate('created_at', $date)->count();

        return response()->json([
            'success' => true,
            'data' => $salesData,
            'totalSalesPrice' => $totalSalesPrice,
            'numberOfSales' => $numberOfSales,
        ]);
    }
}
