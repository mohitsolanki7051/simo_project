<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\SimpleProduct;
use App\Models\VariantProduct;
use App\Models\WarehouseStock;
use App\Models\WarehouseMovement;
use App\Http\Controllers\Admin\LedgerController;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }

    public function outstanding(Request $request)
    {
        $customers = Customer::where('status', 'active')->get();
        $vendors = Vendor::where('status', 'active')->get();
        
        $toCollect = [];
        $toPay = [];
        
        $totalToCollect = 0;
        $totalToPay = 0;

        foreach ($customers as $c) {
            $balance = LedgerController::getClosingBalance($c->party_type, $c->id);
            $addrObj = $c->addresses()->where('type', 'billing')->first() 
                    ?? $c->addresses()->first();
            $addressStr = $addrObj ? $addrObj->full_address : 'N/A';

            if ($balance > 0) {
                $toCollect[] = [
                    'id' => $c->id,
                    'name' => $c->name,
                    'phone' => $c->phone ?? 'N/A',
                    'address' => $addressStr,
                    'party_type' => ucfirst($c->party_type),
                    'closing_balance' => $balance
                ];
                $totalToCollect += $balance;
            } elseif ($balance < 0) {
                $toPay[] = [
                    'id' => $c->id,
                    'name' => $c->name,
                    'phone' => $c->phone ?? 'N/A',
                    'address' => $addressStr,
                    'party_type' => ucfirst($c->party_type),
                    'closing_balance' => abs($balance)
                ];
                $totalToPay += abs($balance);
            }
        }

        foreach ($vendors as $v) {
            $balance = LedgerController::getClosingBalance('vendor', $v->id);
            $addrObj = $v->addresses()->where('type', 'billing')->first() 
                    ?? $v->addresses()->first();
            $addressStr = $addrObj ? $addrObj->full_address : 'N/A';

            if ($balance > 0) {
                $toCollect[] = [
                    'id' => $v->id,
                    'name' => $v->display_name ?? $v->name,
                    'phone' => $v->phone ?? 'N/A',
                    'address' => $addressStr,
                    'party_type' => 'Vendor',
                    'closing_balance' => $balance
                ];
                $totalToCollect += $balance;
            } elseif ($balance < 0) {
                $toPay[] = [
                    'id' => $v->id,
                    'name' => $v->display_name ?? $v->name,
                    'phone' => $v->phone ?? 'N/A',
                    'address' => $addressStr,
                    'party_type' => 'Vendor',
                    'closing_balance' => abs($balance)
                ];
                $totalToPay += abs($balance);
            }
        }

        usort($toCollect, fn($a, $b) => $b['closing_balance'] <=> $a['closing_balance']);
        usort($toPay, fn($a, $b) => $b['closing_balance'] <=> $a['closing_balance']);

        return view('admin.reports.outstanding', compact('toCollect', 'toPay', 'totalToCollect', 'totalToPay'));
    }

    public function outstandingPdf(Request $request)
    {
        $mode = $request->get('mode', 'collect');

        $customers = Customer::where('status', 'active')->get();
        $vendors = Vendor::where('status', 'active')->get();
        
        $outstandingParties = [];
        $totalOutstanding = 0;

        if ($mode === 'pay') {
            foreach ($customers as $c) {
                $balance = LedgerController::getClosingBalance($c->party_type, $c->id);
                if ($balance < 0) {
                    $addrObj = $c->addresses()->where('type', 'billing')->first() 
                            ?? $c->addresses()->first();
                    $addressStr = $addrObj ? $addrObj->full_address : 'N/A';

                    $outstandingParties[] = [
                        'name' => $c->name,
                        'phone' => $c->phone ?? 'N/A',
                        'address' => $addressStr,
                        'party_type' => ucfirst($c->party_type),
                        'closing_balance' => abs($balance)
                    ];
                    $totalOutstanding += abs($balance);
                }
            }
            foreach ($vendors as $v) {
                $balance = LedgerController::getClosingBalance('vendor', $v->id);
                if ($balance < 0) {
                    $addrObj = $v->addresses()->where('type', 'billing')->first() 
                            ?? $v->addresses()->first();
                    $addressStr = $addrObj ? $addrObj->full_address : 'N/A';

                    $outstandingParties[] = [
                        'name' => $v->display_name ?? $v->name,
                        'phone' => $v->phone ?? 'N/A',
                        'address' => $addressStr,
                        'party_type' => 'Vendor',
                        'closing_balance' => abs($balance)
                    ];
                    $totalOutstanding += abs($balance);
                }
            }
        } else {
            foreach ($customers as $c) {
                $balance = LedgerController::getClosingBalance($c->party_type, $c->id);
                if ($balance > 0) {
                    $addrObj = $c->addresses()->where('type', 'billing')->first() 
                            ?? $c->addresses()->first();
                    $addressStr = $addrObj ? $addrObj->full_address : 'N/A';

                    $outstandingParties[] = [
                        'name' => $c->name,
                        'phone' => $c->phone ?? 'N/A',
                        'address' => $addressStr,
                        'party_type' => ucfirst($c->party_type),
                        'closing_balance' => $balance
                    ];
                    $totalOutstanding += $balance;
                }
            }
            foreach ($vendors as $v) {
                $balance = LedgerController::getClosingBalance('vendor', $v->id);
                if ($balance > 0) {
                    $addrObj = $v->addresses()->where('type', 'billing')->first() 
                            ?? $v->addresses()->first();
                    $addressStr = $addrObj ? $addrObj->full_address : 'N/A';

                    $outstandingParties[] = [
                        'name' => $v->display_name ?? $v->name,
                        'phone' => $v->phone ?? 'N/A',
                        'address' => $addressStr,
                        'party_type' => 'Vendor',
                        'closing_balance' => $balance
                    ];
                    $totalOutstanding += $balance;
                }
            }
        }

        usort($outstandingParties, fn($a, $b) => $b['closing_balance'] <=> $a['closing_balance']);

        $settings = \App\Models\InvoiceSetting::first();

        return view('admin.reports.outstanding-pdf', compact('outstandingParties', 'totalOutstanding', 'mode', 'settings'));
    }

    public function stockValuation(Request $request)
    {
        $targetDate = $request->get('date');
        $dateStr = null;
        $carbonDate = null;
        if ($targetDate) {
            try {
                $carbonDate = Carbon::parse($targetDate);
                $dateStr = $carbonDate->format('Y-m-d');
            } catch (\Exception $ex) {
                $targetDate = null;
            }
        }

        $simpleProducts = SimpleProduct::where('status', 'active')->get();
        $variantProducts = VariantProduct::where('status', 'active')->get();

        $totalCost = 0;
        $products = [];

        foreach ($simpleProducts as $product) {
            $stock = $product->current_stock ?? 0;
            $costPrice = $product->cost_price ?? 0;

            if ($targetDate) {
                $movementsQty = WarehouseMovement::where('product_id', $product->id)
                    ->where('created_at', '>', $carbonDate->endOfDay())
                    ->sum('quantity');
                $stock = max(0, $stock - $movementsQty);
                $costPrice = $product->getCostPriceAtDate($dateStr);
            }

            $productCost = $stock * $costPrice;
            $totalCost += $productCost;

            $products[] = [
                'name' => $product->name,
                'sku' => $product->sku_code ?? 'N/A',
                'type' => 'Simple',
                'stock' => $stock,
                'unit' => $product->unit ?? 'PCS',
                'cost_price' => $costPrice,
                'total_cost' => $productCost,
            ];
        }

        foreach ($variantProducts as $product) {
            if ($product->variants && is_array($product->variants)) {
                foreach ($product->variants as $index => $variant) {
                    $stock = $variant['current_stock'] ?? 0;
                    $costPrice = $variant['cost_price'] ?? 0;
                    $variantId = $variant['_id'] ?? null;

                    if ($targetDate && $variantId) {
                        $movementsQty = WarehouseMovement::where('product_id', $product->id)
                            ->where('variant_id', (string)$variantId)
                            ->where('created_at', '>', $carbonDate->endOfDay())
                            ->sum('quantity');
                        $stock = max(0, $stock - $movementsQty);
                        $costPrice = $product->getVariantCostPriceAtDate($variantId, $dateStr);
                    }

                    $variantCost = $stock * $costPrice;
                    $totalCost += $variantCost;

                    $products[] = [
                        'name' => $product->name . ' - ' . ($variant['name'] ?? 'Variant ' . ($index + 1)),
                        'sku' => $variant['sku_code'] ?? 'N/A',
                        'type' => 'Variant',
                        'stock' => $stock,
                        'unit' => $variant['unit'] ?? 'PCS',
                        'cost_price' => $costPrice,
                        'total_cost' => $variantCost,
                    ];
                }
            }
        }

        return view('admin.reports.stock-valuation', compact('products', 'totalCost', 'targetDate'));
    }

    public function showMockReport(string $reportKey)
    {
        $reportsConfig = [
            'party-report-by-item' => [
                'title' => 'Party Report by Item',
                'headers' => ['Party Name', 'Item Name', 'Quantity Sold', 'Average Price', 'Total Sales'],
                'data' => [
                    ['Ram Prasad', 'Penal Light 12W', '45 PCS', '₹ 280.00', '₹ 12,600.00'],
                    ['Shyam Sunder', 'LED Bulb 9W', '120 PCS', '₹ 75.00', '₹ 9,000.00'],
                    ['Krishna Traders', 'Modular Switch 6A', '350 PCS', '₹ 22.00', '₹ 7,700.00'],
                    ['Vijay Electronics', 'Copper Wire 1.5mm', '10 Coils', '₹ 1,150.00', '₹ 11,500.00'],
                    ['Mohan Lal', 'PVC Tape Black', '80 Rolls', '₹ 12.00', '₹ 960.00'],
                ]
            ],
            'item-report-by-party' => [
                'title' => 'Item Report by Party',
                'headers' => ['Item Name', 'Purchased By', 'Total Quantity', 'Last Purchased Date', 'Gross Value'],
                'data' => [
                    ['Penal Light 12W', 'Ram Prasad', '45 PCS', '2026-07-01', '₹ 12,600.00'],
                    ['LED Bulb 9W', 'Shyam Sunder', '120 PCS', '2026-06-28', '₹ 9,000.00'],
                    ['Modular Switch 6A', 'Krishna Traders', '350 PCS', '2026-07-05', '₹ 7,700.00'],
                    ['Copper Wire 1.5mm', 'Vijay Electronics', '10 Coils', '2026-07-02', '₹ 11,500.00'],
                    ['PVC Tape Black', 'Mohan Lal', '80 Rolls', '2026-07-08', '₹ 960.00'],
                ]
            ],
            'rate-list' => [
                'title' => 'Rate List',
                'headers' => ['Product Name', 'SKU', 'MRP Price', 'Sales Price', 'Dealer Price', 'Distributor Price'],
                'data' => [
                    ['Penal Light 12W', 'SIM-PL-12W', '₹ 700.00', '₹ 400.00', '₹ 320.00', '₹ 280.00'],
                    ['LED Bulb 9W', 'SIM-LED-9W', '₹ 180.00', '₹ 90.00', '₹ 80.00', '₹ 72.00'],
                    ['Modular Switch 6A', 'SIM-MS-6A', '₹ 45.00', '₹ 25.00', '₹ 22.00', '₹ 19.50'],
                    ['Copper Wire 1.5mm', 'SIM-CW-1.5', '₹ 2,200.00', '₹ 1,450.00', '₹ 1,300.00', '₹ 1,150.00'],
                    ['PVC Tape Black', 'SIM-PT-BLK', '₹ 25.00', '₹ 15.00', '₹ 13.50', '₹ 12.00'],
                ]
            ],
            'item-sale-summary' => [
                'title' => 'Item Sale Summary',
                'headers' => ['Product Name', 'Quantity Sold', 'MRP Revenue', 'Discount Allowed', 'Net Revenue', 'Avg. Margin'],
                'data' => [
                    ['Penal Light 12W', '125 PCS', '₹ 87,500', '₹ 37,500', '₹ 50,000', '35%'],
                    ['LED Bulb 9W', '340 PCS', '₹ 61,200', '₹ 30,600', '₹ 30,600', '28%'],
                    ['Modular Switch 6A', '1,200 PCS', '₹ 54,000', '₹ 24,000', '₹ 30,000', '42%'],
                    ['Copper Wire 1.5mm', '35 Coils', '₹ 77,000', '₹ 26,250', '₹ 50,750', '22%'],
                    ['PVC Tape Black', '450 Rolls', '₹ 11,250', '₹ 4,500', '₹ 6,750', '50%'],
                ]
            ],
            'item-timeline' => [
                'title' => 'Item Timeline',
                'headers' => ['Date & Time', 'Product Name', 'Action / Event', 'Warehouse', 'Quantity Change', 'Reference ID'],
                'data' => [
                    ['2026-07-09 16:30', 'Penal Light 12W', 'Sale Invoice Confirmed', 'Main Warehouse', '-5 PCS', 'INV-2026-0034'],
                    ['2026-07-09 14:15', 'LED Bulb 9W', 'Purchase Invoice Confirmed', 'Main Warehouse', '+100 PCS', 'PUR-2026-0012'],
                    ['2026-07-08 11:00', 'Modular Switch 6A', 'Manual Adjustment', 'Secondary WH', '+50 PCS', 'ADJ-1004'],
                    ['2026-07-07 10:20', 'Copper Wire 1.5mm', 'Sales Return', 'Main Warehouse', '+2 Coils', 'SRT-0005'],
                    ['2026-07-06 15:45', 'PVC Tape Black', 'Transfer Out', 'Main Warehouse', '-50 Rolls', 'TRF-0018'],
                ]
            ],
            'gstr-1' => [
                'title' => 'GSTR-1 (Sales)',
                'headers' => ['Customer Name', 'GSTIN', 'Invoice No', 'Invoice Date', 'Taxable Value', 'IGST', 'CGST', 'SGST', 'Total Invoice Value'],
                'data' => [
                    ['Vijay Electronics', '07AAAAA1111A1Z1', 'INV-2026-0032', '2026-07-02', '₹ 9,745.76', '₹ 0.00', '₹ 877.12', '₹ 877.12', '₹ 11,500.00'],
                    ['Ram Prasad (B2C)', 'URP', 'INV-2026-0034', '2026-07-09', '₹ 10,677.97', '₹ 0.00', '₹ 961.02', '₹ 961.02', '₹ 12,600.00'],
                    ['Shyam Sunder (B2C)', 'URP', 'INV-2026-0033', '2026-06-28', '₹ 7,627.12', '₹ 0.00', '₹ 686.44', '₹ 686.44', '₹ 9,000.00'],
                ]
            ],
            'gstr-2' => [
                'title' => 'GSTR-2 (Purchase)',
                'headers' => ['Supplier Name', 'GSTIN', 'Invoice No', 'Invoice Date', 'Taxable Value', 'IGST', 'CGST', 'SGST', 'Total Purchase Value'],
                'data' => [
                    ['Goldmedal Electricals', '27BBBBB2222B2Z2', 'GM-2026-1044', '2026-07-01', '₹ 25,000.00', '₹ 4,500.00', '₹ 0.00', '₹ 0.00', '₹ 29,500.00'],
                    ['Polycab India Ltd', '24CCCCC3333C3Z3', 'PC-98745', '2026-07-04', '₹ 45,000.00', '₹ 0.00', '₹ 4,050.00', '₹ 4,050.00', '₹ 53,100.00'],
                ]
            ],
            'gstr-3b' => [
                'title' => 'GSTR-3B Summary',
                'headers' => ['Nature of Supplies', 'Total Taxable Value', 'Integrated Tax (IGST)', 'Central Tax (CGST)', 'State/UT Tax (SGST)', 'Cess'],
                'data' => [
                    ['Outward Taxable Supplies (Other than zero rated)', '₹ 28,050.85', '₹ 0.00', '₹ 2,524.58', '₹ 2,524.58', '₹ 0.00'],
                    ['Eligible ITC (Import of goods / Services / All other ITC)', '₹ 70,000.00', '₹ 4,500.00', '₹ 4,050.00', '₹ 4,050.00', '₹ 0.00'],
                    ['Net Tax Payable / (ITC Refund)', '—', '₹ -4,500.00', '₹ -1,525.42', '₹ -1,525.42', '₹ 0.00'],
                ]
            ],
            'gst-sales-hsn' => [
                'title' => 'GST Sales with HSN Summary',
                'headers' => ['HSN Code', 'Description', 'UQC', 'Total Quantity', 'Total Taxable Value', 'IGST Amount', 'CGST Amount', 'SGST Amount', 'Total Value'],
                'data' => [
                    ['85365020', 'Modular Switches', 'PCS', '1,200', '₹ 25,423.73', '₹ 0.00', '₹ 2,288.14', '₹ 2,288.14', '₹ 30,000.00'],
                    ['85444985', 'Insulated Copper Wires', 'COILS', '10', '₹ 9,745.76', '₹ 0.00', '₹ 877.12', '₹ 877.12', '₹ 11,500.00'],
                    ['94054090', 'LED Panel Lights', 'PCS', '45', '₹ 10,677.97', '₹ 0.00', '₹ 961.02', '₹ 961.02', '₹ 12,600.00'],
                ]
            ],
            'tds-payable' => [
                'title' => 'TDS Payable Report',
                'headers' => ['Deductee Party Name', 'PAN No', 'Section', 'Payment Amount', 'TDS Rate', 'TDS Amount Deducted', 'Due Date'],
                'data' => [
                    ['Goldmedal Electricals', 'BBBBB2222B', '194Q (Purchase of Goods)', '₹ 25,000.00', '0.1%', '₹ 25.00', '2026-08-07'],
                    ['Polycab India Ltd', 'CCCCC3333C', '194Q (Purchase of Goods)', '₹ 45,000.00', '0.1%', '₹ 45.00', '2026-08-07'],
                ]
            ],
            'tds-receivable' => [
                'title' => 'TDS Receivable Report',
                'headers' => ['Deductor Name', 'Deductor PAN', 'Bill Date', 'Bill Amount', 'TDS Rate', 'TDS Receivable Amount', 'Status'],
                'data' => [
                    ['Vijay Electronics', 'AAAAA1111A', '2026-07-02', '₹ 11,500.00', '0.1%', '₹ 11.50', 'Claimable'],
                ]
            ],
            'cash-sales' => [
                'title' => 'Cash Sales Report',
                'headers' => ['Date', 'Invoice No', 'Customer Name', 'Billing Type', 'Payment Mode', 'Gross Total', 'Received Amount'],
                'data' => [
                    ['2026-07-09', 'INV-2026-0034', 'Ram Prasad', 'Cash Memo', 'Cash', '₹ 12,600.00', '₹ 12,600.00'],
                    ['2026-07-08', 'INV-2026-0031', 'Walk-in Customer', 'Cash Memo', 'Cash', '₹ 1,200.00', '₹ 1,200.00'],
                ]
            ],
            'cash-purchase' => [
                'title' => 'Cash Purchases Report',
                'headers' => ['Date', 'Voucher No', 'Supplier Name', 'Billing Type', 'Payment Mode', 'Gross Total', 'Paid Amount'],
                'data' => [
                    ['2026-07-06', 'PUR-2026-0008', 'Local Electrical Market', 'Cash Invoice', 'Cash', '₹ 4,500.00', '₹ 4,500.00'],
                ]
            ],
            'cash-bank-summary' => [
                'title' => 'Cash & Bank Account Summary',
                'headers' => ['Account Name', 'Account Type', 'Opening Balance', 'Total Deposits / Inflow', 'Total Withdrawals / Outflow', 'Closing Balance'],
                'data' => [
                    ['Cash in Hand', 'Cash Account', '₹ 12,500.00', '₹ 38,200.00', '₹ 14,300.00', '₹ 36,400.00'],
                    ['HDFC Bank A/c 5020...', 'Bank Account', '₹ 1,50,000.00', '₹ 2,45,000.00', '₹ 1,80,000.00', '₹ 2,15,000.00'],
                    ['State Bank of India', 'Bank Account', '₹ 75,000.00', '₹ 60,000.00', '₹ 40,000.00', '₹ 95,000.00'],
                ]
            ],
            'bill-wise-profits' => [
                'title' => 'Bill-wise Profits Statement',
                'headers' => ['Date', 'Invoice No', 'Party Name', 'Invoice Grand Total', 'Calculated Product Cost', 'Net Profit Amount', 'Profit Margin (%)'],
                'data' => [
                    ['2026-07-09', 'INV-2026-0034', 'Ram Prasad', '₹ 12,600.00', '₹ 8,200.00', '₹ 4,400.00', '34.92%'],
                    ['2026-07-08', 'INV-2026-0031', 'Walk-in Customer', '₹ 1,200.00', '₹ 750.00', '₹ 450.00', '37.50%'],
                    ['2026-07-02', 'INV-2026-0032', 'Vijay Electronics', '₹ 11,500.00', '₹ 7,900.00', '₹ 3,600.00', '31.30%'],
                ]
            ],
            'sales-summary' => [
                'title' => 'Sales Summary (Monthly Breakdown)',
                'headers' => ['Month & Year', 'Total Invoices generated', 'Total Gross Sales', 'Total Discounts given', 'Total GST Collected', 'Net Sales Value'],
                'data' => [
                    ['July 2026', '12 Bills', '₹ 85,400.00', '₹ 4,200.00', '₹ 12,600.00', '₹ 93,800.00'],
                    ['June 2026', '28 Bills', '₹ 1,98,000.00', '₹ 12,500.00', '₹ 29,700.00', '₹ 2,15,200.00'],
                    ['May 2026', '24 Bills', '₹ 1,65,000.00', '₹ 9,000.00', '₹ 24,750.00', '₹ 1,80,750.00'],
                ]
            ],
            'daybook' => [
                'title' => 'Daybook (General Ledger Diary)',
                'headers' => ['Date & Time', 'Particulars Name', 'Transaction Type', 'Voucher Number', 'Debit Inflow (₹)', 'Credit Outflow (₹)'],
                'data' => [
                    ['2026-07-09 16:30', 'Sales Revenue A/c', 'Sales Invoice', 'INV-2026-0034', '12,600.00', '0.00'],
                    ['2026-07-09 16:35', 'Ram Prasad (Customer)', 'Payment In', 'PAY-2026-0122', '0.00', '12,600.00'],
                    ['2026-07-08 12:00', 'Goldmedal Electricals', 'Payment Out', 'PAY-2026-0044', '29,500.00', '0.00'],
                    ['2026-07-08 12:00', 'HDFC Bank Account', 'Bank Withdrawal', 'VCH-10022', '0.00', '29,500.00'],
                ]
            ],
            'profit-and-loss' => [
                'title' => 'Profit & Loss Statement (P&L)',
                'headers' => ['Particulars Description', 'Details Value (₹)', 'Net Group Amount (₹)'],
                'data' => [
                    ['Revenue from Operations (Gross Sales)', '1,98,000.00', '1,98,000.00'],
                    ['Cost of Goods Sold (COGS)', '1,28,000.00', '(1,28,000.00)'],
                    ['Gross Profit Margin', '—', '70,000.00'],
                    ['Operating Expenses (Salaries, Warehouse Rent)', '18,500.00', '(18,500.00)'],
                    ['Other Income (Interest / Discounts Received)', '1,200.00', '1,200.00'],
                    ['Net Operating Profit Before Taxes', '—', '52,700.00'],
                ]
            ],
            'balance-sheet' => [
                'title' => 'Balance Sheet Statement',
                'headers' => ['Liabilities & Equity Category', 'Liability Value (₹)', 'Assets Category', 'Asset Value (₹)'],
                'data' => [
                    ['Owner Capital Account', '2,50,000.00', 'Fixed Assets (Machinery & Fittings)', '45,000.00'],
                    ['Retained Earnings / Reserves', '52,700.00', 'Current Assets (Stock in Hand)', '1,32,000.00'],
                    ['Current Liabilities (Sundry Creditors)', '45,000.00', 'Current Assets (Sundry Debtors)', '78,200.00'],
                    ['Loans & Borrowings (SBI Term Loan)', '60,000.00', 'Cash & Bank Balance (SBI & Hand)', '1,52,500.00'],
                    ['TOTAL EQUITIES & LIABILITIES', '4,07,700.00', 'TOTAL BUSINESS ASSETS', '4,07,700.00'],
                ]
            ],
            'cash-and-bank-all' => [
                'title' => 'Cash and Bank Transaction Statement',
                'headers' => ['Date & Time', 'Transaction Ref No.', 'Particulars / Party', 'Payment Type', 'Method', 'Amount (₹)', 'Status'],
                'data' => [
                    ['2026-07-09 16:35', 'PAY-2026-0122', 'Ram Prasad', 'Payment In', 'Cash', '₹ 12,600.00', 'Completed'],
                    ['2026-07-08 12:00', 'PAY-2026-0044', 'Goldmedal Electricals', 'Payment Out', 'Bank Transfer', '₹ 29,500.00', 'Completed'],
                    ['2026-07-05 10:30', 'PAY-2026-0121', 'Vijay Electronics', 'Payment In', 'UPI / PhonePe', '₹ 5,000.00', 'Completed'],
                ]
            ],
        ];

        $report = $reportsConfig[$reportKey] ?? null;

        if (!$report) {
            abort(404, 'Report not found');
        }

        $title = $report['title'];
        $headers = $report['headers'];
        $data = $report['data'];

        return view('admin.reports.mock-viewer', compact('title', 'headers', 'data'));
    }

    public function lowStock()
    {
        $simpleProducts = SimpleProduct::with(['category'])->get();
        $variantProducts = VariantProduct::with(['category'])->get();

        $lowStockItems = [];

        // 1. Process Simple Products
        foreach ($simpleProducts as $p) {
            $stock = $p->total_stock ?? 0;
            $minAlert = $p->main_warehouse_min_stock_alert ?? 5;

            if ($stock <= $minAlert) {
                $status = $stock <= 0 ? 'Out of Stock' : ($stock <= 5 ? 'Critical' : 'Low Stock');
                $lowStockItems[] = [
                    'name' => $p->name,
                    'sku' => $p->sku_code ?? 'N/A',
                    'category' => $p->category->name ?? 'No Category',
                    'stock' => $stock,
                    'min_stock_alert' => $minAlert,
                    'type' => 'Simple',
                    'status' => $status
                ];
            }
        }

        // 2. Process Variant Products
        foreach ($variantProducts as $p) {
            if ($p->variants && is_array($p->variants)) {
                foreach ($p->variants as $index => $variant) {
                    $variantIdVal = $variant['_id'] ?? null;
                    $variantStock = WarehouseStock::where('product_id', $p->_id)
                        ->where('product_type', 'variant')
                        ->where('variant_id', (string) $variantIdVal)
                        ->first();
                    $stock = $variantStock ? $variantStock->quantity : 0;
                    $minAlert = $variantStock ? $variantStock->min_stock_alert : 5;

                    if ($stock <= $minAlert) {
                        $status = $stock <= 0 ? 'Out of Stock' : ($stock <= 5 ? 'Critical' : 'Low Stock');
                        $lowStockItems[] = [
                            'name' => $p->name . ' - ' . ($variant['name'] ?? 'Variant ' . ($index + 1)),
                            'sku' => $variant['sku_code'] ?? 'N/A',
                            'category' => $p->category->name ?? 'No Category',
                            'stock' => $stock,
                            'min_stock_alert' => $minAlert,
                            'type' => 'Variant',
                            'status' => $status
                        ];
                    }
                }
            }
        }

        usort($lowStockItems, fn($a, $b) => $a['stock'] <=> $b['stock']);

        return view('admin.reports.low-stock', compact('lowStockItems'));
    }

    public function lowStockPdf()
    {
        $simpleProducts = SimpleProduct::with(['category'])->get();
        $variantProducts = VariantProduct::with(['category'])->get();

        $lowStockItems = [];

        foreach ($simpleProducts as $p) {
            $stock = $p->total_stock ?? 0;
            $minAlert = $p->main_warehouse_min_stock_alert ?? 5;

            if ($stock <= $minAlert) {
                $status = $stock <= 0 ? 'Out of Stock' : ($stock <= 5 ? 'Critical' : 'Low Stock');
                $lowStockItems[] = [
                    'name' => $p->name,
                    'sku' => $p->sku_code ?? 'N/A',
                    'category' => $p->category->name ?? 'No Category',
                    'stock' => $stock,
                    'min_stock_alert' => $minAlert,
                    'type' => 'Simple',
                    'status' => $status
                ];
            }
        }

        foreach ($variantProducts as $p) {
            if ($p->variants && is_array($p->variants)) {
                foreach ($p->variants as $index => $variant) {
                    $variantIdVal = $variant['_id'] ?? null;
                    $variantStock = WarehouseStock::where('product_id', $p->_id)
                        ->where('product_type', 'variant')
                        ->where('variant_id', (string) $variantIdVal)
                        ->first();
                    $stock = $variantStock ? $variantStock->quantity : 0;
                    $minAlert = $variantStock ? $variantStock->min_stock_alert : 5;

                    if ($stock <= $minAlert) {
                        $status = $stock <= 0 ? 'Out of Stock' : ($stock <= 5 ? 'Critical' : 'Low Stock');
                        $lowStockItems[] = [
                            'name' => $p->name . ' - ' . ($variant['name'] ?? 'Variant ' . ($index + 1)),
                            'sku' => $variant['sku_code'] ?? 'N/A',
                            'category' => $p->category->name ?? 'No Category',
                            'stock' => $stock,
                            'min_stock_alert' => $minAlert,
                            'type' => 'Variant',
                            'status' => $status
                        ];
                    }
                }
            }
        }

        usort($lowStockItems, fn($a, $b) => $a['stock'] <=> $b['stock']);
        $settings = \App\Models\InvoiceSetting::first();

        return view('admin.reports.low-stock-pdf', compact('lowStockItems', 'settings'));
    }
}
