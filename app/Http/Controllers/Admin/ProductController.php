<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index()
    {
        $products = Product::with(['category'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new product
     */
    public function create()
    {
        $categories = Category::where('status', 'active')
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.products.create', compact('categories'));
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            // Basic Information
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'brand' => 'required|string|max:100',
            'body_type' => 'required|string|max:100',
            'warranty' => 'required|string|max:100',
            'status' => 'required|in:active,inactive',

            // Main Product SKU & Barcode
            'sku_code' => 'required|string|max:100|unique:products,sku_code',
            'barcode_symbology' => 'required|in:CODE128,CODE39,EAN13,EAN8,UPC',

            // Pricing & Tax
            'mrp_price' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'hsn_code' => 'required|string|max:50',
            'gst' => 'required|numeric|min:0|max:100',

            // Stock Management
            'opening_stock' => 'nullable|integer|min:0',
            'min_stock_alert' => 'nullable|integer|min:0',

            // Images
            'base_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

            // Description
            'description' => 'required|string',

            // Variants
            'variants' => 'nullable|array',
            'variants.*.watt' => 'nullable|string|max:50',
            'variants.*.shape' => 'nullable|string|max:100',
            'variants.*.color_temperature' => 'nullable|string|max:100',
            'variants.*.cutting_size' => 'nullable|string|max:100',
            'variants.*.unit' => 'nullable|string|max:50',
            'variants.*.sku_code' => 'required_with:variants|string|max:100',
            'variants.*.barcode_symbology' => 'required_with:variants|in:CODE128,CODE39,EAN13,EAN8,UPC',
            'variants.*.cost_price' => 'nullable|numeric|min:0',
            'variants.*.dealer_price' => 'nullable|numeric|min:0',
            'variants.*.distributor_price' => 'nullable|numeric|min:0',
            'variants.*.opening_stock' => 'nullable|integer|min:0',
            'variants.*.min_stock_alert' => 'nullable|integer|min:0',
            'variants.*.base_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'variants.*.gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            // Upload main product base image
            $baseImagePath = null;
            if ($request->hasFile('base_image')) {
                $baseImagePath = $request->file('base_image')->store('products', 'public');
            }

            // Upload main product gallery images
            $galleryPaths = [];
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $image) {
                    $galleryPaths[] = $image->store('products/gallery', 'public');
                }
            }

            // Generate barcode for main product
            $barcode = $this->generateBarcode($request->sku_code, $request->barcode_symbology);

            // Process variants if provided
            $variants = [];
            if ($request->has('variants') && is_array($request->variants)) {
                foreach ($request->variants as $index => $variantData) {
                    // Upload variant base image
                    $variantBaseImage = null;
                    if ($request->hasFile("variants.{$index}.base_image")) {
                        $variantBaseImage = $request->file("variants.{$index}.base_image")
                            ->store('products/variants', 'public');
                    }

                    // Upload variant gallery images
                    $variantGalleryImages = [];
                    if ($request->hasFile("variants.{$index}.gallery_images")) {
                        foreach ($request->file("variants.{$index}.gallery_images") as $image) {
                            $variantGalleryImages[] = $image->store('products/variants/gallery', 'public');
                        }
                    }

                    // Generate barcode for variant
                    $variantBarcode = $this->generateBarcode(
                        $variantData['sku_code'],
                        $variantData['barcode_symbology']
                    );

                    $variants[] = [
                        'watt' => $variantData['watt'] ?? null,
                        'shape' => $variantData['shape'] ?? null,
                        'color_temperature' => $variantData['color_temperature'] ?? null,
                        'cutting_size' => $variantData['cutting_size'] ?? null,
                        'unit' => $variantData['unit'] ?? null,
                        'sku_code' => $variantData['sku_code'],
                        'barcode' => $variantBarcode,
                        'barcode_symbology' => $variantData['barcode_symbology'],
                        'cost_price' => $variantData['cost_price'] ?? null,
                        'dealer_price' => $variantData['dealer_price'] ?? null,
                        'distributor_price' => $variantData['distributor_price'] ?? null,
                        'opening_stock' => $variantData['opening_stock'] ?? 0,
                        'current_stock' => $variantData['opening_stock'] ?? 0,
                        'min_stock_alert' => $variantData['min_stock_alert'] ?? 10,
                        'base_image' => $variantBaseImage,
                        'gallery_images' => $variantGalleryImages,
                    ];
                }
            }

            // Create product
            $product = Product::create([
                // Basic Information
                'name' => $request->name,
                'category_id' => $request->category_id,
                'brand' => $request->brand,
                'body_type' => $request->body_type,
                'warranty' => $request->warranty,
                'status' => $request->status,

                // Main Product SKU & Barcode
                'sku_code' => $request->sku_code,
                'barcode' => $barcode,
                'barcode_symbology' => $request->barcode_symbology,

                // Pricing & Tax
                'mrp_price' => $request->mrp_price,
                'price' => $request->price,
                'hsn_code' => $request->hsn_code,
                'gst' => $request->gst,

                // Stock Management
                'opening_stock' => $request->opening_stock ?? 0,
                'current_stock' => $request->opening_stock ?? 0,
                'min_stock_alert' => $request->min_stock_alert ?? 10,

                // Images
                'base_image' => $baseImagePath,
                'gallery_images' => $galleryPaths,

                // Description
                'short_description' => $request->short_description,
                'description' => $request->description,

                // Variants
                'variants' => $variants,
            ]);

            return redirect()->route('admin.products.index')
                ->with('success', 'Product created successfully with ' . count($variants) . ' variant(s)!');

        } catch (\Exception $e) {
            Log::error('Product creation failed: ' . $e->getMessage());

            // Delete uploaded images if product creation fails
            if (isset($baseImagePath)) {
                Storage::disk('public')->delete($baseImagePath);
            }
            if (!empty($galleryPaths)) {
                foreach ($galleryPaths as $path) {
                    Storage::disk('public')->delete($path);
                }
            }

            return back()->withInput()
                ->with('error', 'Failed to create product. Please try again.');
        }
    }

    /**
     * Generate barcode based on SKU and symbology
     */
    /**
 * Generate barcode based on SKU and symbology
 */
private function generateBarcode($sku, $symbology)
{
    // ✅ SKU ko hi barcode banayein
    // Remove any spaces or special characters
    $barcode = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $sku));

    // Ensure barcode has minimum length for some symbologies
    if ($symbology === 'EAN13' && strlen($barcode) < 12) {
        $barcode = str_pad($barcode, 12, '0', STR_PAD_LEFT);
    } elseif ($symbology === 'CODE39' && strlen($barcode) < 4) {
        $barcode = str_pad($barcode, 4, '0', STR_PAD_LEFT);
    }

    return $barcode;
}

    /**
     * Show the form for editing the product
     */
    public function edit($id)
    {
        $product = Product::findOrFail($id);

        $categories = Category::where('status', 'active')
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Update the product
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        // Validation (similar to store but excluding unique SKU for current product)
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'brand' => 'required|string|max:100',
            'body_type' => 'required|string|max:100',
            'warranty' => 'required|string|max:100',
            'status' => 'required|in:active,inactive',
            'sku_code' => 'required|string|max:100|unique:products,sku_code,' . $id,
            'barcode_symbology' => 'required|in:CODE128,CODE39,EAN13,EAN8,UPC',
            'mrp_price' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'hsn_code' => 'required|string|max:50',
            'gst' => 'required|numeric|min:0|max:100',
            'current_stock' => 'nullable|integer|min:0',
            'min_stock_alert' => 'nullable|integer|min:0',
            'base_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'short_description' => 'required|string|max:500',
            'description' => 'required|string',
        ]);

        try {
            $data = $request->except(['base_image', 'gallery_images']);

            // Update barcode if SKU or symbology changed
            if ($request->sku_code !== $product->sku_code ||
                $request->barcode_symbology !== $product->barcode_symbology) {
                $data['barcode'] = $this->generateBarcode($request->sku_code, $request->barcode_symbology);
            }

            // Update base image if provided
            if ($request->hasFile('base_image')) {
                if ($product->base_image) {
                    Storage::disk('public')->delete($product->base_image);
                }
                $data['base_image'] = $request->file('base_image')->store('products', 'public');
            }

            // Update gallery images if provided
            if ($request->hasFile('gallery_images')) {
                if ($product->gallery_images && is_array($product->gallery_images)) {
                    foreach ($product->gallery_images as $oldImage) {
                        Storage::disk('public')->delete($oldImage);
                    }
                }

                $galleryPaths = [];
                foreach ($request->file('gallery_images') as $image) {
                    $galleryPaths[] = $image->store('products/gallery', 'public');
                }
                $data['gallery_images'] = $galleryPaths;
            }

            $product->update($data);

            return redirect()->route('admin.products.index')
                ->with('success', 'Product updated successfully!');

        } catch (\Exception $e) {
            Log::error('Product update failed: ' . $e->getMessage());

            return back()->withInput()
                ->with('error', 'Failed to update product. Please try again.');
        }
    }

    /**
     * Remove the product
     */
    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);

            // Delete base image
            if ($product->base_image) {
                Storage::disk('public')->delete($product->base_image);
            }

            // Delete gallery images
            if ($product->gallery_images && is_array($product->gallery_images)) {
                foreach ($product->gallery_images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }

            // Delete variant images
            if ($product->variants && is_array($product->variants)) {
                foreach ($product->variants as $variant) {
                    if (!empty($variant['base_image'])) {
                        Storage::disk('public')->delete($variant['base_image']);
                    }
                    if (!empty($variant['gallery_images']) && is_array($variant['gallery_images'])) {
                        foreach ($variant['gallery_images'] as $image) {
                            Storage::disk('public')->delete($image);
                        }
                    }
                }
            }

            $product->delete();

            return redirect()->route('admin.products.index')
                ->with('success', 'Product deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Product deletion failed: ' . $e->getMessage());

            return back()->with('error', 'Failed to delete product. Please try again.');
        }
    }

    /**
     * Get product details (AJAX)
     */
    public function show($id)
    {
        try {
            $product = Product::with(['category'])->findOrFail($id);
            return response()->json([
                'success' => true,
                'product' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }
    }

    /**
     * Update product stock (AJAX)
     */
    public function updateStock(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);

            $request->validate([
                'quantity' => 'required|integer',
                'type' => 'required|in:add,subtract,set'
            ]);

            $newStock = $product->updateStock($request->quantity, $request->type);

            return response()->json([
                'success' => true,
                'message' => 'Stock updated successfully',
                'current_stock' => $newStock
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update stock'
            ], 500);
        }
    }

    /**
     * Get low stock products
     */
    public function lowStockProducts()
    {
        $products = Product::lowStock()
            ->with(['category'])
            ->get();

        return view('admin.products.low-stock', compact('products'));
    }

    /**
     * Get out of stock products
     */
    public function outOfStockProducts()
    {
        $products = Product::outOfStock()
            ->with(['category'])
            ->get();

        return view('admin.products.out-of-stock', compact('products'));
    }

    /**
     * Bulk delete products
     */
    public function bulkDestroy(Request $request)
    {
        try {
            $request->validate([
                'product_ids' => 'required|array'
            ]);

            $products = Product::whereIn('id', $request->product_ids)->get();

            foreach ($products as $product) {
                // Delete base image
                if ($product->base_image && Storage::disk('public')->exists($product->base_image)) {
                    Storage::disk('public')->delete($product->base_image);
                }

                // Delete gallery images
                if ($product->gallery_images && is_array($product->gallery_images)) {
                    foreach ($product->gallery_images as $image) {
                        if (Storage::disk('public')->exists($image)) {
                            Storage::disk('public')->delete($image);
                        }
                    }
                }

                // Delete variant images
                if ($product->variants && is_array($product->variants)) {
                    foreach ($product->variants as $variant) {
                        if (!empty($variant['base_image']) && Storage::disk('public')->exists($variant['base_image'])) {
                            Storage::disk('public')->delete($variant['base_image']);
                        }
                        if (!empty($variant['gallery_images']) && is_array($variant['gallery_images'])) {
                            foreach ($variant['gallery_images'] as $image) {
                                if (Storage::disk('public')->exists($image)) {
                                    Storage::disk('public')->delete($image);
                                }
                            }
                        }
                    }
                }
            }

            Product::whereIn('id', $request->product_ids)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Products deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Bulk product deletion failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update products status
     */
    public function bulkUpdateStatus(Request $request)
    {
        try {
            $request->validate([
                'product_ids' => 'required|array',
                'status' => 'required|in:active,inactive'
            ]);

            Product::whereIn('id', $request->product_ids)
                ->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Products status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update products status'
            ], 500);
        }
    }
}
