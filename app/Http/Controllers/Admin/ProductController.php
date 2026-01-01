<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
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
        $products = Product::with(['category', 'brand'])
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

        $brands = Brand::where('status', 'active')
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.products.create', compact('categories', 'brands'));
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
            'brand_id' => 'nullable|exists:brands,id',
            'body_type' => 'required|string|max:100',
            'warranty' => 'required|string|max:100',
            'status' => 'required|in:active,inactive',

            // Pricing & Tax
            'mrp_price' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'hsn_code' => 'required|string|max:50',
            'gst' => 'required|numeric|min:0|max:100',

            // Variants (Optional)
            'sku_code' => 'nullable|string|max:100',
            'watt' => 'nullable|string|max:50',
            'shape' => 'nullable|string|max:100',
            'color_temperature' => 'nullable|string|max:100',
            'cutting_size' => 'nullable|string|max:100',
            'unit' => 'nullable|string|max:50',

            // Pricing Variants (Optional)
            'cost_price' => 'nullable|numeric|min:0',
            'dealer_price' => 'nullable|numeric|min:0',
            'distributor_price' => 'nullable|numeric|min:0',

            // Stock Management
            'opening_stock' => 'nullable|integer|min:0',
            'min_stock_alert' => 'nullable|integer|min:0',

            // Images
            'base_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

            // Description
            'description' => 'required|string',
        ]);

        try {
            // Upload base image
            $baseImagePath = null;
            if ($request->hasFile('base_image')) {
                $baseImagePath = $request->file('base_image')->store('products', 'public');
            }

            // Upload gallery images
            $galleryPaths = [];
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $image) {
                    $galleryPaths[] = $image->store('products/gallery', 'public');
                }
            }

            // Create product
            $product = Product::create([
                // Basic Information
                'name' => $request->name,
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'body_type' => $request->body_type,
                'warranty' => $request->warranty,
                'status' => $request->status,

                // Pricing & Tax
                'mrp_price' => $request->mrp_price,
                'price' => $request->price,
                'hsn_code' => $request->hsn_code,
                'gst' => $request->gst,

                // Variants
                'sku_code' => $request->sku_code,
                'watt' => $request->watt,
                'shape' => $request->shape,
                'color_temperature' => $request->color_temperature,
                'cutting_size' => $request->cutting_size,
                'unit' => $request->unit,

                // Pricing Variants
                'cost_price' => $request->cost_price,
                'dealer_price' => $request->dealer_price,
                'distributor_price' => $request->distributor_price,

                // Stock Management
                'opening_stock' => $request->opening_stock ?? 0,
                'current_stock' => $request->opening_stock ?? 0, // Set current stock to opening stock
                'min_stock_alert' => $request->min_stock_alert ?? 10,

                // Images
                'base_image' => $baseImagePath,
                'gallery_images' => $galleryPaths,

                // Description
                'short_description' => $request->short_description,
                'description' => $request->description,
            ]);

            return redirect()->route('admin.products.index')
                ->with('success', 'Product created successfully!');

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
     * Show the form for editing the product
     */
    public function edit($id)
    {
        $product = Product::findOrFail($id);

        $categories = Category::where('status', 'active')
            ->orderBy('name', 'asc')
            ->get();

        $brands = Brand::where('status', 'active')
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.products.edit', compact('product', 'categories', 'brands'));
    }

    /**
     * Update the product
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        // Validation
        $validated = $request->validate([
            // Basic Information
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'body_type' => 'required|string|max:100',
            'warranty' => 'required|string|max:100',
            'status' => 'required|in:active,inactive',

            // Pricing & Tax
            'mrp_price' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'hsn_code' => 'required|string|max:50',
            'gst' => 'required|numeric|min:0|max:100',

            // Variants (Optional)
            'sku_code' => 'nullable|string|max:100',
            'watt' => 'nullable|string|max:50',
            'shape' => 'nullable|string|max:100',
            'color_temperature' => 'nullable|string|max:100',
            'cutting_size' => 'nullable|string|max:100',
            'unit' => 'nullable|string|max:50',

            // Pricing Variants (Optional)
            'cost_price' => 'nullable|numeric|min:0',
            'dealer_price' => 'nullable|numeric|min:0',
            'distributor_price' => 'nullable|numeric|min:0',

            // Stock Management
            'current_stock' => 'nullable|integer|min:0',
            'min_stock_alert' => 'nullable|integer|min:0',

            // Images
            'base_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

            // Description
            'short_description' => 'required|string|max:500',
            'description' => 'required|string',
        ]);

        try {
            $data = $request->except(['base_image', 'gallery_images']);

            // Update base image if provided
            if ($request->hasFile('base_image')) {
                // Delete old image
                if ($product->base_image) {
                    Storage::disk('public')->delete($product->base_image);
                }
                $data['base_image'] = $request->file('base_image')->store('products', 'public');
            }

            // Update gallery images if provided
            if ($request->hasFile('gallery_images')) {
                // Delete old gallery images
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

            // Update product
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

            // Delete product
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
            $product = Product::with(['category', 'brand'])->findOrFail($id);
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
     * Bulk update products status (AJAX)
     */


    /**
     * Get low stock products
     */
    public function lowStockProducts()
    {
        $products = Product::lowStock()
            ->with(['category', 'brand'])
            ->get();

        return view('admin.products.low-stock', compact('products'));
    }

    /**
     * Get out of stock products
     */
    public function outOfStockProducts()
    {
        $products = Product::outOfStock()
            ->with(['category', 'brand'])
            ->get();

        return view('admin.products.out-of-stock', compact('products'));
    }
    /**
 * Bulk delete products
 */
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
        }

        // Delete products
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
