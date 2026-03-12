<?php
namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\InvoiceSetting;
use App\Models\CashMemoInvoiceSetting;

class PublicQuotationController extends Controller
{
    public function show($token)
    {
        $quotation = Quotation::with(['party', 'items', 'warehouse', 'salesman'])
            ->where('public_token', $token)
            ->firstOrFail();

        if ($quotation->invoice_type === 'cash') {
            $settings = CashMemoInvoiceSetting::first();
        } else {
            $settings = InvoiceSetting::first();
        }

        return view('admin.quotation_whatsapp.public', compact('quotation', 'settings'));
    }
}
