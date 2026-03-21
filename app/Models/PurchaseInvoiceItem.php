<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class PurchaseInvoiceItem extends Model
{
    use HasFactory;

    protected $collection = 'purchase_invoice_items';

    protected $fillable = [
        'purchase_invoice_id',
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
        'purchase_price',
        'sale_price',
        'discount',
        'tax_percent',
        'tax_amount',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'total',
        'warranty_type',
        'warranty_period',
    ];

    protected $casts = [
        'quantity' => 'float',
        'mrp_price' => 'float',
        'purchase_price' => 'float',
        'sale_price' => 'float',
        'discount' => 'float',
        'tax_percent' => 'float',
        'tax_amount' => 'float',
        'cgst_amount' => 'float',
        'sgst_amount' => 'float',
        'igst_amount' => 'float',
        'total' => 'float',
        'warranty_period' => 'integer',
    ];

    public function invoice()
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
    }

    public function product()
    {
        if ($this->variant_id) {
            return $this->belongsTo(VariantProduct::class, 'product_id');
        }
        return $this->belongsTo(SimpleProduct::class, 'product_id');
    }
}
