<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SimpleProduct;
use App\Models\VariantProduct;
use App\Models\Category;
use Illuminate\Http\Request;

class StorefrontApiController extends Controller
{
    /**
     * Get list of published online products for Storefront Website
     */
    public function getProducts(Request $request)
    {
        try {
            $search = $request->query('search');
            $categoryId = $request->query('category_id');
            $productType = $request->query('type'); // simple, variant, or all

            // Simple Products Query (Active + Show on Website)
            $simpleQuery = SimpleProduct::with('category')
                ->where('status', 'active')
                ->where(function($q) {
                    $q->where('show_on_website', true)
                      ->orWhereNull('show_on_website');
                });

            // Variant Products Query (Active + Show on Website)
            $variantQuery = VariantProduct::with('category')
                ->where('status', 'active')
                ->where(function($q) {
                    $q->where('show_on_website', true)
                      ->orWhereNull('show_on_website');
                });

            if ($categoryId) {
                $simpleQuery->where('category_id', $categoryId);
                $variantQuery->where('category_id', $categoryId);
            }

            if ($search) {
                $simpleQuery->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('sku_code', 'like', "%{$search}%")
                      ->orWhere('brand', 'like', "%{$search}%");
                });

                $variantQuery->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('brand', 'like', "%{$search}%");
                });
            }

            $simpleProducts = ($productType === 'variant') ? collect() : $simpleQuery->get()->map(function($p) {
                $p->product_type = 'simple';
                $p->image_url = $p->base_image ? asset('storage/' . $p->base_image) : null;
                return $p;
            });

            $variantProducts = ($productType === 'simple') ? collect() : $variantQuery->get()->map(function($p) {
                $p->product_type = 'variant';
                $p->image_url = $p->base_image ? asset('storage/' . $p->base_image) : null;
                return $p;
            });

            $allProducts = $simpleProducts->concat($variantProducts)->sortByDesc('created_at')->values();

            return response()->json([
                'success' => true,
                'total' => $allProducts->count(),
                'data' => $allProducts
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch storefront products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detailed storefront product by ID
     */
    public function getProductDetails($id)
    {
        try {
            $product = SimpleProduct::with('category')->find($id);

            if ($product) {
                if ($product->status !== 'active' || (isset($product->show_on_website) && !$product->show_on_website)) {
                    return response()->json(['success' => false, 'message' => 'Product not available online'], 404);
                }
                $product->product_type = 'simple';
                $product->image_url = $product->base_image ? asset('storage/' . $product->base_image) : null;
                $product->gallery_urls = is_array($product->gallery_images)
                    ? array_map(fn($img) => asset('storage/' . $img), $product->gallery_images)
                    : [];

                return response()->json(['success' => true, 'data' => $product]);
            }

            $variantProduct = VariantProduct::with('category')->find($id);
            if ($variantProduct) {
                if ($variantProduct->status !== 'active' || (isset($variantProduct->show_on_website) && !$variantProduct->show_on_website)) {
                    return response()->json(['success' => false, 'message' => 'Product not available online'], 404);
                }
                $variantProduct->product_type = 'variant';
                $variantProduct->image_url = $variantProduct->base_image ? asset('storage/' . $variantProduct->base_image) : null;
                $variantProduct->gallery_urls = is_array($variantProduct->gallery_images)
                    ? array_map(fn($img) => asset('storage/' . $img), $variantProduct->gallery_images)
                    : [];

                return response()->json(['success' => true, 'data' => $variantProduct]);
            }

            return response()->json(['success' => false, 'message' => 'Product not found'], 404);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get active product categories for Storefront Navigation
     */
    public function getCategories()
    {
        try {
            $categories = Category::where('status', 'active')->orderBy('name', 'asc')->get();

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
