<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SimpleProduct;
use App\Models\VariantProduct;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BarcodeController extends Controller
{
    /**
     * Show barcode print page
     */
    public function index()
    {
        $mainWarehouse = Warehouse::where('is_main', true)->first()
            ?? Warehouse::where('status', 'active')->first();

        return view('admin.barcode.index', compact('mainWarehouse'));
    }

    /**
     * Search products (Simple + Variant) from MAIN warehouse only
     */
    public function searchProducts(Request $request)
    {
        try {
            $search = trim($request->input('search', ''));

            if ($search === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Search term is required'
                ]);
            }

            $mainWarehouse = Warehouse::where('is_main', true)->first();
            if (!$mainWarehouse) {
                return response()->json([
                    'success' => false,
                    'message' => 'Main warehouse not found'
                ]);
            }

            $results = [];
            $warehouseId = $mainWarehouse->id;

            /* =====================================================
             | SIMPLE PRODUCTS
             ===================================================== */
            $simpleProducts = SimpleProduct::where('status', 'active')
                ->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('sku_code', 'like', "%{$search}%")
                      ->orWhere('barcode', 'like', "%{$search}%");
                })
                ->get();

            foreach ($simpleProducts as $product) {
                $stock = WarehouseStock::where('product_id', $product->id)
                    ->where('product_type', 'simple')
                    ->where('warehouse_id', $warehouseId)
                    ->first();

                if (!$stock) continue;

                $barcodeValue = $product->barcode ?: $product->sku_code ?: $product->id;

                $results[] = [
                    'id' => 'simple-' . $product->id,
                    'type' => 'simple',
                    'model_type' => 'simple',
                    'name' => $product->name,
                    'sku_code' => $product->sku_code,
                    'barcode' => $barcodeValue,
                    'barcode_symbology' => $product->barcode_symbology ?? 'CODE128',
                    'price' => (float) $product->sale_price,
                    'brand' => $product->brand,
                    'current_stock' => $stock->quantity ?? 0,
                    'base_image' => $product->base_image ? asset('storage/' . $product->base_image) : null,
                    'warehouse_id' => $warehouseId,
                    'warehouse_name' => $mainWarehouse->name,
                ];
            }

            /* =====================================================
             | VARIANT PRODUCTS (DYNAMIC ATTRIBUTES)
             ===================================================== */
            $variantProducts = VariantProduct::where('status', 'active')->get();

            foreach ($variantProducts as $product) {
                if (!is_array($product->variants)) continue;

                foreach ($product->variants as $index => $variant) {

                    $variantSku = $variant['sku_code'] ?? '';
                    $variantBarcode = $variant['barcode'] ?? '';

                    $productName = (string) ($product->name ?? '');
$variantSku = (string) ($variant['sku_code'] ?? '');
$variantBarcode = (string) ($variant['barcode'] ?? '');

if (
    stripos($productName, $search) === false &&
    stripos($variantSku, $search) === false &&
    stripos($variantBarcode, $search) === false
) {
    continue;
}


                    $variantId = $variant['_id'] ?? $index;

                    $stock = WarehouseStock::where('product_id', $product->id)
                        ->where('product_type', 'variant')
                        ->where('variant_id', (string) $variantId)
                        ->where('warehouse_id', $warehouseId)
                        ->first();

                    if (!$stock) continue;

                    /* 🔥 Dynamic Variant Name */
                   $variantName = (string) ($product->name ?? '');

if (
    isset($variant['attributes']) &&
    is_array($variant['attributes']) &&
    count($variant['attributes']) > 0
) {
    foreach ($variant['attributes'] as $key => $value) {
        if (is_scalar($value) && $value !== '') {
            $variantName .= ' ' . (string) $value;
        }
    }
}

                    $barcodeValue =
                        $variantBarcode
                        ?: $variantSku
                        ?: ($product->id . '-' . $index);

                    $results[] = [
                        'id' => 'variant-' . $product->id . '-' . $index,
                        'type' => 'variant',
                        'model_type' => 'variant',
                        'product_id' => $product->id,
                        'variant_index' => $index,
                        'name' => $variantName,
                        'sku_code' => $variantSku,
                        'barcode' => $barcodeValue,
                        'barcode_symbology' => 'CODE128',
                        'price' => (float) ($variant['sale_price'] ?? 0),
                        'brand' => $product->brand,
                        'current_stock' => $stock->quantity ?? 0,
                        'base_image' =>
                            !empty($variant['base_image'])
                                ? asset('storage/' . $variant['base_image'])
                                : ($product->base_image ? asset('storage/' . $product->base_image) : null),
                        'warehouse_id' => $warehouseId,
                        'warehouse_name' => $mainWarehouse->name,
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'products' => $results,
                'warehouse_id' => $warehouseId,
                'warehouse_name' => $mainWarehouse->name
            ]);

        } catch (\Exception $e) {
            Log::error('Barcode search failed', [
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Search failed'
            ], 500);
        }
    }
}
