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
        'price',
        'discount',
        'tax_percent',
        'tax_amount',
        'total',
        'warranty_type',
        'warranty_period',
        'warranty_start',
        'warranty_end',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
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
}
