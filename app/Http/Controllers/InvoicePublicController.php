<?php

namespace App\Http\Controllers;

use App\Models\SalesInvoice;

class InvoicePublicController extends Controller
{
    public function show($token)
    {
        $invoice = SalesInvoice::with(['party', 'warehouse', 'items', 'salesman'])
            ->where('public_token', $token)
            ->firstOrFail();

        if ($invoice->invoice_type === 'cash') {
            $settings = \App\Models\CashMemoInvoiceSetting::first();
        } else {
            $settings = \App\Models\InvoiceSetting::first();
        }

        return view('admin.invoice.public', compact('invoice', 'settings'));
    }
}
