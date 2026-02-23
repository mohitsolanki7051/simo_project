<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SimpleProduct;
use App\Models\VariantProduct;
use App\Models\Category;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\WarehouseMovement;
use Illuminate\Support\Facades\DB;
use App\Models\PricingSetting;
use App\Models\StockHistory;
use App\Models\Attribute;
use App\Models\AttributeItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use MongoDB\BSON\ObjectId;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
class ProductController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 20);
        $search = $request->input('search', '');
        $status = $request->input('status', 'all');
        $productType = $request->input('product_type', 'all');

        // Initialize empty collections
        $simpleProducts = collect();
        $variantProducts = collect();

        // Get simple products based on filters
        if ($productType === 'all' || $productType === 'simple') {
            $simpleQuery = SimpleProduct::with('category');

            if (!empty($search)) {
                $simpleQuery->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('sku_code', 'LIKE', "%{$search}%");
                });
            }

            if ($status !== 'all') {
                $simpleQuery->where('status', $status);
            }

            $simpleProducts = $simpleQuery->get()->map(function($product) {
                $product->product_type = 'simple';
                return $product;
            });
        }

        // Get variant products based on filters
        if ($productType === 'all' || $productType === 'variant') {
            $variantQuery = VariantProduct::with(['category', 'warehouse']);

            if (!empty($search)) {
                $variantQuery->where('name', 'LIKE', "%{$search}%");
            }

            if ($status !== 'all') {
                $variantQuery->where('status', $status);
            }

            $variantProducts = $variantQuery->get()->map(function($product) {
                $product->product_type = 'variant';
                return $product;
            });
        }

        // Merge and sort
        $allProducts = $simpleProducts->concat($variantProducts)
            ->sortByDesc('created_at')
            ->values();

        // Manual pagination for MongoDB
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentPageItems = $allProducts->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $products = new LengthAwarePaginator(
            $currentPageItems,
            $allProducts->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $warehouses = Warehouse::where('status', 'active')->orderBy('name', 'asc')->get();
        $defaultWarehouse = Warehouse::where('status', 'active')->first();
        $pricing = PricingSetting::first();

        $totalProducts = $allProducts->count();
        $totalSimple = SimpleProduct::count(); // Direct count
        $totalVariant = VariantProduct::count(); // Direct count

        return view('admin.products.index', compact(
            'products',
            'warehouses',
            'defaultWarehouse',
            'pricing',
            'search',
            'status',
            'perPage',
            'totalProducts',
            'totalSimple',
            'totalVariant',
            'productType'
        ));
    }


    public function create()
    {
        $categories = Category::where('status', 'active')->orderBy('name', 'asc')->get();
        $warehouses = Warehouse::where('status', 'active')->orderBy('name', 'asc')->get();

        // Get all active attribute types
        $attributeTypes = Attribute::where('status', 'active')
            ->orderBy('type', 'asc')
            ->get();

        return view('admin.products.create', compact('categories', 'warehouses', 'attributeTypes'));
    }

    public function createSimple()
    {
        $categories = Category::where('status', 'active')->orderBy('name', 'asc')->get();
        $warehouses = Warehouse::where('status', 'active')->orderBy('name', 'asc')->get();

        return view('admin.products.create-simple', compact('categories', 'warehouses'));
    }

    public function checkSkuAvailability(Request $request)
    {
        try {
            $sku = $request->input('sku_code');
            $productId = $request->input('product_id');
            $productType = $request->input('product_type', 'simple');

            if (empty($sku)) {
                return response()->json([
                    'available' => true,
                    'message' => ''
                ]);
            }

            if ($productType === 'simple') {
                // Check in simple products
                $query = SimpleProduct::where('sku_code', $sku);
                if ($productId) {
                    $query->where('_id', '!=', $productId);
                }
                $exists = $query->exists();

                if ($exists) {
                    return response()->json([
                        'available' => false,
                        'message' => '⚠️ This SKU code is already used in another simple product'
                    ]);
                }

                // Also check in variant products' variants
                $variantProducts = VariantProduct::all();
                foreach ($variantProducts as $product) {
                    if ($product->variants && is_array($product->variants)) {
                        foreach ($product->variants as $variant) {
                            if (isset($variant['sku_code']) && $variant['sku_code'] === $sku) {
                                return response()->json([
                                    'available' => false,
                                    'message' => '⚠️ This SKU code is already used in a variant product'
                                ]);
                            }
                        }
                    }
                }
            } else {
                // Check in variant products' variants
                $allVariantProducts = VariantProduct::all();
                foreach ($allVariantProducts as $product) {
                    // Skip current product if editing
                    if ($productId && $product->_id == $productId) {
                        continue;
                    }

                    if ($product->variants && is_array($product->variants)) {
                        foreach ($product->variants as $variant) {
                            if (isset($variant['sku_code']) && $variant['sku_code'] === $sku) {
                                return response()->json([
                                    'available' => false,
                                    'message' => '⚠️ This SKU code is already used in another variant'
                                ]);
                            }
                        }
                    }
                }

                // Also check in simple products
                if (SimpleProduct::where('sku_code', $sku)->exists()) {
                    return response()->json([
                        'available' => false,
                        'message' => '⚠️ This SKU code is already used in a simple product'
                    ]);
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

    public function storeSimple(Request $request)
    {
        $validated = $request->validate([
            // Basic
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'brand' => 'required|string|max:100',
            'body_type' => 'required|string|max:100',
            'warranty_duration' => 'nullable|integer|min:1',
            'warranty_unit' => 'nullable|in:year,month',
            'status' => 'required|in:active,inactive',

            // SKU / Barcode
            'sku_code' => 'required|string|max:16|unique:simple_products,sku_code',
            'barcode_symbology' => 'required|in:CODE128',

            // Images
            'base_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

            // Description
            'description' => 'nullable|string',

            // Pricing
            'cost_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'mrp_price' => 'required|numeric|min:0',

            // Tax
            'hsn_code' => 'required|string|max:8',
            'gst' => 'required|numeric|min:0|max:100',

            // Stock (OPENING)
            'opening_stock' => 'required|integer|min:0',
            'min_stock_alert' => 'required|integer|min:0',

            'unit' => 'required|string|max:50',
        ]);

        try {
            // ✅ Auto-select main warehouse
            $mainWarehouse = Warehouse::main()->first();

            if (!$mainWarehouse) {
                throw new \Exception('Main warehouse not found. Please create a main warehouse first.');
            }

            /* ==========================
            IMAGE UPLOAD
            ========================== */
            $baseImagePath = $request->file('base_image')
                ->store('simple_products', 'public');

            $galleryPaths = [];
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $img) {
                    $galleryPaths[] = $img->store('simple_products/gallery', 'public');
                }
            }

            /* ==========================
            PRODUCT CREATE
            ========================== */
            $pricing = PricingSetting::first();

            $dealerPercentage = $pricing ? $pricing->dealer_percentage : 0;
            $distributorPercentage = $pricing ? $pricing->distributor_percentage : 0;

            $dealerPrice = $request->mrp_price - ($request->mrp_price * $dealerPercentage / 100);
            $distributorPrice = $request->mrp_price - ($request->mrp_price * $distributorPercentage / 100);
            $product = SimpleProduct::create([
                'type' => 'simple',
                'name' => $request->name,
                'category_id' => $request->category_id,
                'brand' => $request->brand,
                'body_type' => $request->body_type,

                'warranty_duration' => (int) $request->warranty_duration,
                'warranty_unit' => $request->warranty_unit,
                'status' => $request->status,

                'sku_code' => $request->sku_code,
                'barcode' => $this->generateBarcode($request->sku_code),
                'barcode_symbology' => $request->barcode_symbology,

                'cost_price' => $request->cost_price,
                'sale_price' => $request->sale_price,
                'mrp_price' => $request->mrp_price,
                'dealer_price' => round($dealerPrice, 2),
                'distributor_price' => round($distributorPrice, 2),

                'hsn_code' => $request->hsn_code,
                'gst' => $request->gst,
                'unit' => $request->unit,

                'base_image' => $baseImagePath,
                'gallery_images' => $galleryPaths,
                'description' => $request->description,
            ]);

            /* ==========================
            CURRENT STOCK (WAREHOUSE_STOCKS) - Main warehouse mai
            ========================== */
            WarehouseStock::create([
                'product_id' => $product->_id,
                'product_type' => 'simple',
                'warehouse_id' => $mainWarehouse->id,
                'quantity' => (int) $request->opening_stock,
                'min_stock_alert' => (int) $request->min_stock_alert,
            ]);

            /* ==========================
            STOCK HISTORY (WAREHOUSE_MOVEMENTS) - Main warehouse mai
            ========================== */
            WarehouseMovement::create([
                'product_id' => $product->_id,
                 'product_type' => 'simple',
                'warehouse_id' => $mainWarehouse->id,
                'type' => 'opening',
                'quantity' => (int) $request->opening_stock,
            ]);

            // ❌ COMMIT REMOVE KAR DIYA
            // DB::commit();

            return redirect()
                ->route('admin.products.index')
                ->with('success', 'Simple product created successfully in main warehouse.');

        } catch (\Exception $e) {


            Log::error('Simple Product Create Error: ' . $e->getMessage());

            // ❗ IMPORTANT: Images manually delete karo agar product create nahi hua
            if (isset($baseImagePath) && Storage::disk('public')->exists($baseImagePath)) {
                Storage::disk('public')->delete($baseImagePath);
            }

            if (isset($galleryPaths) && is_array($galleryPaths)) {
                foreach ($galleryPaths as $path) {
                    if (Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                    }
                }
            }

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            // Basic
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'warranty_duration' => 'nullable|integer|min:1',
            'warranty_unit' => 'nullable|in:year,month',
            'status' => 'required|in:active,inactive',
            // Tax
            'hsn_code' => 'required|string|max:8',
            'gst' => 'required|numeric|min:0|max:100',

            // Images
            'base_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

            // Description
            'description' => 'nullable|string',

            // Variants
            'variants' => 'required|array|min:1',
            'variants.*.name' => 'required|string|max:255',
            'variants.*.attributes' => 'required',
            'variants.*.unit' => 'required|string|max:50',
            'variants.*.sku_code' => 'required|string|max:16|distinct',
            'variants.*.barcode_symbology' => 'required|in:CODE128',
            'variants.*.cost_price' => 'required|numeric|min:0',
            'variants.*.sale_price' => 'required|numeric|min:0',
            'variants.*.mrp_price' => 'required|numeric|min:0',
            'variants.*.opening_stock' => 'required|integer|min:0',
            'variants.*.min_stock_alert' => 'required|integer|min:0',
            'variants.*.base_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'variants.*.gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            /* =========================
            MAIN WAREHOUSE
            ========================= */
            $mainWarehouse = Warehouse::main()->first();

            if (!$mainWarehouse) {
                throw new \Exception('Main warehouse not found. Please create a main warehouse first.');
            }

            /* =========================
            MAIN PRODUCT IMAGES
            ========================= */
            $baseImagePath = $request->file('base_image')
                ->store('variant_products', 'public');

            $galleryPaths = [];
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $img) {
                    $galleryPaths[] = $img->store('variant_products/gallery', 'public');
                }
            }

            /* =========================
            BUILD VARIANTS (CORRECT FORMAT)
            ========================= */
            $variantsArray = [];
            $pricing = PricingSetting::first();
            $dealerPercentage = $pricing ? $pricing->dealer_percentage : 0;
            $distributorPercentage = $pricing ? $pricing->distributor_percentage : 0;

            foreach ($request->variants as $i => $variantData) {
                $variantId = new ObjectId(); // REAL VARIANT ID

                // Parse attributes
                $attributes = is_string($variantData['attributes'])
                    ? json_decode($variantData['attributes'], true)
                    : $variantData['attributes'];

                // Handle variant base image
                $variantBaseImage = null;
                if ($request->hasFile("variants.$i.base_image")) {
                    $variantBaseImage = $request->file("variants.$i.base_image")
                        ->store('variant_products/variants', 'public');
                }

                // Handle variant gallery images
                $variantGallery = [];
                if ($request->hasFile("variants.$i.gallery_images")) {
                    foreach ($request->file("variants.$i.gallery_images") as $img) {
                        $variantGallery[] = $img->store('variant_products/variants/gallery', 'public');
                    }
                }
                $mrp = (float) $variantData['mrp_price'];

                $dealerPrice = $mrp - ($mrp * $dealerPercentage / 100);
                $distributorPrice = $mrp - ($mrp * $distributorPercentage / 100);


                // Create variant array with proper data types
                $variantArray = [
                    '_id' => $variantId,
                    'name' => $variantData['name'],
                    'attributes' => $attributes,
                    'unit' => $variantData['unit'],
                    'sku_code' => $variantData['sku_code'],
                    'barcode' => $this->generateBarcode($variantData['sku_code']),
                    'barcode_symbology' => $variantData['barcode_symbology'],
                    'cost_price' => (float) $variantData['cost_price'],
                    'sale_price' => (float) $variantData['sale_price'],
                    'mrp_price' => (float) $variantData['mrp_price'],
                    'dealer_price' => round($dealerPrice, 2),
                    'distributor_price' => round($distributorPrice, 2),
                    'base_image' => $variantBaseImage,
                    'gallery_images' => $variantGallery,
                    'created_at' => now()->toDateTimeString(),
                    'updated_at' => now()->toDateTimeString(),
                ];

                $variantsArray[] = $variantArray;
                WarehouseStock::create([
                    'product_id' => null,
                    'product_type' => 'variant', // IMPORTANT FIX
                    'warehouse_id' => $mainWarehouse->id,
                    'variant_id' => (string) $variantId,
                    'quantity' => (int) $variantData['opening_stock'],
                    'min_stock_alert' => (int) $variantData['min_stock_alert'],
                ]);
                WarehouseMovement::create([
                    'product_id' => null,
                    'product_type' => 'variant', // IMPORTANT FIX
                    'warehouse_id' => $mainWarehouse->id,
                    'variant_id' => (string) $variantId,
                    'type' => 'opening',
                    'quantity' => (int) $variantData['opening_stock'],
                ]);
            }

            /* =========================
            CREATE PRODUCT IN VARIANT_PRODUCTS TABLE
            ========================= */
            $product = VariantProduct::create([
                'name' => $request->name,
                'category_id' => $request->category_id,
                'brand' => 'Simko',
                'warranty_duration' => (int) $request->warranty_duration,
                'warranty_unit' => $request->warranty_unit,
                'status' => $request->status,
                'hsn_code' => $request->hsn_code,
                'gst' => (float) $request->gst,
                'base_image' => $baseImagePath,
                'gallery_images' => $galleryPaths,
                'description' => $request->description,
                'variants' => $variantsArray,
            ]);

            /* =========================
            UPDATE WAREHOUSE_STOCKS AND WAREHOUSE_MOVEMENTS
            WITH PRODUCT_ID
            ========================= */
            WarehouseStock::whereIn('variant_id', array_map(function($v) {
                return (string) $v['_id'];
            }, $variantsArray))->update([
                'product_id' => $product->_id,
                'product_type' => 'variant' // CONFIRM product_type
            ]);

            WarehouseMovement::whereIn('variant_id', array_map(function($v) {
                return (string) $v['_id'];
            }, $variantsArray))->update([
                'product_id' => $product->_id,
                'product_type' => 'variant' // CONFIRM product_type
            ]);

            return redirect()
                ->route('admin.products.index')
                ->with('success', 'Variant product created successfully in main warehouse.');

        } catch (\Exception $e) {
            Log::error('Variant Product Create Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            // Cleanup images
            if (isset($baseImagePath) && Storage::disk('public')->exists($baseImagePath)) {
                Storage::disk('public')->delete($baseImagePath);
            }

            if (isset($galleryPaths) && is_array($galleryPaths)) {
                foreach ($galleryPaths as $path) {
                    if (Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                    }
                }
            }

            // Cleanup variant images
            if (isset($variantsArray) && is_array($variantsArray)) {
                foreach ($variantsArray as $variant) {
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

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    private function generateBarcode(string $sku): string
    {
        // CODE128 supports alphanumeric characters
        // Keep barcode identical to SKU (cleaned)
        return strtoupper(
            preg_replace('/[^A-Za-z0-9]/', '', $sku)
        );
    }


    public function edit($id)
    {
        // First check if it's a simple product
        $simpleProduct = SimpleProduct::find($id);
        if ($simpleProduct) {
            $categories = Category::where('status', 'active')->orderBy('name', 'asc')->get();

            // ✅ Get opening stock from FIRST warehouse movement
            $openingStock = 0;
            $openingStockMovement = WarehouseMovement::where('product_id', $simpleProduct->_id)
                ->where('type', 'opening')
                ->orderBy('created_at', 'asc')
                ->first();

            if ($openingStockMovement) {
                $openingStock = $openingStockMovement->quantity;
            }

            return view('admin.products.edit-simple', compact('simpleProduct', 'categories', 'openingStock'));
        }

        // If not simple, check variant product
        $variantProduct = VariantProduct::findOrFail($id);
        $variantOpeningStocks = [];

        $openingMovements = WarehouseMovement::where('product_id', $variantProduct->_id)
            ->where('product_type', 'variant')
            ->where('type', WarehouseMovement::TYPE_OPENING)
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($openingMovements as $movement) {
            if ($movement->variant_id) {
                $variantOpeningStocks[(string) $movement->variant_id] = $movement->quantity;
            }
        }
        // ✅ FIX: Ensure variants is an array
        if (is_string($variantProduct->variants)) {
            try {
                $variantProduct->variants = json_decode($variantProduct->variants, true);
            } catch (\Exception $e) {
                $variantProduct->variants = [];
                Log::error('Error decoding variants for edit: ' . $e->getMessage());
            }
        }

        $categories = Category::where('status', 'active')->orderBy('name', 'asc')->get();
        $warehouses = Warehouse::where('status', 'active')->orderBy('name', 'asc')->get();
        $attributeTypes = Attribute::where('status', 'active')
            ->orderBy('type', 'asc')
            ->get();

        return view('admin.products.edit', compact('variantProduct', 'categories', 'warehouses', 'attributeTypes','variantOpeningStocks'));
    }
    public function show($id)
    {
        try {
            // First check if it's a simple product
            $simpleProduct = SimpleProduct::with(['category', 'warehouse'])->find($id);

            if ($simpleProduct) {
                return view('admin.products.show', [
                    'product' => $simpleProduct
                ]);
            }

            // If not simple, check variant product
            $variantProduct = VariantProduct::with(['category', 'warehouse'])->findOrFail($id);

            return view('admin.products.show', [
                'product' => $variantProduct
            ]);

        } catch (\Exception $e) {
            Log::error('Product view failed: ' . $e->getMessage());
            return redirect()->route('admin.products.index')
                ->with('error', 'Product not found');
        }
    }

    public function update(Request $request, $id)
    {
        // First check if it's a simple product
        $simpleProduct = SimpleProduct::find($id);
        if ($simpleProduct) {
            return $this->updateSimpleProduct($request, $simpleProduct);
        }

        // If not simple, check variant product
        $variantProduct = VariantProduct::findOrFail($id);
        return $this->updateVariantProduct($request, $variantProduct);
    }

    private function updateSimpleProduct(Request $request, $product)
    {
        // ✅ Get current warehouse from warehouse_stocks
        $warehouseStock = WarehouseStock::where('product_id', $product->_id)->first();
        $currentWarehouseId = $warehouseStock ? $warehouseStock->warehouse_id : null;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'brand' => 'required|string|max:100',
            'body_type' => 'required|string|max:100',
            'warranty_duration' => 'nullable|integer|min:1',
            'warranty_unit' => 'nullable|in:year,month',
            'status' => 'required|in:active,inactive',
            'sku_code' => 'required|string|max:16|unique:simple_products,sku_code,' . $product->id,
            'barcode_symbology' => 'required|in:CODE128',
            'base_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
            'cost_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'mrp_price' => 'required|numeric|min:0',
            'hsn_code' => 'required|string|max:8',
            'gst' => 'required|numeric|min:0|max:100',
            'min_stock_alert' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
        ]);

        try {
            $data = $request->except(['base_image', 'gallery_images', 'remove_base_image', 'remove_gallery_images']);

            // ✅ Update barcode if SKU or symbology changed
            if (
                $request->sku_code !== $product->sku_code ||
                $request->barcode_symbology !== $product->barcode_symbology
            ) {
                $data['barcode'] = $this->generateBarcode($request->sku_code);
            }
            if ($request->mrp_price != $product->mrp_price) {
                $pricing = PricingSetting::first();

                $dealerPercentage = $pricing ? $pricing->dealer_percentage : 0;
                $distributorPercentage = $pricing ? $pricing->distributor_percentage : 0;

                $data['dealer_price'] =
                    round($request->mrp_price - ($request->mrp_price * $dealerPercentage / 100), 2);

                $data['distributor_price'] =
                    round($request->mrp_price - ($request->mrp_price * $distributorPercentage / 100), 2);
            }
            // ✅ Warranty fields
            $data['warranty_duration'] = (int) $request->warranty_duration;
            $data['warranty_unit'] = $request->warranty_unit;

            // ✅ Handle base image removal
            if ($request->has('remove_base_image') && $request->remove_base_image == '1') {
                if ($product->base_image) {
                    Storage::disk('public')->delete($product->base_image);
                }
                $data['base_image'] = null;
            }

            // ✅ Update base image if provided
            if ($request->hasFile('base_image')) {
                if ($product->base_image) {
                    Storage::disk('public')->delete($product->base_image);
                }
                $data['base_image'] = $request->file('base_image')->store('simple_products', 'public');
            }

            // ✅ Handle gallery images removal
            $currentGallery = $product->gallery_images ?? [];

            if ($request->has('remove_gallery_images')) {
                $removeIndices = $request->remove_gallery_images;
                foreach ($removeIndices as $index) {
                    if (isset($currentGallery[$index])) {
                        Storage::disk('public')->delete($currentGallery[$index]);
                        unset($currentGallery[$index]);
                    }
                }
                $currentGallery = array_values($currentGallery); // Re-index array
            }

            // ✅ Update gallery images if provided
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $image) {
                    $currentGallery[] = $image->store('simple_products/gallery', 'public');
                }
            }

            $data['gallery_images'] = $currentGallery;

            // ✅ Update product (including min_stock_alert)
            $data['min_stock_alert'] = (int) $request->min_stock_alert; // ✅ Make sure this is included
            $product->update($data);

            // ✅ Update warehouse stock (ONLY min_stock_alert)
            if ($warehouseStock) {

                $warehouseStock->update([
                    'min_stock_alert' => (int) $request->min_stock_alert,
                ]);
            }

            return redirect()->route('admin.products.index')
                ->with('success', 'Simple product updated successfully!');

        } catch (\Exception $e) {
            Log::error('Simple product update failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to update product. Please try again.');
        }
    }
public function updateprice(Request $request)
{
    $request->validate([
        'dealer_percentage' => 'required|numeric|min:0|max:100',
        'distributor_percentage' => 'required|numeric|min:0|max:100',
    ]);

    $pricing = PricingSetting::first(); // 👈 sirf ek record lena

    if ($pricing) {
        // 👉 SAME record update hoga
        $pricing->update([
            'dealer_percentage' => (float) $request->dealer_percentage,
            'distributor_percentage' => (float) $request->distributor_percentage,
        ]);
    } else {
        // 👉 sirf pehli baar create
        PricingSetting::create([
            'dealer_percentage' => (float) $request->dealer_percentage,
            'distributor_percentage' => (float) $request->distributor_percentage,
        ]);
    }

    return back()->with('success', 'Pricing updated successfully');
}



    private function updateVariantProduct(Request $request, $product)
    {
        $mainWarehouse = Warehouse::main()->first();
        if (!$mainWarehouse) {
            return back()->withInput()->with('error', 'Main warehouse not found');
        }

        // Validation for variant product
        $validated = $request->validate([
            // Basic Information
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'warranty_duration' => 'nullable|integer|min:1',
            'warranty_unit' => 'nullable|in:year,month',
            'status' => 'required|in:active,inactive',



            // Tax
            'hsn_code' => 'required|string|max:8',
            'gst' => 'required|numeric|min:0|max:100',

            // Images
            'base_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

            // Description
            'description' => 'nullable|string',

            // Variants
            'variants' => 'required|array|min:1',
            'variants.*._id' => 'nullable|string', // Allow string for existing IDs
            'variants.*.name' => 'required|string|max:255',
            'variants.*.attributes' => 'required|array',
            'variants.*.unit' => 'required|string|max:50',
            'variants.*.sku_code' => 'required|string|max:16',
            'variants.*.barcode_symbology' => 'required|in:CODE128',
            'variants.*.cost_price' => 'required|numeric|min:0',
            'variants.*.sale_price' => 'required|numeric|min:0|gt:variants.*.cost_price',
            'variants.*.mrp_price' => 'required|numeric|min:0|gt:variants.*.sale_price',
            'variants.*.opening_stock' => 'required|integer|min:0',
            'variants.*.min_stock_alert' => 'required|integer|min:0',
            'variants.*.base_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'variants.*.gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $data = $request->except(['base_image', 'gallery_images', 'remove_base_image', 'remove_gallery_images', 'variants']);

            // Add warranty fields with integer cast
            $data['warranty_duration'] = (int) $request->warranty_duration;
            $data['warranty_unit'] = $request->warranty_unit;

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
                $data['base_image'] = $request->file('base_image')->store('variant_products', 'public');
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
                    $galleryPaths[] = $image->store('variant_products/gallery', 'public');
                }

                $data['gallery_images'] = array_values($galleryPaths);
            }

            // ✅ CRITICAL FIX: Prepare variants array properly
            $updatedVariants = [];
            $pricing = PricingSetting::first();
           $dealerPercentage = $pricing ? $pricing->dealer_percentage : 0;
            $distributorPercentage = $pricing ? $pricing->distributor_percentage : 0;

            if ($request->has('variants') && is_array($request->variants)) {
                foreach ($request->variants as $index => $variantData) {
                    // Get variant ID from request
                    $variantId = $variantData['_id'] ?? null;

                    // Find existing variant by ID
                    $existingVariant = null;
                    if ($variantId) {
                        foreach ($product->variants as $v) {
                            $existingVariantId = $v['_id'] ?? null;
                            if ($existingVariantId) {
                                // Convert both to string for comparison
                                if ((string) $existingVariantId === $variantId) {
                                    $existingVariant = $v;
                                    break;
                                }
                            }
                        }
                    }

                    // Parse attributes from request
                    $variantAttributes = [];
                    if (isset($variantData['attributes']) && is_array($variantData['attributes'])) {
                        foreach ($variantData['attributes'] as $attr) {
                            if (is_array($attr) && isset($attr['type']) && isset($attr['value'])) {
                                $variantAttributes[] = [
                                    'type' => $attr['type'],
                                    'displayName' => $attr['displayName'] ?? $attr['type'],
                                    'value' => $attr['value']
                                ];
                            }
                        }
                    }

                    // If it's a new variant (starts with 'new-'), create new ObjectId
                    if ($variantId && !str_starts_with($variantId, 'new-')) {
                        // Existing variant - use the existing ID (it might be string, convert to ObjectId if needed)
                        $variantObjectId = $variantId;
                        // If it's a MongoDB ObjectId string, convert it
                        if (preg_match('/^[0-9a-fA-F]{24}$/', $variantId)) {
                            $variantObjectId = new ObjectId($variantId);
                        }
                    } else {
                        // New variant - create new ObjectId
                        $variantObjectId = new ObjectId();
                    }

                    // Handle variant base image
                    $variantBaseImage = $existingVariant['base_image'] ?? null;

                    // Remove variant base image if requested
                    if ($request->has("remove_variant_base_image.$index") && $request->input("remove_variant_base_image.$index") == '1') {
                        if ($variantBaseImage) {
                            Storage::disk('public')->delete($variantBaseImage);
                        }
                        $variantBaseImage = null;
                    }

                    // Update variant base image if provided
                    if ($request->hasFile("variants.$index.base_image")) {
                        if ($variantBaseImage) {
                            Storage::disk('public')->delete($variantBaseImage);
                        }
                        $variantBaseImage = $request->file("variants.$index.base_image")
                            ->store('variant_products/variants', 'public');
                    }

                    // Handle variant gallery images
                    $variantGalleryImages = $existingVariant['gallery_images'] ?? [];

                    // Remove specific gallery images
                    if ($request->has("remove_variant_gallery_images.$index")) {
                        $removeIndices = $request->input("remove_variant_gallery_images.$index");
                        foreach ($removeIndices as $imgIndex) {
                            if (isset($variantGalleryImages[$imgIndex])) {
                                Storage::disk('public')->delete($variantGalleryImages[$imgIndex]);
                                unset($variantGalleryImages[$imgIndex]);
                            }
                        }
                        $variantGalleryImages = array_values($variantGalleryImages);
                    }

                    // Add new gallery images
                    if ($request->hasFile("variants.$index.gallery_images")) {
                        foreach ($request->file("variants.$index.gallery_images") as $image) {
                            $variantGalleryImages[] = $image->store('variant_products/variants/gallery', 'public');
                        }
                    }

                    // Generate barcode if SKU or symbology changed
                    $variantBarcode = $existingVariant['barcode'] ?? '';
                    if (!isset($existingVariant['sku_code']) ||
                        $variantData['sku_code'] !== ($existingVariant['sku_code'] ?? '') ||
                        $variantData['barcode_symbology'] !== ($existingVariant['barcode_symbology'] ?? '')) {
                        $variantBarcode = $this->generateBarcode($variantData['sku_code']);

                    }

                    // Get opening stock from request (should be readonly in form)
                    $openingStock = (int) ($variantData['opening_stock'] ?? 0);

                    // If it's a new variant with 0 opening stock, use current stock from warehouse if exists
                    if ($openingStock === 0 && $existingVariant) {
                        $warehouseStock = WarehouseStock::where('product_id', $product->_id)
                            ->where('product_type', 'variant')
                            ->where('variant_id', (string) $existingVariant['_id'])
                            ->first();

                        if ($warehouseStock) {
                            $openingStock = $warehouseStock->quantity;
                        }
                    }
                   $mrp = (float) $variantData['mrp_price'];
                    $oldMrp = (float) ($existingVariant['mrp_price'] ?? 0);

                    if ($mrp != $oldMrp) {
                        $dealerPrice = $mrp - ($mrp * $dealerPercentage / 100);
                        $distributorPrice = $mrp - ($mrp * $distributorPercentage / 100);
                    } else {
                        $dealerPrice = $existingVariant['dealer_price'] ?? 0;
                        $distributorPrice = $existingVariant['distributor_price'] ?? 0;
                    }

                    // ✅ CRITICAL FIX: Create variant array with proper _id format
                    $variant = [
                        '_id' => $variantObjectId, // This is now properly handled
                        'name' => $variantData['name'],
                        'attributes' => $variantAttributes,
                        'unit' => $variantData['unit'],
                        'sku_code' => $variantData['sku_code'],
                        'barcode' => $variantBarcode,
                        'barcode_symbology' => $variantData['barcode_symbology'],
                        'cost_price' => (float) $variantData['cost_price'],
                        'sale_price' => (float) $variantData['sale_price'],
                        'mrp_price' => (float) $variantData['mrp_price'],
                        'dealer_price' => round($dealerPrice, 2),
                        'distributor_price' => round($distributorPrice, 2),
                        'opening_stock' => $openingStock,
                        'min_stock_alert' => (int) $variantData['min_stock_alert'],
                        'base_image' => $variantBaseImage,
                        'gallery_images' => $variantGalleryImages,
                        'created_at' => $existingVariant['created_at'] ?? now()->toDateTimeString(),
                        'updated_at' => now()->toDateTimeString(),
                    ];

                    $updatedVariants[] = $variant;

                    // Update warehouse stock
                    $warehouseStock = WarehouseStock::where('product_id', $product->_id)
                        ->where('product_type', 'variant')
                        ->where('variant_id', (string) $variant['_id'])
                        ->first();

                    if ($warehouseStock) {
                        // Update existing warehouse stock
                        $warehouseStock->update([
                            'min_stock_alert' => (int) $variantData['min_stock_alert'],
                            'product_type' => 'variant'
                        ]);

                        // Note: We don't update the quantity here because opening stock is readonly
                        // Quantity changes should happen through stock adjustment feature
                    } else {
                        // Create new warehouse stock for new variants
                        WarehouseStock::create([
                            'product_id' => $product->_id,
                            'product_type' => 'variant',
                            'warehouse_id' => $mainWarehouse->id,
                            'variant_id' => (string) $variant['_id'],
                            'quantity' => $openingStock, // Use the opening stock from form
                            'min_stock_alert' => (int) $variantData['min_stock_alert'],
                        ]);

                        // Also create warehouse movement for new variants
                        if ($openingStock > 0) {
                            WarehouseMovement::create([
                                'product_id' => $product->_id,
                                'product_type' => 'variant',
                                'warehouse_id' => $mainWarehouse->id,
                                'variant_id' => (string) $variant['_id'],
                                'type' => 'opening',
                                'quantity' => $openingStock,
                            ]);
                        }
                    }
                }
            }

            // ✅ CRITICAL FIX: Update product using save() instead of update() to trigger mutator
            $product->name = $request->name;
            $product->category_id = $request->category_id;
            $product->warranty_duration = (int) $request->warranty_duration;
            $product->warranty_unit = $request->warranty_unit;
            $product->status = $request->status;
            $product->hsn_code = $request->hsn_code;
            $product->gst = (float) $request->gst;
            $product->description = $request->description;

            if (isset($data['base_image'])) {
                $product->base_image = $data['base_image'];
            }

            if (isset($data['gallery_images'])) {
                $product->gallery_images = $data['gallery_images'];
            }

            // ✅ Set variants directly - the mutator will handle JSON encoding
            $product->variants = $updatedVariants;

            $product->save();

            return redirect()->route('admin.products.index')
                ->with('success', 'Variant product updated successfully!');
        } catch (\Exception $e) {
            Log::error('Variant product update failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return back()->withInput()->with('error', 'Failed to update product: ' . $e->getMessage());
        }
    }
    private function generateVariantKey($attributes)
    {
        if (!is_array($attributes)) {
            return '';
        }

        // Sort attributes by type to ensure consistent key generation
        usort($attributes, function($a, $b) {
            return strcmp($a['type'] ?? '', $b['type'] ?? '');
        });

        $keyParts = [];
        foreach ($attributes as $attr) {
            $keyParts[] = ($attr['type'] ?? '') . ':' . ($attr['value'] ?? '');
        }

        return implode('|', $keyParts);
    }

    // REMOVED: destroy function
    // REMOVED: destroySimpleProduct function
    // REMOVED: destroyVariantProduct function

    // REMOVED: bulkDestroy function

    public function bulkUpdateStatus(Request $request)
    {
        try {
            $request->validate([
                'product_ids' => 'required|array',
                'product_types' => 'required|array',
                'status' => 'required|in:active,inactive'
            ]);

            $simpleProductIds = [];
            $variantProductIds = [];

            // Separate simple and variant product IDs
            foreach ($request->product_ids as $index => $productId) {
                if ($request->product_types[$index] === 'simple') {
                    $simpleProductIds[] = $productId;
                } else {
                    $variantProductIds[] = $productId;
                }
            }

            // Update simple products
            if (!empty($simpleProductIds)) {
                SimpleProduct::whereIn('_id', $simpleProductIds)
                    ->update(['status' => $request->status]);
            }

            // Update variant products
            if (!empty($variantProductIds)) {
                VariantProduct::whereIn('_id', $variantProductIds)
                    ->update(['status' => $request->status]);
            }

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

    // In your ProductController, update the updateStock method:
    public function updateStock(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required',
                'product_type' => 'required|in:simple,variant',
                'warehouse_id' => 'required|exists:warehouses,id',
                'adjustment_type' => 'required|in:add,reduce', // ✅ Only add/reduce
                'quantity' => 'required|integer|min:1',
                'remarks' => 'nullable|string|max:500',
                'variant_id' => 'required_if:product_type,variant',
            ]);

            $mainWarehouse = Warehouse::main()->first();
            if (!$mainWarehouse) {
                return response()->json([
                    'success' => false,
                    'message' => 'Main warehouse not found'
                ], 404);
            }

            // ✅ Force main warehouse
            $warehouseId = $mainWarehouse->id;

            // Find or create warehouse stock
            $warehouseStockQuery = WarehouseStock::where('warehouse_id', $warehouseId)
                ->where('product_id', $request->product_id)
                ->where('product_type', $request->product_type);

            if ($request->product_type === 'variant') {
                $warehouseStockQuery->where('variant_id', $request->variant_id);
            }

            $warehouseStock = $warehouseStockQuery->first();

            if (!$warehouseStock) {
                // Create warehouse stock if it doesn't exist
                $warehouseStock = WarehouseStock::create([
                    'warehouse_id' => $warehouseId,
                    'product_id' => $request->product_id,
                    'product_type' => $request->product_type,
                    'variant_id' => $request->product_type === 'variant' ? $request->variant_id : null,
                    'quantity' => 0,
                    'min_stock_alert' => 0,
                ]);
            }

            $oldStock = $warehouseStock->quantity;
            $newStock = $oldStock;

            // Calculate new stock
            switch ($request->adjustment_type) {
                case 'add':
                    $newStock = $oldStock + $request->quantity;
                    break;
                case 'reduce':
                    $newStock = max(0, $oldStock - $request->quantity);
                    break;
            }

            // Update warehouse stock
            $warehouseStock->update([
                'quantity' => $newStock,
            ]);

            // Create warehouse movement record with remarks
            WarehouseMovement::create([
                'warehouse_id' => $warehouseId,
                'product_id' => $request->product_id,
                'product_type' => $request->product_type,
                'variant_id' => $request->variant_id ?? null,
                'type' => 'adjustment',
                'quantity' => $request->quantity,
                'reference_id' => null,
                'remarks' => $request->remarks, // ✅ Remarks saved here
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Stock updated successfully!',
                'old_stock' => $oldStock,
                'new_stock' => $newStock,
                'warehouse_name' => $mainWarehouse->name,
            ]);
        } catch (\Exception $e) {
            Log::error('Stock update failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update stock: ' . $e->getMessage()
            ], 500);
        }

    }

    public function getSimpleProductMainWarehouseStock($id)
    {
        try {
            $product = SimpleProduct::findOrFail($id);
            $mainWarehouse = Warehouse::main()->first();

            if (!$mainWarehouse) {
                return response()->json([
                    'success' => false,
                    'message' => 'Main warehouse not found'
                ]);
            }

            // Get stock from warehouse_stocks
            $warehouseStock = WarehouseStock::where('product_id', $product->_id)
                ->where('product_type', 'simple')
                ->where('warehouse_id', $mainWarehouse->id)
                ->first();

            $stock = $warehouseStock ? $warehouseStock->quantity : 0;

            return response()->json([
                'success' => true,
                'main_warehouse_stock' => $stock,
                'warehouse_name' => $mainWarehouse->name
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching simple product stock: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'main_warehouse_stock' => 0
            ]);
        }
    }

    /**
     * Get variant product with main warehouse stock
     */
    public function getVariantsWithMainWarehouseStock($id)
    {
        try {
            $product = VariantProduct::findOrFail($id);
            $mainWarehouse = Warehouse::main()->first();

            if (!$mainWarehouse) {
                return response()->json([
                    'success' => false,
                    'message' => 'Main warehouse not found',
                    'variants' => $product->variants ?? []
                ]);
            }

            $variantsWithStock = [];

            if ($product->variants && is_array($product->variants)) {
                foreach ($product->variants as $variant) {
                    $variantId = $variant['_id'] ?? null;
                    if (!$variantId) {
                        continue;
                    }

                    // Get stock from warehouse_stocks
                    $warehouseStock = WarehouseStock::where('product_id', $product->_id)
                        ->where('product_type', 'variant')
                        ->where('variant_id', (string) $variantId)
                        ->where('warehouse_id', $mainWarehouse->id)
                        ->first();

                    $stock = $warehouseStock ? $warehouseStock->quantity : 0;
                    $minAlert = $warehouseStock ? $warehouseStock->min_stock_alert : 0;

                    $variant['current_stock'] = $stock;
                    $variant['min_stock_alert'] = $minAlert;

                    $variantsWithStock[] = $variant;
                }
            }

            return response()->json([
                'success' => true,
                'variants' => $variantsWithStock,
                'warehouse_name' => $mainWarehouse->name
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching variants with stock: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'variants' => $product->variants ?? []
            ]);
        }
    }
    public function getSimpleProductDetails($id)
    {
        try {
            $product = SimpleProduct::with('warehouse')->findOrFail($id);

            return response()->json([
                'success' => true,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'current_stock' => $product->current_stock,
                ],
                'warehouse' => $product->warehouse ? [
                    'id' => $product->warehouse->id,
                    'name' => $product->warehouse->name,
                ] : null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }
    }

    /**
     * Get variant product details with warehouse
     */
    public function getVariantProductDetails($id)
    {
        try {
            $product = VariantProduct::with('warehouse')->findOrFail($id);

            return response()->json([
                'success' => true,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                ],
                'warehouse' => $product->warehouse ? [
                    'id' => $product->warehouse->id,
                    'name' => $product->warehouse->name,
                ] : null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }
    }

    private function findVariantIndex($variants, $variantIdentifier)
    {
        foreach ($variants as $index => $variant) {
            // Check by _id field
            if (isset($variant['_id']) && $variant['_id'] == $variantIdentifier) {
                return $index;
            }
            // If no _id, use index
            if ($index == $variantIdentifier) {
                return $index;
            }
        }
        return -1;
    }

    public function getVariants($id)
    {
        try {
            $product = VariantProduct::findOrFail($id);

            return response()->json([
                'success' => true,
                'variants' => $product->variants ?? []
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }
    }

    /**
     * FIXED: Get attribute values based on attribute type
     */
    public function getAttributeValues(Request $request)
    {
        try {
            $request->validate([
                'attribute_type' => 'required|string'
            ]);

            Log::info('Getting attribute values for type: ' . $request->attribute_type);

            // Find the attribute by type
            $attribute = Attribute::where('type', $request->attribute_type)
                ->where('status', 'active')
                ->first();

            if (!$attribute) {
                Log::warning('Attribute type not found: ' . $request->attribute_type);
                return response()->json([
                    'success' => false,
                    'message' => 'Attribute type not found'
                ], 404);
            }

            Log::info('Found attribute with ID: ' . $attribute->id);

            // Get values from AttributeItem table where attribute_type_id matches
            // IMPORTANT: Using 'id' field instead of '_id' for MongoDB
            $attributeItems = AttributeItem::where('attribute_type_id', $attribute->id)
                ->where('status', 'active')
                ->orderBy('value', 'asc')
                ->get();

            Log::info('Found ' . $attributeItems->count() . ' attribute items');

            if ($attributeItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No values found for this attribute type',
                    'values' => []
                ]);
            }

            // Extract values
            $values = $attributeItems->pluck('value')->toArray();

            Log::info('Returning values: ' . json_encode($values));

            return response()->json([
                'success' => true,
                'values' => $values,
                'attribute_type' => $attribute->type,
                'attribute_id' => $attribute->id
            ]);

        } catch (\Exception $e) {
            Log::error('Get attribute values failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load attribute values: ' . $e->getMessage()
            ], 500);
        }
    }


    public function getReportData()
    {
        try {
            // Get all products
            $simpleProducts = SimpleProduct::all();
            $variantProducts = VariantProduct::all();

            $totalCost = 0;
            $simpleLowStockCount = 0;
            $variantLowStockCount = 0;
            $simpleProductCount = 0;
            $variantItemCount = 0;

            // Process simple products
            foreach ($simpleProducts as $product) {
                $simpleProductCount++;

                // Get ALL warehouses stock for this product
                $warehouseStocks = WarehouseStock::where('product_id', $product->_id)
                    ->where('product_type', 'simple')
                    ->get();

                $totalStock = 0;
                $isLowStock = false;

                foreach ($warehouseStocks as $warehouseStock) {
                    $stock = $warehouseStock->quantity ?? 0;
                    $minStockAlert = $warehouseStock->min_stock_alert ?? 0;
                    $totalStock += $stock;

                    // Check if low stock in ANY warehouse
                    if ($stock <= $minStockAlert && $stock > 0) { // Only count as low stock, not out of stock
                        $isLowStock = true;
                    }
                }

                // Calculate total cost (cost_price × total stock from all warehouses)
                $costPrice = $product->cost_price ?? 0;
                $totalCost += ($totalStock * $costPrice);

                // Count low stock
                if ($isLowStock) {
                    $simpleLowStockCount++;
                }
            }

            // Process variant products
            foreach ($variantProducts as $product) {
                if ($product->variants && is_array($product->variants)) {
                    foreach ($product->variants as $variant) {
                        $variantItemCount++;

                        $variantId = $variant['_id'] ?? null;
                        if (!$variantId) continue;

                        // Get ALL warehouses stock for this variant
                        $warehouseStocks = WarehouseStock::where('product_id', $product->_id)
                            ->where('product_type', 'variant')
                            ->where('variant_id', (string) $variantId)
                            ->get();

                        $totalStock = 0;
                        $isLowStock = false;

                        foreach ($warehouseStocks as $warehouseStock) {
                            $stock = $warehouseStock->quantity ?? 0;
                            $minStockAlert = $warehouseStock->min_stock_alert ?? 0;
                            $totalStock += $stock;

                            // Check if low stock in ANY warehouse
                            if ($stock <= $minStockAlert && $stock > 0) { // Only count as low stock, not out of stock
                                $isLowStock = true;
                            }
                        }

                        // Calculate total cost (cost_price × total stock from all warehouses)
                        $costPrice = $variant['cost_price'] ?? 0;
                        $totalCost += ($totalStock * $costPrice);

                        // Count low stock
                        if ($isLowStock) {
                            $variantLowStockCount++;
                        }
                    }
                }
            }

            // Format total cost with 2 decimal places
            $formattedCost = number_format($totalCost, 2);

            // Calculate totals
            $totalProducts = $simpleProductCount + $variantItemCount;
            $totalLowStock = $simpleLowStockCount + $variantLowStockCount;

            return response()->json([
                'success' => true,
                'total_cost' => $formattedCost,
                'total_products' => $totalProducts,
                'simple_products_count' => $simpleProductCount,
                'variant_items_count' => $variantItemCount,
                'simple_low_stock_count' => $simpleLowStockCount,
                'variant_low_stock_count' => $variantLowStockCount,
                'total_low_stock' => $totalLowStock
            ]);

        } catch (\Exception $e) {
            Log::error('Report data fetch failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'total_cost' => '0.00',
                'total_products' => 0,
                'simple_products_count' => 0,
                'variant_items_count' => 0,
                'simple_low_stock_count' => 0,
                'variant_low_stock_count' => 0,
                'total_low_stock' => 0
            ]);
        }
    }
    /**
     * Get detailed total cost report
     */
    public function totalCostReport()
    {
        try {
            // Get all products with relationships
            $simpleProducts = SimpleProduct::with(['category'])->get();
            $variantProducts = VariantProduct::with(['category'])->get();

            // Initialize totals
            $totalCost = 0;
            $totalSaleValue = 0;
            $totalMRPValue = 0;

            // Process simple products
            $products = [];
            foreach ($simpleProducts as $product) {
                $stock = $product->current_stock ?? 0;
                $costPrice = $product->cost_price ?? 0;
                $salePrice = $product->sale_price ?? 0;
                $mrpPrice = $product->mrp_price ?? 0;

                $productCost = $stock * $costPrice;
                $productSaleValue = $stock * $salePrice;
                $productMRPValue = $stock * $mrpPrice;

                $totalCost += $productCost;
                $totalSaleValue += $productSaleValue;
                $totalMRPValue += $productMRPValue;

                $products[] = [
                    'name' => $product->name,
                    'sku' => $product->sku_code ?? 'N/A',
                    'type' => 'Simple',
                    'category' => $product->category->name ?? 'No Category',
                    'stock' => $stock,
                    'unit' => $product->unit ?? 'pcs',
                    'cost_price' => $costPrice,
                    'sale_price' => $salePrice,
                    'mrp_price' => $mrpPrice,
                    'total_cost' => $productCost,
                    'total_sale' => $productSaleValue,
                    'total_mrp' => $productMRPValue,
                ];
            }

            // Process variant products
            foreach ($variantProducts as $product) {
                if ($product->variants && is_array($product->variants)) {
                    foreach ($product->variants as $index => $variant) {
                        $stock = $variant['current_stock'] ?? 0;
                        $costPrice = $variant['cost_price'] ?? 0;
                        $salePrice = $variant['sale_price'] ?? 0;
                        $mrpPrice = $variant['mrp_price'] ?? 0;

                        $variantCost = $stock * $costPrice;
                        $variantSaleValue = $stock * $salePrice;
                        $variantMRPValue = $stock * $mrpPrice;

                        $totalCost += $variantCost;
                        $totalSaleValue += $variantSaleValue;
                        $totalMRPValue += $variantMRPValue;

                        $products[] = [
                            'name' => $product->name . ' - ' . ($variant['name'] ?? 'Variant ' . ($index + 1)),
                            'sku' => $variant['sku_code'] ?? 'N/A',
                            'type' => 'Variant',
                            'category' => $product->category->name ?? 'No Category',
                            'stock' => $stock,
                            'unit' => $variant['unit'] ?? 'pcs',
                            'cost_price' => $costPrice,
                            'sale_price' => $salePrice,
                            'mrp_price' => $mrpPrice,
                            'total_cost' => $variantCost,
                            'total_sale' => $variantSaleValue,
                            'total_mrp' => $variantMRPValue,
                        ];
                    }
                }
            }

            // Calculate total products count
            $totalSimpleProducts = $simpleProducts->count();
            $totalVariantProducts = $variantProducts->count();
            $totalVariantItems = 0;

            foreach ($variantProducts as $product) {
                if ($product->variants && is_array($product->variants)) {
                    $totalVariantItems += count($product->variants);
                }
            }

            $totalProducts = $totalSimpleProducts + $totalVariantItems;

            return response()->json([
                'success' => true,
                'total_cost' => $totalCost,
                'total_value' => $totalSaleValue,
                'total_sale_value' => $totalSaleValue,
                'total_mrp_value' => $totalMRPValue,
                'total_products' => $totalProducts,
                'simple_products' => $totalSimpleProducts,
                'variant_items' => $totalVariantItems,
                'products' => $products,
            ]);

        } catch (\Exception $e) {
            Log::error('Total cost report failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report: ' . $e->getMessage(),
            ]);
        }
    }
    /**
     * Get low stock products report
     */
    public function lowStockReport()
    {
        try {
            // Get all products
            $simpleProducts = SimpleProduct::with(['category', 'warehouse'])->get();
            $variantProducts = VariantProduct::with(['category', 'warehouse'])->get();

            // Initialize counters
            $criticalStockCount = 0;
            $lowStockCount = 0;
            $outOfStockCount = 0;

            // Process simple products
            $lowStockProducts = [];
            foreach ($simpleProducts as $product) {
                $stock = $product->current_stock ?? 0;
                $minAlert = $product->min_stock_alert ?? 5;

                if ($stock <= 0) {
                    $outOfStockCount++;
                    $status = 'out_of_stock';
                    $action = 'Urgent Restock Required';
                } elseif ($stock <= 5) {
                    $criticalStockCount++;
                    $status = 'critical';
                    $action = 'Immediate Restock';
                } elseif ($stock <= $minAlert) {
                    $lowStockCount++;
                    $status = 'low_stock';
                    $action = 'Plan Restock';
                } else {
                    continue; // Not low stock
                }

                $lowStockProducts[] = [
                    'name' => $product->name,
                    'sku' => $product->sku_code ?? 'N/A',
                    'stock' => $stock,
                    'min_stock_alert' => $minAlert,
                    'category' => $product->category->name ?? 'No Category',
                    'unit' => $product->unit ?? 'pcs',
                    'status' => $status,
                    'action' => $action,
                    'last_updated' => $product->updated_at ? $product->updated_at->format('d M Y, h:i A') : 'N/A',
                    'type' => 'simple',
                    'product_id' => $product->id,
                ];
            }

            // Process variant products
            foreach ($variantProducts as $product) {
                if ($product->variants && is_array($product->variants)) {
                    foreach ($product->variants as $index => $variant) {
                        $stock = $variant['current_stock'] ?? 0;
                        $minAlert = $variant['min_stock_alert'] ?? 5;

                        if ($stock <= 0) {
                            $outOfStockCount++;
                            $status = 'out_of_stock';
                            $action = 'Urgent Restock Required';
                        } elseif ($stock <= 5) {
                            $criticalStockCount++;
                            $status = 'critical';
                            $action = 'Immediate Restock';
                        } elseif ($stock <= $minAlert) {
                            $lowStockCount++;
                            $status = 'low_stock';
                            $action = 'Plan Restock';
                        } else {
                            continue; // Not low stock
                        }

                        $lowStockProducts[] = [
                            'name' => $product->name . ' - ' . ($variant['name'] ?? 'Variant ' . ($index + 1)),
                            'sku' => $variant['sku_code'] ?? 'N/A',
                            'stock' => $stock,
                            'min_stock_alert' => $minAlert,
                            'category' => $product->category->name ?? 'No Category',
                            'unit' => $variant['unit'] ?? 'pcs',
                            'status' => $status,
                            'action' => $action,
                            'last_updated' => $variant['updated_at'] ?? 'N/A',
                            'type' => 'variant',
                            'product_id' => $product->id,
                            'variant_index' => $index,
                        ];
                    }
                }
            }

            // Get recent stock changes from StockHistory
            $recentChanges = StockHistory::with(['warehouse'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function($history) {
                    // Try to get product name
                    $productName = 'Unknown Product';
                    if ($history->product_type === 'simple') {
                        $product = SimpleProduct::find($history->product_id);
                        if ($product) {
                            $productName = $product->name;
                        }
                    } else {
                        $product = VariantProduct::find($history->product_id);
                        if ($product) {
                            $productName = $product->name;
                        }
                    }

                    return [
                        'product_name' => $productName,
                        'warehouse' => $history->warehouse->name ?? 'Unknown',
                        'type' => $history->adjustment_type,
                        'old_stock' => $history->old_quantity,
                        'new_stock' => $history->new_quantity,
                        'time_ago' => $history->created_at->diffForHumans(),
                        'date' => $history->created_at->format('d M Y, h:i A'),
                    ];
                });

            return response()->json([
                'success' => true,
                'critical_stock_count' => $criticalStockCount,
                'low_stock_count' => $lowStockCount,
                'out_of_stock_count' => $outOfStockCount,
                'total_low_stock' => count($lowStockProducts),
                'products' => $lowStockProducts,
                'recent_changes' => $recentChanges,
            ]);

        } catch (\Exception $e) {
            Log::error('Low stock report failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report',
            ]);
        }
    }

}
