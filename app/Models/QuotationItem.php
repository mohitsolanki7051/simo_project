<?php
// app/Models/QuotationItem.php - Update model

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class QuotationItem extends Model
{
    use HasFactory;

    protected $table = 'quotation_items';

    protected $fillable = [
        'quotation_id',
        'product_id',
        'variant_id',
        'product_type',
        'product_name',
        'variant_name',
        'sku',
        'barcode',
        'hsn_sac', // New
        'unit',
        'quantity',
        'mrp_price',
        'price',
        'discount',
        'tax_percent', // New
        'tax_amount', // New
        'cgst_amount', // New
        'sgst_amount', // New
        'igst_amount', // New
        'total',
        'warranty_type', // New
        'warranty_period', // New
        'warranty_start', // New
        'warranty_end', // New
        'party_type',
    ];

    protected $casts = [
        'quantity' => 'float',
        'mrp_price' => 'float',
        'price' => 'float',
        'discount' => 'float',
        'tax_percent' => 'float',
        'tax_amount' => 'float',
        'cgst_amount' => 'float',
        'sgst_amount' => 'float',
        'igst_amount' => 'float',
        'total' => 'float',
        'warranty_period' => 'integer',
        'warranty_start' => 'date',
        'warranty_end' => 'date',
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }

    public function product()
    {
        if ($this->variant_id) {
            return $this->belongsTo(VariantProduct::class, 'product_id');
        }
        return $this->belongsTo(SimpleProduct::class, 'product_id');
    }
}
