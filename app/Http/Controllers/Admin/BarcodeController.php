<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BarcodeController extends Controller
{
    /**
     * Show barcode print page
     */
    public function index()
    {
        $warehouses = Warehouse::where('status', 'active')
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.barcode.index', compact('warehouses'));
    }

    /**
     * Search products by SKU or name (AJAX)
     * ✅ UPDATED: Filter by warehouse
     */
    public function searchProducts(Request $request)
    {
        try {
            $search = $request->input('search', '');
            $warehouseId = $request->input('warehouse_id');

            if (empty($search)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Search term is required'
                ]);
            }

            // ✅ UPDATED: Check if warehouse is selected
            if (empty($warehouseId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select a warehouse first'
                ]);
            }

            // ✅ UPDATED: Search in main products WITH warehouse filter
            $products = Product::where(function($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                      ->orWhere('sku_code', 'like', '%' . $search . '%')
                      ->orWhere('barcode', 'like', '%' . $search . '%');
            })
            ->where('warehouse_id', $warehouseId) // ✅ ADDED: Filter by warehouse
            ->where('status', 'active')
            ->limit(20)
            ->get(['id', 'name', 'sku_code', 'barcode', 'barcode_symbology', 'brand', 'current_stock', 'base_image', 'price', 'variants', 'warehouse_id']);

            $results = [];

            // Add main products
            foreach ($products as $product) {
                $barcodeValue = !empty($product->barcode) ? $product->barcode : $product->sku_code;

                $results[] = [
                    'id' => $product->id,
                    'type' => 'main',
                    'sku_code' => $product->sku_code,
                    'barcode' => $barcodeValue,
                    'barcode_symbology' => $product->barcode_symbology ?: 'CODE128',
                    'name' => $product->name,
                    'brand' => $product->brand,
                    'price' => $product->price,
                    'current_stock' => $product->current_stock,
                    'base_image' => $product->base_image ? asset('storage/' . $product->base_image) : null,
                    'warehouse_id' => $product->warehouse_id, // ✅ ADDED
                ];

                // Add variants if they exist
                if ($product->variants && is_array($product->variants)) {
                    foreach ($product->variants as $index => $variant) {
                        // Search in variant SKU or barcode
                        if (stripos($variant['sku_code'] ?? '', $search) !== false ||
                            stripos($variant['barcode'] ?? '', $search) !== false) {

                            $variantName = $product->name;
                            if (!empty($variant['watt'])) $variantName .= ' ' . $variant['watt'];
                            if (!empty($variant['color_temperature'])) $variantName .= ' ' . $variant['color_temperature'];
                            if (!empty($variant['shape'])) $variantName .= ' ' . $variant['shape'];

                            $variantBarcode = !empty($variant['barcode']) ? $variant['barcode'] : $variant['sku_code'];

                            $results[] = [
                                'id' => $product->id . '-variant-' . $index,
                                'type' => 'variant',
                                'product_id' => $product->id,
                                'variant_index' => $index,
                                'sku_code' => $variant['sku_code'],
                                'barcode' => $variantBarcode,
                                'barcode_symbology' => $variant['barcode_symbology'] ?: 'CODE128',
                                'name' => $variantName,
                                'brand' => $product->brand,
                                'price' => $variant['price'] ?? $product->price,
                                'current_stock' => $variant['current_stock'] ?? 0,
                                'base_image' => !empty($variant['base_image']) ? asset('storage/' . $variant['base_image']) : ($product->base_image ? asset('storage/' . $product->base_image) : null),
                                'warehouse_id' => $product->warehouse_id, // ✅ ADDED
                            ];
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'products' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Product search failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Search failed'
            ], 500);
        }
    }

    /**
     * Get product details by ID (AJAX)
     */
    public function getProduct(Request $request)
    {
        try {
            $id = $request->input('id');

            // Check if it's a variant
            if (strpos($id, '-variant-') !== false) {
                $parts = explode('-variant-', $id);
                $productId = $parts[0];
                $variantIndex = $parts[1];

                $product = Product::findOrFail($productId);
                $variant = $product->variants[$variantIndex] ?? null;

                if (!$variant) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Variant not found'
                    ], 404);
                }

                $variantName = $product->name;
                if (!empty($variant['watt'])) $variantName .= ' ' . $variant['watt'];
                if (!empty($variant['color_temperature'])) $variantName .= ' ' . $variant['color_temperature'];
                if (!empty($variant['shape'])) $variantName .= ' ' . $variant['shape'];

                $variantBarcode = !empty($variant['barcode']) ? $variant['barcode'] : $variant['sku_code'];

                return response()->json([
                    'success' => true,
                    'product' => [
                        'id' => $id,
                        'type' => 'variant',
                        'product_id' => $product->id,
                        'variant_index' => $variantIndex,
                        'sku_code' => $variant['sku_code'],
                        'barcode' => $variantBarcode,
                        'barcode_symbology' => $variant['barcode_symbology'] ?: 'CODE128',
                        'name' => $variantName,
                        'brand' => $product->brand,
                        'price' => $variant['price'] ?? $product->price,
                        'current_stock' => $variant['current_stock'] ?? 0,
                        'base_image' => !empty($variant['base_image']) ? asset('storage/' . $variant['base_image']) : ($product->base_image ? asset('storage/' . $product->base_image) : null),
                        'warehouse_id' => $product->warehouse_id, // ✅ ADDED
                    ]
                ]);
            } else {
                // Main product
                $product = Product::findOrFail($id);
                $barcodeValue = !empty($product->barcode) ? $product->barcode : $product->sku_code;

                return response()->json([
                    'success' => true,
                    'product' => [
                        'id' => $product->id,
                        'type' => 'main',
                        'sku_code' => $product->sku_code,
                        'barcode' => $barcodeValue,
                        'barcode_symbology' => $product->barcode_symbology ?: 'CODE128',
                        'name' => $product->name,
                        'brand' => $product->brand,
                        'price' => $product->price,
                        'current_stock' => $product->current_stock,
                        'base_image' => $product->base_image ? asset('storage/' . $product->base_image) : null,
                        'warehouse_id' => $product->warehouse_id, // ✅ ADDED
                    ]
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Get product failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }
    }
}
