<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\PurchasePayment;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Vendor;
use App\Models\VendorAddress;
use App\Models\Salesman;
use App\Models\SimpleProduct;
use App\Models\VariantProduct;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\WarehouseMovement;
use App\Models\InvoiceSetting;
use App\Models\Category;
use App\Models\PricingSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PurchaseInvoiceController extends Controller
{
    // =========================================================
    //  HELPERS
    // =========================================================

    /**
     * Given a purchase_invoice record, determine the party type by:
     *  - checking whether party_id matches a Vendor (→ 'vendor')
     *  - otherwise loading the Customer record and using its party_type field
     *
     * Returns: ['type' => 'vendor'|'dealer'|'distributor', 'model' => Vendor|Customer|null]
     */
    private function resolvePartyFromInvoice(PurchaseInvoice $invoice): array
    {
        // Try vendor first
        if ($invoice->party_id) {
            if ($invoice->party_type === 'vendor') {
                return [
                    'type' => 'vendor',
                    'model' => Vendor::find($invoice->party_id)
                ];
            }

            if (in_array($invoice->party_type, ['dealer', 'distributor'])) {
                return [
                    'type' => $invoice->party_type,
                    'model' => Customer::find($invoice->party_id)
                ];
            }
        }
            return ['type' => 'vendor', 'model' => null];
    }

    /**
     * Resolve party from incoming request data.
     * party_id / vendor_id(form) / dealer_id / distributor_id are separate form fields — we pick the right one.
     * Returns [$partyId, $billingAddress, $shippingAddress, $purchaseExecutiveId, $partyModel]
     */
    private function resolvePartyFromRequest(Request $request): array
    {
        $partyType = $request->party_type; // Only used inside this function to select correct ID
        $purchaseExecutiveId = null;

        if ($partyType === 'vendor') {
            $vendor = Vendor::with('addresses')->findOrFail($request->vendor_id);
            $billing  = $vendor->addresses->where('type','billing')->where('is_default',true)->first();
            $shipping = $vendor->addresses->where('type','shipping')->where('is_default',true)->first();
            $purchaseExecutiveId = $request->purchase_executive_id
                ?? $vendor->purchase_executive_id
                ?? null;
            return [$vendor->id, $billing, $shipping, $purchaseExecutiveId, $vendor];

        } elseif ($partyType === 'dealer') {
            $dealer = Customer::with('addresses')->findOrFail($request->dealer_id);
            $billing  = $dealer->addresses->where('type','billing')->where('is_default',true)->first();
            $shipping = $dealer->addresses->where('type','shipping')->where('is_default',true)->first();
            $purchaseExecutiveId = $request->purchase_executive_id; // manual
            return [(string)$dealer->_id, $billing, $shipping, $purchaseExecutiveId, $dealer];

        } else { // distributor
            $dist = Customer::with('addresses')->findOrFail($request->distributor_id);
            $billing  = $dist->addresses->where('type','billing')->where('is_default',true)->first();
            $shipping = $dist->addresses->where('type','shipping')->where('is_default',true)->first();
            $purchaseExecutiveId = $request->purchase_executive_id; // manual
            return [(string)$dist->_id, $billing, $shipping, $purchaseExecutiveId, $dist];
        }
    }

private function generateInvoiceNumber(string $invoiceType = 'gst'): string
{
    $fy     = $this->getFinancialYear();
    $prefix = $invoiceType === 'cash' ? 'SIM/PICM' : 'SIM/PI';

    $escapedPrefix = str_replace('/', '\/', $prefix);

    $last = PurchaseInvoice::where('invoice_number', 'regex', "/^{$escapedPrefix}\/{$fy}\/\d+$/")
                ->orderBy('invoice_number', 'desc')
                ->first();

    $next = $last
        ? str_pad((int) preg_replace('/.*\/(\d+)$/', '$1', $last->invoice_number) + 1, 6, '0', STR_PAD_LEFT)
        : '000001';

    return "{$prefix}/{$fy}/{$next}";
}

    private function getFinancialYear(): string
    {
        $m = (int) date('m');
        $y = (int) date('y');
        return $m >= 4 ? "{$y}-" . ($y + 1) : ($y - 1) . "-{$y}";
    }

    /**
     * Calculate item totals from the items array.
     */
    private function calcItemTotals(array $items, string $invoiceType, bool $isIntra): array
    {
        $subtotal  = $totalMRP = $taxTotal = $cgst = $sgst = $igst = 0.0;

        foreach ($items as $item) {
            $qty       = (float) $item['quantity'];
            $price     = (float) $item['purchase_price'];
            $mrp       = (float) ($item['mrp_price'] ?? $price);
            $itemTotal = $qty * $price;

            $subtotal  += $itemTotal;
            $totalMRP  += $qty * $mrp;

            if ($invoiceType === 'gst') {
                $tax      = $itemTotal * ((float)($item['tax_percent'] ?? 0)) / 100;
                $taxTotal += $tax;
                $isIntra ? ($cgst += $tax / 2) && ($sgst += $tax / 2) : ($igst += $tax);
            }
        }

        return compact('subtotal','totalMRP','taxTotal','cgst','sgst','igst');
    }

    private function resolvePaymentStatus(float $paid, float $balance): string
    {
        if ($paid <= 0)         return 'unpaid';
        if ($balance <= 0.01)  return 'paid';
        return 'partial';
    }

    // =========================================================
    //  INDEX
    // =========================================================

  public function index(Request $request)
    {
        $query = PurchaseInvoice::with(['warehouse', 'purchaseExecutive'])
                    ->orderBy('created_at', 'desc');

        if ($request->filled('period')) {
            $p = $request->period;
            if ($p === 'today') {
                $query->whereDate('invoice_date', today());
            } elseif ($p === 'custom') {
                if ($request->filled('date_from')) $query->whereDate('invoice_date', '>=', $request->date_from);
                if ($request->filled('date_to'))   $query->whereDate('invoice_date', '<=', $request->date_to);
            } elseif (is_numeric($p)) {
                $query->whereDate('invoice_date', '>=', now()->subDays((int)$p)->toDateString());
            }
        }

        if ($request->filled('date')) {
            $query->whereDate('invoice_date', $request->date);
        }

        if ($request->filled('invoice_number')) $query->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
        if ($request->filled('invoice_type'))   $query->where('invoice_type', $request->invoice_type);
        if ($request->filled('payment_status')) $query->where('payment_status', $request->payment_status);
        if ($request->filled('status'))         $query->where('status', $request->status);
        if ($request->filled('warehouse_id'))   $query->where('warehouse_id', $request->warehouse_id);
        if ($request->filled('party_id'))       $query->where('party_id', $request->party_id);

        $invoices = $query->paginate(20)->withQueryString();
        $partyIds = $invoices->pluck('party_id')
            ->map(fn($id) => (string)$id)
            ->unique()
            ->values()
            ->toArray();

        $debitNoteMap = [];

        if (!empty($partyIds)) {
            \App\Models\DebitNote::whereIn('party_id', $partyIds)
                ->whereIn('status', ['active', 'partial'])
                ->where('remaining_amount', '>', 0)
                ->get()
                ->each(function ($dn) use (&$debitNoteMap) {
                    $pid = (string) $dn->party_id;
                    $debitNoteMap[$pid] = ($debitNoteMap[$pid] ?? 0) + $this->decimalToFloat($dn->remaining_amount);
                });
        }
        // Party dropdown for filter
        $vendors = Vendor::where('status', 'active')->orderBy('company_name')
            ->get()->map(fn($v) => [
                'id'   => $v->id,
                'name' => $v->company_name . ' (Vendor)',
            ]);

        $dealers = Customer::where('status', 'active')
            ->where('party_type', 'dealer')->orderBy('name')
            ->get()->map(fn($c) => [
                'id'   => (string) $c->_id,
                'name' => $c->name . ' (Dealer)',
            ]);

        $distributors = Customer::where('status', 'active')
            ->where('party_type', 'distributor')->orderBy('name')
            ->get()->map(fn($c) => [
                'id'   => (string) $c->_id,
                'name' => $c->name . ' (Distributor)',
            ]);

        $allParties = collect()
            ->merge($vendors)
            ->merge($dealers)
            ->merge($distributors)
            ->sortBy('name')
            ->values();

        return view('admin.purchases.index', compact('invoices', 'allParties','debitNoteMap'));
    }
    // =========================================================
    //  CREATE
    // =========================================================

    public function create()
    {
        $invoiceNumber = $this->generateInvoiceNumber('gst');

        $vendors = Vendor::with(['addresses' => fn($q) => $q->where('is_default',true)])
                    ->where('status','active')->orderBy('company_name')->get();

        $dealers = Customer::with(['addresses' => fn($q) => $q->where('is_default',true)])
                    ->where('status','active')->where('party_type','dealer')->orderBy('name')->get();

        $distributors = Customer::with(['addresses' => fn($q) => $q->where('is_default',true)])
                    ->where('status','active')->where('party_type','distributor')->orderBy('name')->get();

        $purchaseExecutives = Salesman::purchaseExecutives()
                    ->where('status','active')->orderBy('name')->get(['_id','name']);

        $mainWarehouse = Warehouse::main()->first();
        $warehouses    = Warehouse::active()->get();
        $invoiceSetting = InvoiceSetting::first();
        $categories    = Category::where('status','active')->orderBy('name')->get();

        return view('admin.purchases.create', compact(
            'invoiceNumber','vendors','dealers','distributors',
            'mainWarehouse','warehouses','invoiceSetting',
            'purchaseExecutives','categories'
        ));
    }

    // =========================================================
    //  PARTIES LIST  (AJAX)
    // =========================================================

    public function getPartiesList(Request $request)
    {
        $type   = $request->party_type ?? 'all';
        $search = trim($request->search ?? '');
        $parties = collect();

        $buildSearch = function ($query, string $nameCol) use ($search) {
            if ($search) {
                $query->where(function ($q) use ($search, $nameCol) {
                    $q->where($nameCol,   'like', "%{$search}%")
                    ->orWhere('phone',  'like', "%{$search}%")
                    ->orWhere('email',  'like', "%{$search}%");
                });
            }
        };

        if (in_array($type, ['all','vendor'])) {
            $q = Vendor::where('status','active');
            $buildSearch($q, 'company_name');
            $parties = $parties->merge($q->get()->map(fn($v) => [
                'id'              => $v->id,
                'name'            => $v->company_name,
                'phone'           => $v->phone,
                'email'           => $v->email,
                'party_type'      => 'vendor',
                'party_type_text' => 'Vendor',
                'opening_balance' => (float)($v->opening_balance ?? 0), // Only vendors show opening balance
            ]));
        }

        foreach (['dealer','distributor'] as $pt) {
            if (!in_array($type, ['all', $pt])) continue;
            $q = Customer::where('status','active')->where('party_type',$pt);
            $buildSearch($q, 'name');
            $parties = $parties->merge($q->get()->map(fn($c) => [
                'id'              => (string)$c->_id,
                'name'            => $c->name,
                'phone'           => $c->phone,
                'email'           => $c->email,
                'party_type'      => $pt,
                'party_type_text' => ucfirst($pt),
                // No opening_balance for dealer/distributor
            ]));
        }

        return response()->json(['parties' => $parties->values()]);
    }

public function getPartyDetails($id, Request $request)
{
    $partyType = $request->get('type');

    if ($partyType === 'vendor') {
        $vendor = Vendor::with('addresses')->find($id);
        if (!$vendor) return response()->json(['success'=>false,'message'=>'Vendor not found'],404);

        $billing  = $vendor->addresses->where('type','billing')->where('is_default',true)->first();
        $shipping = $vendor->addresses->where('type','shipping')->where('is_default',true)->first();
        $pe = $vendor->purchase_executive_id ? Salesman::find($vendor->purchase_executive_id) : null;

        // ✅ Fix: Calculate total remaining amount from active debit notes
        $debitNotes = \App\Models\DebitNote::where('party_id', $id)
            ->whereIn('status', ['active', 'partial'])
            ->where('remaining_amount', '>', 0)
            ->get();

        $totalDebitRemaining = 0;
        foreach ($debitNotes as $dn) {
            // Convert Decimal128 to float
            $remaining = $dn->remaining_amount;
            if ($remaining instanceof \MongoDB\BSON\Decimal128) {
                $totalDebitRemaining += (float) $remaining->__toString();
            } else {
                $totalDebitRemaining += (float) $remaining;
            }
        }

        return response()->json(['success'=>true,'party'=>[
            'id'                      => $id,
            'name'                    => $vendor->company_name,
            'phone'                   => $vendor->phone,
            'email'                   => $vendor->email,
            'gst_number'              => $vendor->gst_number,
            'party_type'              => 'vendor',
            'party_type_text'         => 'Vendor',
            'purchase_executive_id'   => $vendor->purchase_executive_id,
            'purchase_executive_name' => $pe?->name,
            'billing_address'         => $billing?->full_address ?? '',
            'shipping_address'        => $shipping?->full_address ?? '',
            'billing_state'           => $billing?->state ?? '',
            'opening_balance'         => (float)($vendor->opening_balance ?? 0),
            'debit_notes_remaining'   => $totalDebitRemaining,
        ]]);
    }

    // Dealer or Distributor
    $customer = Customer::with('addresses')->find($id);
    if (!$customer) return response()->json(['success'=>false,'message'=>'Customer not found'],404);

    $billing  = $customer->addresses->where('type','billing')->where('is_default',true)->first();
    $shipping = $customer->addresses->where('type','shipping')->where('is_default',true)->first();

    // ✅ Fix: Calculate total remaining amount from active debit notes for customer
    $debitNotes = \App\Models\DebitNote::where('party_id', (string)$customer->_id)
        ->whereIn('status', ['active', 'partial'])
        ->where('remaining_amount', '>', 0)
        ->get();

    $totalDebitRemaining = 0;
    foreach ($debitNotes as $dn) {
        // Convert Decimal128 to float
        $remaining = $dn->remaining_amount;
        if ($remaining instanceof \MongoDB\BSON\Decimal128) {
            $totalDebitRemaining += (float) $remaining->__toString();
        } else {
            $totalDebitRemaining += (float) $remaining;
        }
    }

    return response()->json(['success'=>true,'party'=>[
        'id'                      => $id,
        'name'                    => $customer->name,
        'phone'                   => $customer->phone,
        'email'                   => $customer->email,
        'gst_number'              => $customer->gst_number,
        'party_type'              => $customer->party_type,
        'party_type_text'         => ucfirst($customer->party_type),
        'purchase_executive_id'   => null,
        'purchase_executive_name' => null,
        'billing_address'         => $billing?->full_address ?? '',
        'shipping_address'        => $shipping?->full_address ?? '',
        'billing_state'           => $billing?->state ?? '',
        'debit_notes_remaining'   => $totalDebitRemaining,
    ]]);
}

    public function storePartyAjax(Request $request)
    {
        $isVendor = $request->party_type === 'vendor';

        $rules = [
            'name'            => 'required|string|max:255',
            'party_type'      => 'required|in:vendor,dealer,distributor',
            'gst_number'      => 'nullable|string|max:15',
            'billing_address' => 'required|string',
            'billing_city'    => 'required|string',
            'billing_state'   => 'required|string',
            'billing_pincode' => 'required|digits:6',
            'opening_balance' => 'nullable|numeric|min:0',
        ];
        $rules['phone'] = $isVendor
            ? 'required|digits:10|unique:vendors,phone'
            : 'required|digits:10|unique:customers,phone';
        $rules['email'] = $isVendor
            ? 'nullable|email|unique:vendors,email'
            : 'nullable|email|unique:customers,email';
        if ($isVendor) {
            $rules['purchase_executive_id'] = 'nullable|exists:salesmen,_id';
        }

        $v = Validator::make($request->all(), $rules);
        if ($v->fails()) return response()->json(['success'=>false,'message'=>$v->errors()->first()],422);

        try {
            if ($isVendor) {
                $party = Vendor::create([
                    'company_name'          => $request->name,
                    'name'                  => $request->name,
                    'phone'                 => $request->phone,
                    'email'                 => $request->email,
                    'gst_number'            => strtoupper($request->gst_number ?? ''),
                    'purchase_executive_id' => $request->purchase_executive_id,
                    'opening_balance'       => (float)($request->opening_balance ?? 0),
                    'notes'                 => $request->notes,
                    'status'                => 'active',
                ]);

                $this->saveVendorAddress($party->id, $request, 'billing');
                if ($request->same_billing_shipping == '1') {
                    $this->saveVendorAddress($party->id, $request, 'shipping', true);
                } elseif ($request->shipping_address) {
                    $this->saveVendorAddress($party->id, $request, 'shipping', false);
                } else {
                    $this->saveVendorAddress($party->id, $request, 'shipping', true);
                }
                $partyId = $party->id;

            } else {
                // Dealer / Distributor — no salesman_id, no purchase_executive_id here
                $party = Customer::create([
                    'name'            => $request->name,
                    'phone'           => $request->phone,
                    'email'           => $request->email,
                    'party_type'      => $request->party_type,
                    'gst_number'      => strtoupper($request->gst_number ?? ''),
                    'opening_balance' => (float)($request->opening_balance ?? 0),
                    'notes'           => $request->notes,
                    'status'          => 'active',
                ]);

                $this->saveCustomerAddress((string)$party->_id, $request, 'billing');
               if ($request->same_billing_shipping == '1') {
                    $this->saveCustomerAddress((string)$party->_id, $request, 'shipping', true);
                } elseif ($request->shipping_address) {
                    $this->saveCustomerAddress((string)$party->_id, $request, 'shipping', false);
                } else {
                    $this->saveCustomerAddress((string)$party->_id, $request, 'shipping', true);
                }
                $partyId = (string)$party->_id;
            }

            return response()->json([
                'success'    => true,
                'party_id'   => $partyId,
                'party_type' => $request->party_type,
                'message'    => ucfirst($request->party_type) . ' created successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json(['success'=>false,'message'=>'Error: '.$e->getMessage()],500);
        }
    }

    private function saveVendorAddress($vendorId, Request $req, string $type, bool $usesBilling = false): void
    {
        VendorAddress::create([
            'vendor_id'  => $vendorId,
            'type'       => $type,
            'address' => $usesBilling ? $req->billing_address : ($req->{"{$type}_address"} ?: $req->billing_address),
            'city'    => $usesBilling ? $req->billing_city    : ($req->{"{$type}_city"}    ?: $req->billing_city),
            'state'   => $usesBilling ? $req->billing_state   : ($req->{"{$type}_state"}   ?: $req->billing_state),
            'pincode' => $usesBilling ? $req->billing_pincode : ($req->{"{$type}_pincode"} ?: $req->billing_pincode),
            'country'    => $req->{"{$type}_country"} ?? $req->billing_country ?? 'India',
            'is_default' => true,
        ]);
    }

    private function saveCustomerAddress($customerId, Request $req, string $type, bool $usesBilling = false): void
    {
        CustomerAddress::create([
            'customer_id' => $customerId,
            'type'        => $type,
            'address' => $usesBilling ? $req->billing_address : ($req->{"{$type}_address"} ?: $req->billing_address),
            'city'    => $usesBilling ? $req->billing_city    : ($req->{"{$type}_city"}    ?: $req->billing_city),
            'state'   => $usesBilling ? $req->billing_state   : ($req->{"{$type}_state"}   ?: $req->billing_state),
            'pincode' => $usesBilling ? $req->billing_pincode : ($req->{"{$type}_pincode"} ?: $req->billing_pincode),
            'country'     => $req->{"{$type}_country"} ?? $req->billing_country ?? 'India',
            'is_default'  => true,
        ]);
    }

    // =========================================================
    //  GET WAREHOUSE PRODUCTS  (AJAX)
    // =========================================================

    public function getWarehouseProducts(Request $request)
    {
        try {
            $search = strtolower(trim($request->search ?? ''));

            $simple = SimpleProduct::where('status','active')->orderBy('name')->get()
                ->map(fn($p) => [
                    'id'             => (string)$p->_id,
                    'product_type'   => 'simple',
                    'name'           => $p->name,
                    'sku'            => $p->sku_code,
                    'mrp_price'      => (float)($p->mrp_price  ?? 0),
                    'purchase_price' => (float)($p->cost_price ?? 0),
                    'unit'           => $p->unit ?? 'PCS',
                    'hsn_code'       => $p->hsn_code,
                    'tax_percent'    => (float)($p->gst        ?? 0),
                    'variant_id'     => null,
                ]);

            $variants = collect();
            VariantProduct::where('status','active')->orderBy('name')->get()
                ->each(function($prod) use (&$variants) {
                    foreach ((array)$prod->variants as $v) {
                        $variants->push([
                            'id'             => (string)$prod->_id,
                            'product_type'   => 'variant',
                            'name'           => $v['name'] ?? $prod->name,
                            'sku'            => $v['sku_code'] ?? '',
                            'mrp_price'      => (float)($v['mrp_price']  ?? 0),
                            'purchase_price' => (float)($v['cost_price'] ?? 0),
                            'unit'           => $v['unit'] ?? 'PCS',
                            'hsn_code'       => $prod->hsn_code,
                            'tax_percent'    => (float)($prod->gst        ?? 0),
                            'variant_id'     => isset($v['_id']) ? (string)$v['_id'] : null,
                        ]);
                    }
                });

            $products = collect()->concat($simple)->concat($variants)->values();

            if ($search) {
                $products = $products->filter(fn($p) =>
                    str_contains(strtolower($p['name']), $search) ||
                    str_contains(strtolower($p['sku'] ?? ''), $search)
                )->values();
            }

            return response()->json(['products' => $products]);

        } catch (\Exception $e) {
            return response()->json(['error'=>true,'message'=>$e->getMessage()],500);
        }
    }

    // =========================================================
    //  QUICK ADD SIMPLE PRODUCT  (AJAX)
    // =========================================================

    public function quickAddSimpleProduct(Request $request)
    {
        $v = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'sku_code'      => 'required|string|max:16|unique:simple_products,sku_code',
            'cost_price'    => 'required|numeric|min:0',
            'mrp_price'     => 'required|numeric|min:0',
            'hsn_code'      => 'required|string|max:8',
            'gst'           => 'required|numeric|min:0|max:100',
            'unit'          => 'required|string',
            'opening_stock' => 'required|integer|min:0',
        ]);
        if ($v->fails()) return response()->json(['success'=>false,'message'=>$v->errors()->first()],422);

        try {
            $mainWh = Warehouse::main()->first();
            if (!$mainWh) return response()->json(['success'=>false,'message'=>'Main warehouse not found'],422);

            $pricing = PricingSetting::first();
            $mrp     = (float)$request->mrp_price;
            $dp      = $mrp - ($mrp * ($pricing->dealer_percentage     ?? 0) / 100);
            $distp   = $mrp - ($mrp * ($pricing->distributor_percentage ?? 0) / 100);

            $product = SimpleProduct::create([
                'type'               => 'simple',
                'name'               => $request->name,
                'category_id'        => $request->category_id,
                'brand'              => 'Simko',
                'body_type'          => $request->body_type ?? 'N/A',
                'status'             => 'active',
                'sku_code'           => $request->sku_code,
                'barcode'            => strtoupper(preg_replace('/[^A-Za-z0-9]/','', $request->sku_code)),
                'barcode_symbology'  => 'CODE128',
                'cost_price'         => (float)$request->cost_price,
                'sale_price'         => (float)($request->sale_price ?? $request->cost_price),
                'mrp_price'          => $mrp,
                'dealer_price'       => round($dp, 2),
                'distributor_price'  => round($distp, 2),
                'hsn_code'           => $request->hsn_code,
                'gst'                => (float)$request->gst,
                'unit'               => $request->unit,
                'description'        => $request->description,
            ]);

            $qty = (int)$request->opening_stock;
            WarehouseStock::create([
                'product_id'=>$product->_id,'product_type'=>'simple',
                'warehouse_id'=>$mainWh->id,'quantity'=>$qty,'min_stock_alert'=>0,
            ]);
            if ($qty > 0) {
                WarehouseMovement::create([
                    'product_id'=>$product->_id,'product_type'=>'simple',
                    'warehouse_id'=>$mainWh->id,'type'=>'opening','quantity'=>$qty,
                ]);
            }

            return response()->json(['success'=>true,'product'=>[
                'id'            =>(string)$product->_id,'product_type'=>'simple',
                'name'          =>$product->name,'sku'=>$product->sku_code,
                'mrp_price'     =>(float)$product->mrp_price,
                'purchase_price'=>(float)$product->cost_price,
                'unit'          =>$product->unit,'hsn_code'=>$product->hsn_code,
                'tax_percent'   =>(float)$product->gst,'variant_id'=>null,
            ],'message'=>'Product created successfully']);

        } catch (\Exception $e) {
            return response()->json(['success'=>false,'message'=>'Error: '.$e->getMessage()],500);
        }
    }

    // =========================================================
    //  STORE
    // =========================================================
    //
    //  IMPORTANT ACCOUNTING RULES (opening_balance is NEVER modified):
    //
    //  Vendor purchase   → We owe vendor more  → payable increases (+)
    //  Dealer/Distributor purchase from them → They owe us less / we record purchase transaction
    //                                          → their outstanding reduces (-)
    //
    //  Grand Total = pure invoice total (subtotal + tax + charges − discounts)
    //  Opening balance is just informational context; the ledger balance is
    //  calculated dynamically from opening_balance + all transactions.
    //
    //  We do NOT subtract opening_balance from grand_total here.
    // =========================================================

    public function store(Request $request)
    {
        if ($request->has('items') && is_string($request->items)) {
            $request->merge(['items' => json_decode($request->items, true)]);
        }
        if (empty($request->purchase_executive_id)) {
            $request->merge(['purchase_executive_id' => null]);
        }
        $v = Validator::make($request->all(), [
            'party_type'            => 'required|in:vendor,dealer,distributor',
            'vendor_id'             => 'required_if:party_type,vendor',
            'dealer_id'             => 'required_if:party_type,dealer',
            'distributor_id'        => 'required_if:party_type,distributor',
            'warehouse_id'          => 'required',
            'invoice_date'          => 'required|date',
            'invoice_type'          => 'required|in:gst,cash',
            'items'                 => 'required|array|min:1',
            'items.*.product_name'  => 'required|string',
            'items.*.quantity'      => 'required|numeric|min:0.01',
            'items.*.purchase_price'=> 'required|numeric|min:0',
            'purchase_executive_id' => 'nullable|exists:salesmen,_id',
        ]);
        if ($v->fails()) return response()->json(['success'=>false,'message'=>$v->errors()->first()],422);



        try {

            [$partyId, $billing, $shipping, $peId, $partyModel] = $this->resolvePartyFromRequest($request);

            $wh           = Warehouse::findOrFail($request->warehouse_id);
            $whState      = $wh->state ?? '';
            $partyState   = $billing?->state ?? '';
            $isIntra      = $whState && $partyState && strtolower($whState) === strtolower($partyState);

            $totals = $this->calcItemTotals($request->items, $request->invoice_type, $isIntra);

            // Extra discount
            $discType   = $request->extra_discount_type ?? 'amount';
            $discValue  = (float)($request->extra_discount ?? 0);
            $discAmount = $discType === 'percent'
                ? $totals['subtotal'] * $discValue / 100
                : $discValue;

            $extraCharge = (float)($request->extra_charge ?? 0);
            $grandTotal  = ($totals['subtotal'] - $discAmount)
                         + ($request->invoice_type === 'gst' ? $totals['taxTotal'] : 0)
                         + $extraCharge;

            // Round off
            $roundOff = 0;
            if ($request->auto_round_off) {
                $rounded  = round($grandTotal);
                $roundOff = $rounded - $grandTotal;
                $grandTotal = $rounded;
            }

            $amountPaid = (float)($request->amount_paid ?? 0);
            $balance    = $grandTotal - $amountPaid;
            $payStatus  = $this->resolvePaymentStatus($amountPaid, $balance);
            if ($payStatus === 'paid') $balance = 0;
            $partyName = '';

            if ($request->party_type === 'vendor') {
                $partyName = $partyModel->company_name;
            } else {
                $partyName = $partyModel->name;
            }
            $invoice = PurchaseInvoice::create([
                'invoice_number' => $this->generateInvoiceNumber($request->invoice_type ?? 'gst'),
                'public_token'          => Str::random(40),
                'invoice_type'          => $request->invoice_type,
                'invoice_date'          => $request->invoice_date,
                // party_id stores the party ID regardless of type (vendor, dealer, distributor)
                // Party type is NOT stored — it is derived by looking up the ID
                'party_id'              => $partyId,
                'party_type'            => $request->party_type,
                'party_name'            => $partyName,
                'purchase_executive_id' => $peId,
                'warehouse_id'          => $request->warehouse_id,
                'billing_address'       => $billing?->full_address,
                'shipping_address'      => $shipping?->full_address,
                'payment_terms'         => $request->payment_terms,
                'due_date'              => $request->due_date,
                'po_number'             => $request->po_number,
                'total_mrp'             => round($totals['totalMRP'], 2),
                'subtotal'              => round($totals['subtotal'], 2),
                'discount_total'        => 0,
                'tax_total'             => round($totals['taxTotal'], 2),
                'cgst_total'            => round($totals['cgst'], 2),
                'sgst_total'            => round($totals['sgst'], 2),
                'igst_total'            => round($totals['igst'], 2),
                'tax_type'              => $isIntra ? 'intra' : 'inter',
                'extra_discount'        => round($discValue, 2),
                'extra_discount_type'   => $discType,
                'extra_charge'          => round($extraCharge, 2),
                'charge_name'           => $request->charge_name,
                'round_off'             => round($roundOff, 2),
                'grand_total'           => round($grandTotal, 2),
                'total_paid'            => round($amountPaid, 2),
                'balance_amount'        => round($balance, 2),
                'payment_status'        => $payStatus,
                'status'                => 'draft',
                'notes'                 => $request->notes,
                'created_by'            => Auth::guard('admin')->id(),
            ]);

            foreach ($request->items as $item) {
                $qty   = (float)$item['quantity'];
                $price = (float)$item['purchase_price'];
                $mrp   = (float)($item['mrp_price'] ?? $price);
                $taxPercent = (float)($item['tax_percent'] ?? 0);
                $discountPercent = (float)($item['discount'] ?? 0);

                // 1. subtotal
                $subtotal = $qty * $price;

                // 2. discount
                $discountAmount = ($subtotal * $discountPercent) / 100;

                // 3. taxable
                $taxable = $subtotal - $discountAmount;

                // 4. tax
                $taxAmount = ($taxable * $taxPercent) / 100;

                // 5. GST split
                $cgst = $isIntra ? $taxAmount / 2 : 0;
                $sgst = $isIntra ? $taxAmount / 2 : 0;
                $igst = !$isIntra ? $taxAmount : 0;

                // 6. final total
                $total = $taxable + $taxAmount;

                PurchaseInvoiceItem::create([
                    'purchase_invoice_id' => $invoice->id,
                    'product_id'          => $item['product_id'] ?? null,
                    'variant_id'          => $item['variant_id'] ?? null,
                    'product_name'        => $item['product_name'],
                    'sku'                 => $item['sku'] ?? '',
                    'hsn_sac'             => $item['hsn_sac'] ?? '',
                    'quantity'            => $qty,
                    'unit'                => $item['unit'] ?? 'PCS',
                    'mrp_price'           => round($mrp, 2),
                    'purchase_price'      => round($price, 2),

                    // ✅ important fields
                    'discount'            => round($discountAmount, 2),
                    'tax_percent'         => $taxPercent,
                    'tax_amount'          => round($taxAmount, 2),
                    'cgst_amount'         => round($cgst, 2),
                    'sgst_amount'         => round($sgst, 2),
                    'igst_amount'         => round($igst, 2),

                    // ✅ FINAL TOTAL
                    'total'               => round($total, 2),
                ]);
            }


            return response()->json(['success'=>true,'invoice_id'=>$invoice->id,'message'=>'Purchase invoice created successfully']);

        } catch (\Exception $e) {

            return response()->json(['success'=>false,'message'=>'Error: '.$e->getMessage()],500);
        }
    }

/**
     * Generate invoice (draft → confirmed + stock in)
     * PASTE THIS generate() method inside PurchaseInvoiceController
     * replacing the existing generate() method
     */
    public function generate($id)
    {
        try {
            $invoice = PurchaseInvoice::with('items')->findOrFail($id);

            if ($invoice->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice is already generated.',
                ], 400);
            }

            $cashPaid   = (float) ($invoice->total_paid ?? 0);
            $grandTotal = (float) $invoice->grand_total;

            /* ================= DEBIT NOTE AUTO-ADJUSTMENT ================= */
            $activeDebitNotes = \App\Models\DebitNote::where('party_id', $invoice->party_id)
                ->whereIn('status', ['active', 'partial'])
                ->where('remaining_amount', '>', 0)
                ->orderBy('debit_date', 'asc')
                ->orderBy('created_at', 'asc')
                ->get();

            $totalDebitApplied = 0;
            $appliedDNNumbers  = [];
            $remainingToAdjust = max(0, $grandTotal - $cashPaid);

            foreach ($activeDebitNotes as $dn) {
                if ($remainingToAdjust <= 0) break;

                $dnRemaining = (float) $dn->remaining_amount;
                $applyAmount = min($dnRemaining, $remainingToAdjust);

                $dn->used_amount      = (float) $dn->used_amount + $applyAmount;
                $dn->remaining_amount = $dnRemaining - $applyAmount;

                if ($dn->remaining_amount <= 0.001) {
                    $dn->remaining_amount = 0;
                    $dn->status           = 'settled';
                } else {
                    $dn->status = 'partial';
                }
                $dn->save();

                $totalDebitApplied  += $applyAmount;
                $remainingToAdjust  -= $applyAmount;
                $appliedDNNumbers[]  = $dn->debit_note_number;
            }

            /* ================= PAYMENT CALCULATION ================= */
            $amountPaid = $cashPaid + $totalDebitApplied;
            if ($amountPaid > $grandTotal) $amountPaid = $grandTotal;

            $balance = max(0, $grandTotal - $amountPaid);

            if ($amountPaid <= 0) {
                $paymentStatus = 'unpaid';
            } elseif ($balance <= 0.01) {
                $paymentStatus = 'paid';
                $balance       = 0;
            } else {
                $paymentStatus = 'partial';
            }

            // Update invoice payment fields
            $invoice->total_paid         = round($amountPaid, 2);
            $invoice->balance_amount     = round($balance, 2);
            $invoice->payment_status     = $paymentStatus;
            $invoice->debit_note_applied = round($totalDebitApplied, 2);

            if (!empty($appliedDNNumbers)) {
                $invoice->debit_note_numbers = implode(', ', $appliedDNNumbers);
            }
            $invoice->save();

            /* ================= STOCK ADDITION ================= */
            foreach ($invoice->items as $item) {
                if (!$item->product_id) continue;

                $isVariant = (bool) $item->variant_id;
                $stockQ    = WarehouseStock::where('warehouse_id', $invoice->warehouse_id)
                    ->where('product_id', $item->product_id)
                    ->where('product_type', $isVariant ? 'variant' : 'simple');
                if ($isVariant) $stockQ->where('variant_id', $item->variant_id);

                $stock = $stockQ->first();
                if ($stock) {
                    $stock->update(['quantity' => $stock->quantity + $item->quantity]);
                } else {
                    WarehouseStock::create([
                        'warehouse_id'    => $invoice->warehouse_id,
                        'product_id'      => $item->product_id,
                        'variant_id'      => $item->variant_id,
                        'product_type'    => $isVariant ? 'variant' : 'simple',
                        'quantity'        => $item->quantity,
                        'min_stock_alert' => 0,
                    ]);
                }

                WarehouseMovement::create([
                    'warehouse_id' => $invoice->warehouse_id,
                    'product_id'   => $item->product_id,
                    'variant_id'   => $item->variant_id,
                    'product_type' => $isVariant ? 'variant' : 'simple',
                    'type'         => 'purchase',
                    'quantity'     => $item->quantity,
                    'reference_id' => $invoice->id,
                    'remarks'      => "Purchase Invoice: {$invoice->invoice_number}",
                ]);
            }

            /* ================= INVOICE STATUS ================= */
            $invoice->update(['status' => 'confirmed']);

            /* ================= CREATE PAYMENT RECORD ================= */
            // ✅ FIX: payment_type, payment_subtype, allocations — sabhi fields set karo
            if ($amountPaid > 0) {
                $notes       = [];
                $allocations = [];

                // Cash paid allocation (invoice ke against)
                if ($cashPaid > 0) {
                    $notes[] = "Cash paid: ₹" . number_format($cashPaid, 2);
                    $allocations[] = [
                        'type'           => 'invoice',
                        'invoice_id'     => (string) $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'amount'         => round($cashPaid, 2),
                        'description'    => 'Invoice payment at time of generation',
                    ];
                }

                // Debit note allocation
                if ($totalDebitApplied > 0) {
                    $notes[] = "Debit note adjusted: ₹" . number_format($totalDebitApplied, 2)
                             . " (" . implode(', ', $appliedDNNumbers) . ")";
                    foreach ($appliedDNNumbers as $dnNum) {
                        $allocations[] = [
                            'type'              => 'debit_note',
                            'debit_note_number' => $dnNum,
                            'amount'            => round($totalDebitApplied / count($appliedDNNumbers), 2),
                            'description'       => 'Debit Note Adjustment: ' . $dnNum,
                        ];
                    }
                }

                PurchasePayment::create([
                    'payment_number'      => null,               // invoice-time payment has no payment_number
                    'purchase_invoice_id' => $invoice->id,
                    'party_id'            => $invoice->party_id,
                    'amount'              => round($amountPaid, 2),
                    'payment_method'      => request()->payment_method ?? 'cash',
                    'payment_date'        => $invoice->invoice_date,
                    'status'              => 'completed',
                    'notes'               => implode("\n", $notes),
                    'payment_type'        => 'payment_out',      // ✅ FIXED
                    'payment_subtype'     => 'purchase_payment', // ✅ FIXED
                    'allocations'         => $allocations,       // ✅ FIXED
                    'created_by'          => Auth::guard('admin')->id(),
                ]);
            }

            $msg = 'Invoice generated successfully. Stock added to warehouse.';
            if ($totalDebitApplied > 0) {
                $msg .= ' Debit note adjusted: ₹' . number_format($totalDebitApplied, 2)
                      . ' (' . implode(', ', $appliedDNNumbers) . ')';
            }

            return response()->json([
                'success'            => true,
                'message'            => $msg,
                'debit_note_applied' => $totalDebitApplied,
                'debit_note_numbers' => $appliedDNNumbers,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate invoice: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================
    //  CANCEL  (confirmed → cancelled + stock out)
    // =========================================================

    public function cancel($id)
    {
        try {

            $invoice = PurchaseInvoice::with('items')->findOrFail($id);
            if ($invoice->status !== 'confirmed') {
                return response()->json(['success'=>false,'message'=>'Only confirmed invoices can be cancelled.'],400);
            }

            if ($invoice->payment_status === 'paid') {
                return response()->json(['success'=>false,'message'=>'Paid invoices cannot be cancelled. Please process a return instead.'],400);
            }

            if ($invoice->total_paid > 0) {
                return response()->json(['success'=>false,'message'=>'This invoice has received payment. Please process a return instead.'],400);
            }

            foreach ($invoice->items as $item) {
                if (!$item->product_id) continue;

                $isVariant = (bool)$item->variant_id;
                $stockQ = WarehouseStock::where('warehouse_id',$invoice->warehouse_id)
                    ->where('product_id',$item->product_id)
                    ->where('product_type', $isVariant ? 'variant' : 'simple');
                if ($isVariant) $stockQ->where('variant_id',$item->variant_id);

                $stock = $stockQ->first();
                if ($stock) {
                    $stock->update(['quantity' => max(0, $stock->quantity - $item->quantity)]);
                    WarehouseMovement::create([
                        'warehouse_id' => $invoice->warehouse_id,
                        'product_id'   => $item->product_id,
                        'variant_id'   => $item->variant_id,
                        'product_type' => $isVariant ? 'variant' : 'simple',
                        'type'         => 'cancellation',
                        'quantity'     => -$item->quantity,
                        'reference_id' => $invoice->id,
                        'remarks'      => "Purchase Invoice Cancelled: {$invoice->invoice_number}",
                    ]);
                }
            }

            $invoice->update([
                'status' => 'cancelled',
                'notes'  => ($invoice->notes ? $invoice->notes."\n" : '').
                            "[Cancelled on ".now()->format('d/m/Y H:i')."]",
            ]);

            return response()->json(['success'=>true,'message'=>'Invoice cancelled. Stock reversed.']);

        } catch (\Exception $e) {
            return response()->json(['success'=>false,'message'=>$e->getMessage()],500);
        }
    }

    // =========================================================
    //  SHOW
    // =========================================================

    public function show($id)
    {
        $invoice = PurchaseInvoice::with(['warehouse','purchaseExecutive','items'])->findOrFail($id);

        // Resolve party without relying on a stored party_type field
        $partyInfo = $this->resolvePartyFromInvoice($invoice);

        // Dynamic ledger balance (opening_balance is NEVER modified)
        $ledgerBalance = $this->calcLedgerBalance($partyInfo['type'], $partyInfo['model'], $invoice);

        return view('admin.purchases.show', compact('invoice','partyInfo','ledgerBalance'));
    }

    /**
     * Dynamically calculate the running balance for the party:
     *
     * Vendor:
     *   payable = opening_balance + Σ confirmed purchase invoices − Σ payments
     *
     * Dealer / Distributor:
     *   Their ledger context from the purchase side:
     *   The purchase is money WE pay THEM (or record as purchase).
     *   outstanding = opening_balance − Σ confirmed purchase invoice totals − Σ purchase payments
     *   (opening_balance for dealer/distributor in purchases means how much they owed us initially
     *    or an advance they gave — business context defines sign; we expose both figures.)
     *
     * We return an array so the view can display whatever is relevant.
     */
    private function calcLedgerBalance(string $partyType, $partyModel, PurchaseInvoice $currentInvoice): array
    {
        if (!$partyModel) return [];

        $partyId        = $partyType === 'vendor' ? $partyModel->id : (string)$partyModel->_id;
        $openingBalance = (float)($partyModel->opening_balance ?? 0);

        // Sum of all CONFIRMED purchase invoices for this party
        $totalPurchased = PurchaseInvoice::where('party_id', $partyId)
            ->where('status', 'confirmed')
            ->sum('grand_total');

        // Sum of all payments made against this party's purchase invoices
        // (requires PurchasePayment model — if not available falls back to total_paid sums)
        $totalPaid = PurchaseInvoice::where('party_id', $partyId)
            ->where('status', 'confirmed')
            ->sum('total_paid');

        if ($partyType === 'vendor') {
            // We OWE the vendor: opening + purchases − payments
            $balance = $openingBalance + $totalPurchased - $totalPaid;
            return [
                'label'           => 'Payable to Vendor',
                'opening_balance' => $openingBalance,
                'total_purchased' => $totalPurchased,
                'total_paid'      => $totalPaid,
                'balance'         => $balance,
                'balance_label'   => $balance >= 0 ? 'Payable' : 'Advance Paid',
            ];

        } else {
            // Dealer / Distributor: purchase means we buy FROM them.
            // Their balance reduces when we pay them (total_paid).
            // opening_balance = initial amount they may owe us, or we owe them.
            $balance = $openingBalance - $totalPaid; // how much of opening is still outstanding
            return [
                'label'           => ucfirst($partyType) . ' Ledger',
                'opening_balance' => $openingBalance,
                'total_purchased' => $totalPurchased,  // value of goods we've received from them
                'total_paid'      => $totalPaid,
                'balance'         => $balance,
                'balance_label'   => $balance >= 0 ? 'Outstanding' : 'Advance',
            ];
        }
    }

    // =========================================================
    //  EDIT
    // =========================================================

    public function edit($id)
    {
        $invoice = PurchaseInvoice::with(['items'])->findOrFail($id);
        if ($invoice->status !== 'draft') {
            return redirect()->route('admin.purchases.show',$id)->with('error','Only draft invoices can be edited.');
        }

        $vendors = Vendor::with(['addresses'=>fn($q)=>$q->where('is_default',true)])
            ->where('status','active')->orderBy('company_name')->get();
        $dealers = Customer::with(['addresses'=>fn($q)=>$q->where('is_default',true)])
            ->where('status','active')->where('party_type','dealer')->orderBy('name')->get();
        $distributors = Customer::with(['addresses'=>fn($q)=>$q->where('is_default',true)])
            ->where('status','active')->where('party_type','distributor')->orderBy('name')->get();
        $purchaseExecutives = Salesman::purchaseExecutives()->where('status','active')->orderBy('name')->get(['_id','name']);
        $mainWarehouse  = Warehouse::main()->first();
        $warehouses     = Warehouse::active()->get();
        $invoiceSetting = InvoiceSetting::first();
        $categories     = Category::where('status','active')->orderBy('name')->get();

        // Resolve current party for pre-population
        $partyInfo = $this->resolvePartyFromInvoice($invoice);

        return view('admin.purchases.edit', compact(
            'invoice','vendors','dealers','distributors',
            'mainWarehouse','warehouses','invoiceSetting',
            'purchaseExecutives','categories','partyInfo'
        ));
    }

    // =========================================================
    //  UPDATE
    // =========================================================

    public function update(Request $request, $id)
    {
        $invoice = PurchaseInvoice::findOrFail($id);
        if ($invoice->status !== 'draft') {
            return response()->json(['success'=>false,'message'=>'Only draft invoices can be updated.'],403);
        }

        if ($request->has('items') && is_string($request->items)) {
            $request->merge(['items' => json_decode($request->items, true)]);
        }

        $v = Validator::make($request->all(), [
            'party_type'            => 'required|in:vendor,dealer,distributor',
            'vendor_id'             => 'required_if:party_type,vendor',
            'dealer_id'             => 'required_if:party_type,dealer',
            'distributor_id'        => 'required_if:party_type,distributor',
            'warehouse_id'          => 'required',
            'invoice_date'          => 'required|date',
            'invoice_type'          => 'required|in:gst,cash',
            'items'                 => 'required|array|min:1',
            'items.*.product_name'  => 'required|string',
            'items.*.quantity'      => 'required|numeric|min:0.01',
            'items.*.purchase_price'=> 'required|numeric|min:0',
        ]);
        if ($v->fails()) return response()->json(['success'=>false,'message'=>$v->errors()->first()],422);

        try {
            [$partyId, $billing, $shipping, $peId, $partyModel] = $this->resolvePartyFromRequest($request);

            $wh       = Warehouse::findOrFail($request->warehouse_id);
            $isIntra  = $wh->state && ($billing?->state)
                && strtolower($wh->state) === strtolower($billing->state);

            $totals    = $this->calcItemTotals($request->items, $request->invoice_type, $isIntra);
            $discType  = $request->extra_discount_type ?? 'amount';
            $discValue = (float)($request->extra_discount ?? 0);
            $discAmt   = $discType === 'percent' ? $totals['subtotal'] * $discValue / 100 : $discValue;
            $extraChg  = (float)($request->extra_charge ?? 0);
            $grand     = ($totals['subtotal'] - $discAmt)
                       + ($request->invoice_type === 'gst' ? $totals['taxTotal'] : 0)
                       + $extraChg;

            $roundOff = 0;
            if ($request->auto_round_off) { $rounded = round($grand); $roundOff = $rounded - $grand; $grand = $rounded; }

            $paid    = (float)($request->amount_paid ?? 0);
            $balance = $grand - $paid;
            $payStatus = $this->resolvePaymentStatus($paid, $balance);
            if ($payStatus === 'paid') $balance = 0;
            $partyName = '';

            if ($request->party_type === 'vendor') {
                $partyName = $partyModel->company_name;
            } else {
                $partyName = $partyModel->name;
            }
            PurchaseInvoiceItem::where('purchase_invoice_id',$id)->delete();

            $invoice->update([
                'invoice_type'          => $request->invoice_type,
                'invoice_date'          => $request->invoice_date,
                'party_id'              => $partyId, // party_type NOT stored — derived on load
                'party_type'             => $request->party_type,
                'party_name' => $partyName,
                'purchase_executive_id' => $peId,
                'warehouse_id'          => $request->warehouse_id,
                'billing_address'       => $billing?->full_address,
                'shipping_address'      => $shipping?->full_address,
                'payment_terms'         => $request->payment_terms,
                'due_date'              => $request->due_date,
                'po_number'             => $request->po_number,
                'total_mrp'             => round($totals['totalMRP'],2),
                'subtotal'              => round($totals['subtotal'],2),
                'tax_total'             => round($totals['taxTotal'],2),
                'cgst_total'            => round($totals['cgst'],2),
                'sgst_total'            => round($totals['sgst'],2),
                'igst_total'            => round($totals['igst'],2),
                'tax_type'              => $isIntra ? 'intra' : 'inter',
                'extra_discount'        => round($discValue,2),
                'extra_discount_type'   => $discType,
                'extra_charge'          => round($extraChg,2),
                'charge_name'           => $request->charge_name,
                'round_off'             => round($roundOff,2),
                'grand_total'           => round($grand,2),
                'total_paid'            => round($paid,2),
                'balance_amount'        => round($balance,2),
                'payment_status'        => $payStatus,
                'notes'                 => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $qty   = (float)$item['quantity'];
                $price = (float)$item['purchase_price'];
                $mrp   = (float)($item['mrp_price'] ?? $price);
                $taxPercent = (float)($item['tax_percent'] ?? 0);
                $discountPercent = (float)($item['discount'] ?? 0);

                // 1. subtotal
                $subtotal = $qty * $price;

                // 2. discount
                $discountAmount = ($subtotal * $discountPercent) / 100;

                // 3. taxable
                $taxable = $subtotal - $discountAmount;

                // 4. tax
                $taxAmount = ($taxable * $taxPercent) / 100;

                // 5. GST split
                $cgst = $isIntra ? $taxAmount / 2 : 0;
                $sgst = $isIntra ? $taxAmount / 2 : 0;
                $igst = !$isIntra ? $taxAmount : 0;

                // 6. final total
                $total = $taxable + $taxAmount;

                PurchaseInvoiceItem::create([
                    'purchase_invoice_id' => $invoice->id,
                    'product_id'          => $item['product_id'] ?? null,
                    'variant_id'          => $item['variant_id'] ?? null,
                    'product_name'        => $item['product_name'],
                    'sku'                 => $item['sku'] ?? '',
                    'hsn_sac'             => $item['hsn_sac'] ?? '',
                    'quantity'            => $qty,
                    'unit'                => $item['unit'] ?? 'PCS',
                    'mrp_price'           => round($mrp, 2),
                    'purchase_price'      => round($price, 2),

                    // ✅ important fields
                    'discount'            => round($discountAmount, 2),
                    'tax_percent'         => $taxPercent,
                    'tax_amount'          => round($taxAmount, 2),
                    'cgst_amount'         => round($cgst, 2),
                    'sgst_amount'         => round($sgst, 2),
                    'igst_amount'         => round($igst, 2),

                    // ✅ FINAL TOTAL
                    'total'               => round($total, 2),
                ]);
            }


            return response()->json(['success'=>true,'invoice_id'=>$invoice->id,'message'=>'Invoice updated successfully']);

        } catch (\Exception $e) {

            return response()->json(['success'=>false,'message'=>'Error: '.$e->getMessage()],500);
        }
    }

    // =========================================================
    //  DESTROY
    // =========================================================

    public function destroy($id)
    {
        try {
            $invoice = PurchaseInvoice::with('items')->findOrFail($id);
            if ($invoice->status !== 'draft') {
                return response()->json(['success'=>false,'message'=>'Only draft invoices can be deleted.'],403);
            }
            PurchaseInvoiceItem::where('purchase_invoice_id',$id)->delete();
            $invoice->delete();
            return response()->json(['success'=>true,'message'=>'Invoice deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success'=>false,'message'=>'Failed: '.$e->getMessage()],500);
        }
    }
    private function decimalToFloat($value)
    {
        if ($value instanceof \MongoDB\BSON\Decimal128) {
            return (float) $value->__toString();
        }
        return (float) $value;
    }
    public function getNextInvoiceNumber(Request $request)
    {
        $type = $request->invoice_type ?? 'gst';
        return response()->json([
            'invoice_number' => $this->generateInvoiceNumber($type)
        ]);
    }
}
