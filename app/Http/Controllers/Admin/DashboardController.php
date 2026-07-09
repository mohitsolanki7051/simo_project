<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SimpleProduct;
use App\Models\VariantProduct;
use App\Models\Customer;
use App\Models\Salesman;
use App\Models\Warehouse;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Safely convert MongoDB Decimal128 / int / string / null to float
     */
    private function toFloat($value): float
    {
        if (is_null($value)) return 0.0;
        if ($value instanceof \MongoDB\BSON\Decimal128) return (float)(string)$value;
        if ($value instanceof \MongoDB\BSON\Int64)     return (float)(string)$value;
        return (float)$value;
    }

    public function index()
    {
        // ── Products ──────────────────────────────────────────────────────
        $totalSimple      = SimpleProduct::count();
        $totalVariant     = VariantProduct::count();
        $totalProducts    = $totalSimple + $totalVariant;
        $activeProducts   = SimpleProduct::where('status', 'active')->count()
                          + VariantProduct::where('status', 'active')->count();
        $inactiveProducts = $totalProducts - $activeProducts;

        // ── Parties ───────────────────────────────────────────────────────
        $totalCustomers     = Customer::where('party_type', 'customer')->count();
        $activeCustomers    = Customer::where('party_type', 'customer')->where('status', 'active')->count();
        $totalDealers       = Customer::where('party_type', 'dealer')->count();
        $activeDealers      = Customer::where('party_type', 'dealer')->where('status', 'active')->count();
        $totalDistributors  = Customer::where('party_type', 'distributor')->count();
        $activeDistributors = Customer::where('party_type', 'distributor')->where('status', 'active')->count();
        $totalParties       = $totalCustomers + $totalDealers + $totalDistributors;

        // ── Salesmen ──────────────────────────────────────────────────────
        $totalSalesmen    = Salesman::count();
        $activeSalesmen   = Salesman::where('status', 'active')->count();
        $inactiveSalesmen = $totalSalesmen - $activeSalesmen;

        // ── Warehouses ────────────────────────────────────────────────────
        $totalWarehouses  = Warehouse::count();
        $activeWarehouses = Warehouse::where('status', 'active')->count();
        $mainWarehouse    = Warehouse::main()->first();

        // ── Sales Invoices ────────────────────────────────────────────────
        $totalInvoices   = SalesInvoice::count();
        $totalRevenue    = $this->toFloat(SalesInvoice::sum('grand_total'));
        $totalCollected  = $this->toFloat(SalesInvoice::sum('total_paid'));
        $paidInvoices    = SalesInvoice::where('payment_status', 'paid')->count();
        $unpaidInvoices  = SalesInvoice::where('payment_status', 'unpaid')->count();
        $partialInvoices = SalesInvoice::where('payment_status', 'partial')->count();
        $unpaidAmount    = $this->toFloat(SalesInvoice::where('payment_status', 'unpaid')->sum('balance_amount'))
                         + $this->toFloat(SalesInvoice::where('payment_status', 'partial')->sum('balance_amount'));

        // ── Monthly Sales — last 7 months for bar chart ───────────────────
        $monthlySales   = [];
        $maxMonthlySale = 0;
        for ($i = 6; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $total = $this->toFloat(
                SalesInvoice::whereYear('invoice_date', $month->year)
                    ->whereMonth('invoice_date', $month->month)
                    ->sum('grand_total')
            );
            $monthlySales[] = ['label' => $month->format('M'), 'total' => $total];
            if ($total > $maxMonthlySale) $maxMonthlySale = $total;
        }

        // ── Recent 8 Invoices ─────────────────────────────────────────────
        $recentSales = SalesInvoice::with('party')
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();


// ── Revenue Breakdown (Today / Week / Month) ──────────────────────
$todayRevenue   = $this->toFloat(SalesInvoice::whereDate('invoice_date', Carbon::today())->sum('grand_total'));
$todayCount     = SalesInvoice::whereDate('invoice_date', Carbon::today())->count();

$weekRevenue    = $this->toFloat(SalesInvoice::whereBetween('invoice_date', [
                    Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()
                  ])->sum('grand_total'));
$weekCount      = SalesInvoice::whereBetween('invoice_date', [
                    Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()
                  ])->count();

$monthRevenue   = $this->toFloat(SalesInvoice::whereYear('invoice_date', Carbon::now()->year)
                    ->whereMonth('invoice_date', Carbon::now()->month)
                    ->sum('grand_total'));
$monthCount     = SalesInvoice::whereYear('invoice_date', Carbon::now()->year)
                    ->whereMonth('invoice_date', Carbon::now()->month)
                    ->count();

        // ── Top Selling Products (Current Month) ──────────────────────────
$startOfMonth = Carbon::now()->startOfMonth();
$endOfMonth   = Carbon::now()->endOfMonth();

$topProducts = SalesInvoiceItem::whereBetween('created_at', [$startOfMonth, $endOfMonth])
    ->get()
    ->groupBy('product_name')
    ->map(function ($items, $productName) {
        return (object)[
            'product_name' => $productName,
            'total_qty'    => $items->sum(fn($i) => (float) $i->quantity),
            'total_orders' => $items->count(),
            'unit'         => $items->first()->unit ?? 'qty',
        ];
    })
    ->sortByDesc('total_qty')
    ->take(5)
    ->values();

        // ── Stats Array ───────────────────────────────────────────────────
        $stats = [
            'total_products'         => $totalProducts,
            'total_simple_products'  => $totalSimple,
            'total_variant_products' => $totalVariant,
            'active_products'        => $activeProducts,
            'inactive_products'      => $inactiveProducts,

            'total_parties'          => $totalParties,
            'total_customers'        => $totalCustomers,
            'active_customers'       => $activeCustomers,
            'total_dealers'          => $totalDealers,
            'active_dealers'         => $activeDealers,
            'total_distributors'     => $totalDistributors,
            'active_distributors'    => $activeDistributors,

            'total_salesmen'         => $totalSalesmen,
            'active_salesmen'        => $activeSalesmen,
            'inactive_salesmen'      => $inactiveSalesmen,

            'total_warehouses'       => $totalWarehouses,
            'active_warehouses'      => $activeWarehouses,
            'main_warehouse'         => $mainWarehouse?->name,

            'total_invoices'         => $totalInvoices,
            'total_revenue'          => $totalRevenue,
            'total_collected'        => $totalCollected,
            'paid_invoices'          => $paidInvoices,
            'unpaid_invoices'        => $unpaidInvoices,
            'partial_invoices'       => $partialInvoices,
            'unpaid_amount'          => $unpaidAmount,

            'monthly_sales'          => $monthlySales,
            'max_monthly_sale'       => $maxMonthlySale ?: 1,
            // Revenue breakdown
'today_revenue'  => $todayRevenue,
'today_count'    => $todayCount,
'week_revenue'   => $weekRevenue,
'week_count'     => $weekCount,
'month_revenue'  => $monthRevenue,
'month_count'    => $monthCount,
        ];

        return view('admin.dashboard', compact('stats', 'recentSales', 'topProducts'));
    }
}