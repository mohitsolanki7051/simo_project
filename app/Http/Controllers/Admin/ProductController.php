<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Warehouse;
use App\Models\StockHistory;
use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{

    public function index()
    {
        $products = Product::with(['category', 'warehouse'])
            ->orderBy('created_at', 'desc')
            ->get();
        $warehouses = Warehouse::where('status', 'active')->orderBy('name', 'asc')->get();

        return view('admin.products.index', compact('products', 'warehouses'));
    }
    public function create()
    {
        $categories = Category::where('status', 'active')->orderBy('name', 'asc')->get();
        $attributes = Attribute::active()->ordered()->get();
        $warehouses = Warehouse::where('status', 'active')->orderBy('name', 'asc')->get();

        return view('admin.products.create', compact('categories', 'attributes', 'warehouses'));
    }

    /**
     * Check SKU code availability (for real-time validation)
     */
    public function checkSkuAvailability(Request $request)
    {
        try {
            $sku = $request->input('sku_code');
            $productId = $request->input('product_id'); // For edit mode

            if (empty($sku)) {
                return response()->json([
                    'available' => true,
                    'message' => ''
                ]);
            }

            // Check in main products
            $query = Product::where('sku_code', $sku);

            // Exclude current product if editing
            if ($productId) {
                $query->where('_id', '!=', $productId);
            }

            $existsInProducts = $query->exists();

            if ($existsInProducts) {
                return response()->json([
                    'available' => false,
                    'message' => '⚠️ This SKU code is already used in another product'
                ]);
            }

            // Check in variants of all products
            $productsWithVariants = Product::all();
            foreach ($productsWithVariants as $product) {
                // Skip current product if editing
                if ($productId && $product->_id == $productId) {
                    continue;
                }

                if ($product->variants && is_array($product->variants)) {
                    foreach ($product->variants as $variant) {
                        if (isset($variant['sku_code']) && $variant['sku_code'] === $sku) {
                            return response()->json([
                                'available' => false,
                                'message' => '⚠️ This SKU code is already used in a product variant'
                            ]);
                        }
                    }
                }
            }

            return response()->json([
                'available' => true,
                'message' => '✓ SKU code is available'
            ]);

        } catch (\Exception $e) {
            Log::error('SKU check failed: ' . $e->getMessage());
            return response()->json([
                'available' => true,
                'message' => ''
            ]);
        }
    }

    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            // Basic Information
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'brand' => 'required|string|max:100',
            'body_type' => 'required|string|max:100',
            'warranty' => 'required|string|max:10',
            'status' => 'required|in:active,inactive',

            // Main Product SKU & Barcode
            'sku_code' => 'required|string|max:16|unique:products,sku_code',
            'barcode_symbology' => 'required|in:CODE128,CODE39,EAN13,EAN8,UPC',

            // Tax (pricing fields removed from main product)
            'hsn_code' => 'required|string|max:8',
            'gst' => 'required|numeric|min:0|max:100',

            // Images
            'base_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

            // Description
            'description' => 'required|string',

            // Variants
            'variants' => 'nullable|array',
            'variants.*.name' => 'required_with:variants|string|max:255',
            'variants.*.color_temperature' => 'nullable|string|max:100',
            'variants.*.watt' => 'nullable|string|max:50',
            'variants.*.shape' => 'nullable|string|max:100',
            'variants.*.unit' => 'required_with:variants|string|max:50',
            'variants.*.sku_code' => 'required_with:variants|string|max:16|distinct',
            'variants.*.barcode_symbology' => 'required_with:variants|in:CODE128,CODE39,EAN13,EAN8,UPC',
            'variants.*.mrp_price' => 'required_with:variants|numeric|min:0',
            'variants.*.cost_price' => 'required_with:variants|numeric|min:0',
            'variants.*.dealer_price' => 'required_with:variants|numeric|min:0',
            'variants.*.distributor_price' => 'required_with:variants|numeric|min:0',
            'variants.*.opening_stock' => 'required_with:variants|integer|min:0',
            'variants.*.min_stock_alert' => 'required_with:variants|integer|min:0',
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
                        // Variant name
                        'name' => $variantData['name'],

                        // Attribute values
                        'color_temperature' => $variantData['color_temperature'] ?? null,
                        'watt' => $variantData['watt'] ?? null,
                        'shape' => $variantData['shape'] ?? null,
                        'unit' => $variantData['unit'],

                        // SKU & Barcode
                        'sku_code' => $variantData['sku_code'],
                        'barcode' => $variantBarcode,
                        'barcode_symbology' => $variantData['barcode_symbology'],

                        // Pricing
                        'mrp_price' => $variantData['mrp_price'],
                        'cost_price' => $variantData['cost_price'],
                        'dealer_price' => $variantData['dealer_price'],
                        'distributor_price' => $variantData['distributor_price'],

                        // Stock
                        'opening_stock' => $variantData['opening_stock'],
                        'current_stock' => $variantData['opening_stock'],
                        'min_stock_alert' => $variantData['min_stock_alert'],

                        // Images
                        'base_image' => $variantBaseImage,
                        'gallery_images' => $variantGalleryImages,

                        // Store complete attributes data
                        'attributes' => json_decode($variantData['attributes'] ?? '[]', true)
                    ];
                }
            }

            // Create product
            $product = Product::create([
                // Basic Information
                'name' => $request->name,
                'category_id' => $request->category_id,
                'warehouse_id' => $request->warehouse_id,
                'brand' => $request->brand,
                'body_type' => $request->body_type,
                'warranty' => $request->warranty,
                'status' => $request->status,

                // Main Product SKU & Barcode
                'sku_code' => $request->sku_code,
                'barcode' => $barcode,
                'barcode_symbology' => $request->barcode_symbology,

                // Tax (no pricing/stock for main product)
                'hsn_code' => $request->hsn_code,
                'gst' => $request->gst,

                // Images
                'base_image' => $baseImagePath,
                'gallery_images' => $galleryPaths,

                // Description
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
    private function generateBarcode($sku, $symbology)
    {
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


    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::where('status', 'active')->orderBy('name', 'asc')->get();
        $warehouses = Warehouse::where('status', 'active')->orderBy('name', 'asc')->get();

        return view('admin.products.edit', compact('product', 'categories', 'warehouses'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        // Validation
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'brand' => 'required|string|max:100',
            'body_type' => 'required|string|max:100',
            'warranty' => 'required|string|max:10',
            'status' => 'required|in:active,inactive',
            'sku_code' => 'required|string|max:16|unique:products,sku_code,' . $id,
            'barcode_symbology' => 'required|in:CODE128,CODE39,EAN13,EAN8,UPC',
            'hsn_code' => 'required|string|max:8',
            'gst' => 'required|numeric|min:0|max:100',
            'base_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'required|string',

            // Variant validation
            'variants' => 'nullable|array',
            'variants.*.name' => 'required_with:variants|string|max:255',
            'variants.*.unit' => 'required_with:variants|string|max:50',
            'variants.*.sku_code' => 'required_with:variants|string|max:16',
            'variants.*.barcode_symbology' => 'required_with:variants|in:CODE128,CODE39,EAN13,EAN8,UPC',
            'variants.*.mrp_price' => 'required_with:variants|numeric|min:0',
            'variants.*.cost_price' => 'required_with:variants|numeric|min:0',
            'variants.*.dealer_price' => 'required_with:variants|numeric|min:0',
            'variants.*.distributor_price' => 'required_with:variants|numeric|min:0',
            'variants.*.current_stock' => 'required_with:variants|integer|min:0',
            'variants.*.min_stock_alert' => 'required_with:variants|integer|min:0',
            'variants.*.base_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'variants.*.gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $data = $request->except(['base_image', 'gallery_images', 'remove_base_image', 'remove_gallery_images', 'variants']);

            // Update barcode if SKU or symbology changed
            if (
                $request->sku_code !== $product->sku_code ||
                $request->barcode_symbology !== $product->barcode_symbology
            ) {
                $data['barcode'] = $this->generateBarcode($request->sku_code, $request->barcode_symbology);
            }

            // Handle base image removal
            if ($request->has('remove_base_image') && $request->remove_base_image == '1') {
                if ($product->base_image) {
                    Storage::disk('public')->delete($product->base_image);
                }
                $data['base_image'] = null;
            }

            // Update base image if provided
            if ($request->hasFile('base_image')) {
                if ($product->base_image) {
                    Storage::disk('public')->delete($product->base_image);
                }
                $data['base_image'] = $request->file('base_image')->store('products', 'public');
            }

            // Handle gallery images removal
            if ($request->has('remove_gallery_images')) {
                $currentGallery = $product->gallery_images ?? [];
                $removeIndices = $request->remove_gallery_images;

                foreach ($removeIndices as $index) {
                    if (isset($currentGallery[$index])) {
                        Storage::disk('public')->delete($currentGallery[$index]);
                        unset($currentGallery[$index]);
                    }
                }

                $data['gallery_images'] = array_values($currentGallery);
            }

            // Update gallery images if provided
            if ($request->hasFile('gallery_images')) {
                $galleryPaths = $data['gallery_images'] ?? $product->gallery_images ?? [];

                foreach ($request->file('gallery_images') as $image) {
                    $galleryPaths[] = $image->store('products/gallery', 'public');
                }

                $data['gallery_images'] = array_values($galleryPaths);
            }

            // Update variants
            if ($request->has('variants') && is_array($request->variants)) {
                $updatedVariants = [];

                foreach ($request->variants as $index => $variantData) {
                    $existingVariant = $product->variants[$index] ?? [];

                    // Handle variant base image
                    $variantBaseImage = $existingVariant['base_image'] ?? null;

                    // Remove old variant base image if new one uploaded
                    if ($request->hasFile("variants.{$index}.base_image")) {
                        if ($variantBaseImage) {
                            Storage::disk('public')->delete($variantBaseImage);
                        }
                        $variantBaseImage = $request->file("variants.{$index}.base_image")
                            ->store('products/variants', 'public');
                    }

                    // Handle variant gallery images
                    $variantGalleryImages = $existingVariant['gallery_images'] ?? [];

                    if ($request->hasFile("variants.{$index}.gallery_images")) {
                        // Delete old gallery images
                        foreach ($variantGalleryImages as $oldImg) {
                            Storage::disk('public')->delete($oldImg);
                        }

                        $variantGalleryImages = [];
                        foreach ($request->file("variants.{$index}.gallery_images") as $image) {
                            $variantGalleryImages[] = $image->store('products/variants/gallery', 'public');
                        }
                    }

                    // Update barcode if SKU changed
                    $variantBarcode = $existingVariant['barcode'] ?? '';
                    if (!isset($existingVariant['sku_code']) || $variantData['sku_code'] !== $existingVariant['sku_code']) {
                        $variantBarcode = $this->generateBarcode(
                            $variantData['sku_code'],
                            $variantData['barcode_symbology']
                        );
                    }

                    $updatedVariants[] = [
                        'name' => $variantData['name'],
                        'color_temperature' => $existingVariant['color_temperature'] ?? null,
                        'watt' => $existingVariant['watt'] ?? null,
                        'shape' => $existingVariant['shape'] ?? null,
                        'unit' => $variantData['unit'],
                        'sku_code' => $variantData['sku_code'],
                        'barcode' => $variantBarcode,
                        'barcode_symbology' => $variantData['barcode_symbology'],
                        'mrp_price' => $variantData['mrp_price'],
                        'cost_price' => $variantData['cost_price'],
                        'dealer_price' => $variantData['dealer_price'],
                        'distributor_price' => $variantData['distributor_price'],
                        'current_stock' => $variantData['current_stock'],
                        'min_stock_alert' => $variantData['min_stock_alert'],
                        'base_image' => $variantBaseImage,
                        'gallery_images' => $variantGalleryImages,
                        'attributes' => $existingVariant['attributes'] ?? []
                    ];
                }

                $data['variants'] = $updatedVariants;
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

                $product->delete();
            }

            return response()->json([
                'success' => true,
                'message' => count($request->product_ids) . ' product(s) deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Bulk product deletion failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete products: ' . $e->getMessage()
            ], 500);
        }
    }

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
                'message' => count($request->product_ids) . ' product(s) status updated to ' . $request->status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update products status'
            ], 500);
        }
    }


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
    public function updateStock(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,_id',
                'warehouse_id' => 'required|exists:warehouses,id',
                'stock_date' => 'required|date',
                'adjustment_type' => 'required|in:add,reduce,set',
                'quantity' => 'required|integer|min:1',
                'remarks' => 'nullable|string|max:500',
            ]);

            $product = Product::findOrFail($request->product_id);
            $oldStock = $product->current_stock ?? 0;

            // Update stock based on adjustment type
            switch ($request->adjustment_type) {
                case 'add':
                    $newStock = $oldStock + $request->quantity;
                    break;
                case 'reduce':
                    $newStock = max(0, $oldStock - $request->quantity);
                    break;
                case 'set':
                    $newStock = $request->quantity;
                    break;
                default:
                    $newStock = $oldStock;
            }

            // Update product stock
            $product->current_stock = $newStock;
            $product->save();

            // Create stock history record
            StockHistory::create([
                'product_id' => $product->_id,
                'warehouse_id' => $request->warehouse_id,
                'adjustment_type' => $request->adjustment_type,
                'old_quantity' => $oldStock,
                'new_quantity' => $newStock,
                'adjustment_quantity' => $request->quantity,
                'date' => $request->stock_date,
                'remarks' => $request->remarks
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Stock updated successfully!',
                'old_stock' => $oldStock,
                'new_stock' => $newStock,
            ]);
        } catch (\Exception $e) {
            Log::error('Stock update failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update stock: ' . $e->getMessage()
            ], 500);
        }
    }
}
