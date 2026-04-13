<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class SalesInvoiceItem extends Model
{
    use HasFactory;

    protected $collection = 'sales_invoice_items';

    protected $fillable = [
        'sales_invoice_id',
        'product_id',
        'variant_id',

        'product_name',
        'variant_name',
        'sku',
        'barcode',
        'hsn_sac',

        'quantity',
        'unit',

        'mrp_price',
        'price',            // always stored ex-GST (base price)
        'sale_price_incl',  // original inclusive price (only set when gst_inclusive=true)
        'gst_inclusive',    // boolean: was this item added in inclusive mode?
        'discount',
        'tax_percent',
        'tax_amount',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'total',

        'warranty_type',
        'warranty_period',
        'warranty_start',
        'warranty_end',
    ];

    protected $casts = [
        'quantity'        => 'decimal:2',
        'price'           => 'decimal:2',
        'sale_price_incl' => 'float',
        'gst_inclusive'   => 'boolean',
        'discount'        => 'decimal:2',
        'tax_percent'     => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'cgst_amount'     => 'decimal:2',
        'sgst_amount'     => 'decimal:2',
        'igst_amount'     => 'decimal:2',
        'total'           => 'decimal:2',
        'warranty_period' => 'integer',
        'warranty_start'  => 'date',
        'warranty_end'    => 'date',
    ];

    // Relationships
    public function invoice()
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_id');
    }

    public function product()
    {
        if ($this->variant_id) {
            return $this->belongsTo(VariantProduct::class, 'product_id');
        }
        return $this->belongsTo(SimpleProduct::class, 'product_id');
    }

    public function hasIgst()
    {
        return $this->igst_amount > 0;
    }

    public function hasCgstSgst()
    {
        return $this->cgst_amount > 0 && $this->sgst_amount > 0;
    }
}
