<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WarrantyClaim;
use App\Models\Warehouse;

class DefectiveStockController extends Controller
{
    public function index(Request $request)
    {
        $query = WarrantyClaim::with(['party', 'salesInvoice', 'salesInvoiceItem', 'simpleProduct', 'variantProduct', 'warehouse'])
            ->where('replaced_qty', '>', 0);

        $this->applyFilters($query, $request);

        $claims = $query->orderBy('created_at', 'desc')->paginate(15);

        // Stats — same filters apply karke filtered counts milenge
        $statsBase = WarrantyClaim::where('replaced_qty', '>', 0);
        $this->applyFilters($statsBase, $request);

        $stats = [
            'total'          => (clone $statsBase)->count(),
            'pending_repair' => (clone $statsBase)->where('defective_stock_status', 'pending_repair')->count(),
            'repaired'       => (clone $statsBase)->where('defective_stock_status', 'repaired')->count(),
            'scrapped'       => (clone $statsBase)->where('defective_stock_status', 'scrapped')->count(),
        ];

        $warehouses = Warehouse::orderBy('name')->get();

        return view('admin.warranty.defective-stock-index', compact('claims', 'stats', 'warehouses'));
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('warranty_claim_number', 'like', "%{$search}%")
                  ->orWhereHas('party', fn($sq) => $sq->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"))
                  ->orWhereHas('salesInvoiceItem', fn($sq) => $sq->where('product_name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"))
                  ->orWhereHas('warehouse', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('defective_stock_status')) {
            $query->where('defective_stock_status', $request->defective_stock_status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('claim_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('claim_date', '<=', $request->to_date);
        }
    }

public function detail($id)
{
    try {
        // Base query without relationships
        $claim = WarrantyClaim::findOrFail($id);

        // Manually load relationships with safe handling
        $claimData = [
            '_id' => $claim->_id,
            'id' => $claim->id,
            'warranty_claim_number' => $claim->warranty_claim_number,
            'claim_date' => $claim->claim_date,
            'defective_stock_status' => $claim->defective_stock_status,
            'replaced_qty' => $claim->replaced_qty,
            'repaired_qty' => $claim->repaired_qty,
            'scrapped_qty' => $claim->scrapped_qty,
            'notes' => $claim->notes,
            'scrap_reason' => $claim->scrap_reason,
            'approved_at' => $claim->approved_at,
            'repaired_at' => $claim->repaired_at,
            'scrapped_at' => $claim->scrapped_at,
        ];

        // Load party safely
        if ($claim->party) {
            $party = $claim->party;
            $claimData['party'] = [
                'name' => $party->name ?? null,
                'phone' => $party->phone ?? null,
            ];
        } else {
            $claimData['party'] = null;
        }

        // Load warehouse safely
        if ($claim->warehouse) {
            $warehouse = $claim->warehouse;
            $claimData['warehouse'] = [
                'name' => $warehouse->name ?? null,
            ];
        } else {
            $claimData['warehouse'] = null;
        }

        // Load sales invoice safely
        if ($claim->salesInvoice) {
            $invoice = $claim->salesInvoice;
            $claimData['sales_invoice'] = [
                '_id' => $invoice->_id,
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number ?? null,
            ];
        } else {
            $claimData['sales_invoice'] = null;
        }

        // Load sales invoice item safely - YAHAN SE VARIANT/SIMPLE DONO AAYENGE
        if ($claim->salesInvoiceItem) {
            $item = $claim->salesInvoiceItem;
            $claimData['sales_invoice_item'] = [
                'product_name' => $item->product_name ?? null,
                'variant_name' => $item->variant_name ?? null, // Variant name yahan se aayega
                'sku' => $item->sku ?? null,
            ];
        } else {
            $claimData['sales_invoice_item'] = null;
        }

        return response()->json([
            'success' => true,
            'claim' => $claimData
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}
}
