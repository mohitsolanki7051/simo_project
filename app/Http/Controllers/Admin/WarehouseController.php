<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\WarehouseMovement;
use App\Models\SimpleProduct;
use App\Models\VariantProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class WarehouseController extends Controller
{
    /**
     * Display warehouse management page
     */
    public function index()
    {
        try {
            // Get ALL warehouses for dropdown (active and inactive both)
            $warehouses = Warehouse::orderBy('is_main', 'desc')
                ->orderBy('name', 'asc')
                ->get();

            // Get main warehouse for default selection
            $mainWarehouse = Warehouse::main()->first();

            // If no warehouses exist, mainWarehouse will be null
            return view('admin.warehouses.index', [
                'warehouses' => $warehouses,
                'mainWarehouse' => $mainWarehouse,
                'hasWarehouses' => $warehouses->count() > 0
            ]);

        } catch (\Exception $e) {
            Log::error('Warehouse index error: ' . $e->getMessage());
            return back()->with('error', 'Failed to load warehouse page');
        }
    }

    /**
     * Get warehouse stock data (AJAX)
     */
    public function getWarehouseStock($warehouseId)
    {
        try {
            Log::info('Loading stock for warehouse: ' . $warehouseId);

            $warehouse = Warehouse::find($warehouseId);
            if (!$warehouse) {
                return response()->json([
                    'success' => false,
                    'message' => 'Warehouse not found'
                ], 404);
            }

            // Get all stocks for this warehouse
            $stocks = WarehouseStock::where('warehouse_id', (string)$warehouseId)->get();

            if ($stocks->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'stock' => [],
                    'stats' => [
                        'total_products' => 0,
                        'total_value' => '0.00',
                        'low_stock_items' => 0,
                    ]
                ]);
            }

            $stockData = [];
            $totalValue = 0;
            $totalProducts = 0;
            $lowStockItems = 0;

            // First, organize stocks by product_id to group variant products
            $groupedStocks = [];
            foreach ($stocks as $stock) {
                $productId = (string)$stock->product_id;
                if (!isset($groupedStocks[$productId])) {
                    $groupedStocks[$productId] = [];
                }
                $groupedStocks[$productId][] = $stock;
            }

            // Process each product
            foreach ($groupedStocks as $productId => $productStocks) {
                // Try to find in SimpleProduct first
                $simpleProduct = SimpleProduct::find($productId);

                if ($simpleProduct) {
                    // This is a simple product
                    foreach ($productStocks as $stock) {
                        if ($stock->variant_id === null) {
                            $salePrice = $this->convertToFloat($simpleProduct->sale_price ?? 0);
                            $costPrice = $this->convertToFloat($simpleProduct->cost_price ?? 0);
                            $quantity = $stock->quantity;

                            $stockData[] = [
                                'type' => 'simple',
                                'stock_id' => (string)$stock->_id,
                                'product_id' => $productId,
                                'product_name' => $simpleProduct->name,
                                'variant_name' => null,
                                'sku' => $simpleProduct->sku_code ?? 'N/A',
                                'quantity' => $quantity,
                                'min_stock_alert' => $stock->min_stock_alert ?? 10,
                                'unit' => $simpleProduct->unit ?? 'PCS',
                                'sale_price' => $salePrice,
                                'cost_price' => $costPrice,
                                'stock_value' => $quantity * $salePrice,
                                'product_status' => $simpleProduct->status ?? 'active',
                            ];

                            $totalValue += $quantity * $salePrice;
                            $totalProducts++;

                            if ($quantity <= ($stock->min_stock_alert ?? 10) && $quantity > 0) {
                                $lowStockItems++;
                            }
                            break;
                        }
                    }
                } else {
                    // Try to find in VariantProduct
                    $variantProduct = VariantProduct::find($productId);

                    if ($variantProduct) {
                        // This is a variant product
                        $variants = $variantProduct->variants ?? [];

                        // Create variant parent entry
                        $parentEntry = [
                            'type' => 'variant_parent',
                            'product_id' => $productId,
                            'product_name' => $variantProduct->name,
                            'product_status' => $variantProduct->status ?? 'active',
                            'variants' => []
                        ];

                        // Process each stock entry for this variant product
                        foreach ($productStocks as $stock) {
                            if ($stock->variant_id === null) {
                                continue;
                            }

                            $variantId = (string) $stock->variant_id;

                            $variant = collect($variants)->first(function ($v) use ($variantId) {
                                return isset($v['_id']) && (string)$v['_id'] === $variantId;
                            });

                            if (!$variant) {
                                continue;
                            }

                            $salePrice = $this->convertToFloat($variant['sale_price'] ?? 0);
                            $costPrice = $this->convertToFloat($variant['cost_price'] ?? 0);
                            $quantity = $stock->quantity;

                            $parentEntry['variants'][] = [
                                'stock_id' => (string)$stock->_id,
                                'variant_id' => (string)$variant['_id'],
                                'variant_name' => $variant['name'] ?? 'Variant',
                                'sku' => $variant['sku_code'] ?? 'N/A',
                                'quantity' => $quantity,
                                'min_stock_alert' => $stock->min_stock_alert ?? 10,
                                'unit' => $variant['unit'] ?? 'PCS',
                                'sale_price' => $salePrice,
                                'cost_price' => $costPrice,
                                'stock_value' => $quantity * $salePrice,
                                'product_status' => $variantProduct->status ?? 'active',
                            ];

                            $totalValue += $quantity * $salePrice;
                            $totalProducts++;

                            if ($quantity <= ($stock->min_stock_alert ?? 10) && $quantity > 0) {
                                $lowStockItems++;
                            }
                        }

                        // Only add parent entry if it has variants
                        if (!empty($parentEntry['variants'])) {
                            $stockData[] = $parentEntry;
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'stock' => $stockData,
                'stats' => [
                    'total_products' => $totalProducts,
                    'total_value' => number_format($totalValue, 2, '.', ''),
                    'low_stock_items' => $lowStockItems,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Get warehouse stock failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load warehouse stock: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Transfer stock between warehouses
     */
    public function transferStock(Request $request)
    {
        try {
            Log::info('Transfer stock request received:', $request->all());

            $validated = $request->validate([
                'from_warehouse_id' => 'required',
                'to_warehouse_id' => 'required|different:from_warehouse_id',
                'items' => 'required|array|min:1',
                'items.*.stock_id' => 'required',
                'items.*.product_id' => 'required',
                'items.*.product_type' => 'required|in:simple,variant',
                'items.*.variant_id' => 'nullable|string',
                'items.*.quantity' => 'required|integer|min:1',
            ]);

            $fromWarehouse = Warehouse::find($validated['from_warehouse_id']);
            $toWarehouse = Warehouse::find($validated['to_warehouse_id']);

            if (!$fromWarehouse) {
                return response()->json([
                    'success' => false,
                    'message' => 'Source warehouse not found'
                ], 404);
            }

            if (!$toWarehouse) {
                return response()->json([
                    'success' => false,
                    'message' => 'Destination warehouse not found'
                ], 404);
            }

            $transferredCount = 0;
            $errors = [];

            foreach ($validated['items'] as $item) {
                $sourceStock = WarehouseStock::find($item['stock_id']);

                if (!$sourceStock) {
                    $errors[] = "Stock not found";
                    continue;
                }

                if ((string)$sourceStock->warehouse_id !== $validated['from_warehouse_id']) {
                    $errors[] = "Stock doesn't belong to source warehouse";
                    continue;
                }

                if ($sourceStock->quantity < $item['quantity']) {
                    $errors[] = "Insufficient stock (available: {$sourceStock->quantity})";
                    continue;
                }

                // Reduce source stock
                $sourceStock->quantity -= $item['quantity'];
                $sourceStock->save();

                // Find or create destination stock
                $destStockQuery = WarehouseStock::where('warehouse_id', $validated['to_warehouse_id'])
                    ->where('product_id', $item['product_id'])
                    ->where('product_type', $item['product_type']);

                if ($item['product_type'] === 'variant' && $item['variant_id'] !== null) {
                    $destStockQuery->where('variant_id', (string)$item['variant_id']);
                } else {
                    $destStockQuery->whereNull('variant_id');
                }

                $destStock = $destStockQuery->first();

                if ($destStock) {
                    $destStock->quantity += $item['quantity'];
                    $destStock->save();
                } else {
                    $destStockData = [
                        'warehouse_id' => $validated['to_warehouse_id'],
                        'product_id' => $item['product_id'],
                        'product_type' => $item['product_type'],
                        'quantity' => $item['quantity'],
                        'min_stock_alert' => $sourceStock->min_stock_alert ?? 10,
                    ];

                    if ($item['product_type'] === 'variant' && $item['variant_id'] !== null) {
                        $destStockData['variant_id'] = (string)$item['variant_id'];
                    }

                    WarehouseStock::create($destStockData);
                }

                // Create movement records
                WarehouseMovement::create([
                    'warehouse_id' => $validated['from_warehouse_id'],
                    'product_id' => $item['product_id'],
                    'product_type' => $item['product_type'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'type' => 'transfer_out',
                    'quantity' => -$item['quantity'],
                    'reference_id' => (string)$toWarehouse->_id,
                ]);

                WarehouseMovement::create([
                    'warehouse_id' => $validated['to_warehouse_id'],
                    'product_id' => $item['product_id'],
                    'product_type' => $item['product_type'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'type' => 'transfer_in',
                    'quantity' => $item['quantity'],
                    'reference_id' => (string)$fromWarehouse->_id,
                ]);

                $transferredCount++;
            }

            $message = "Successfully transferred {$transferredCount} item(s) from {$fromWarehouse->name} to {$toWarehouse->name}";

            if (!empty($errors)) {
                $message .= ". Issues: " . implode(", ", $errors);
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            Log::error('Transfer failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Transfer failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new warehouse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:4',
                'min:4',
                'regex:/^\d{4}$/',
                'unique:warehouses,code'
            ],
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => [
                'required',
                'string',
                'max:6',
                'min:6',
                'regex:/^\d{6}$/',
            ],
            'phone' => [
                'required',
                'string',
                'max:10',
                'min:10',
                'regex:/^\d{10}$/',
            ],
            'email' => 'nullable|email|max:255',
            'manager_name' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            'is_main' => 'boolean',
        ]);

        try {
            if (Warehouse::count() == 0) {
                $validated['is_main'] = true;
                $validated['status'] = 'active';
                $message = 'Warehouse created as main warehouse!';
            } elseif ($request->has('is_main') && $request->is_main == true) {
                Warehouse::where('is_main', true)->update(['is_main' => false]);
                $validated['is_main'] = true;
                $message = 'Warehouse created as main warehouse!';
            } else {
                $validated['is_main'] = false;
                $message = 'Warehouse created successfully!';
            }

            $warehouse = Warehouse::create($validated);

            return response()->json([
                'success' => true,
                'message' => $message,
                'warehouse' => [
                    '_id' => (string)$warehouse->_id,
                    'id' => (string)$warehouse->_id,
                    'name' => $warehouse->name,
                    'code' => $warehouse->code,
                    'address' => $warehouse->address,
                    'city' => $warehouse->city,
                    'state' => $warehouse->state,
                    'pincode' => $warehouse->pincode,
                    'phone' => $warehouse->phone,
                    'email' => $warehouse->email,
                    'manager_name' => $warehouse->manager_name,
                    'is_main' => $warehouse->is_main,
                    'status' => $warehouse->status,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Warehouse creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create warehouse: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get warehouse for editing
     */
    public function edit($id)
    {
        try {
            $warehouse = Warehouse::findOrFail($id);
            return response()->json([
                'success' => true,
                'warehouse' => $warehouse
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Warehouse not found'
            ], 404);
        }
    }

    /**
     * Update warehouse
     */
    public function update(Request $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:4',
                'min:4',
                'regex:/^\d{4}$/',
                Rule::unique('warehouses', 'code')->ignore($id),
            ],
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => [
                'required',
                'string',
                'max:6',
                'min:6',
                'regex:/^\d{6}$/',
            ],
            'phone' => [
                'required',
                'string',
                'max:10',
                'min:10',
                'regex:/^\d{10}$/',
            ],
            'email' => 'nullable|email|max:255',
            'manager_name' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            'is_main' => 'boolean',
        ]);

        try {
            // Check if setting as main and it's not already main
            if ($request->has('is_main') && $request->is_main == true && !$warehouse->is_main) {
                Warehouse::where('is_main', true)->update(['is_main' => false]);
                $validated['is_main'] = true;
            }

            $warehouse->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Warehouse updated successfully!',
                'warehouse' => [
                    '_id' => (string)$warehouse->_id,
                    'id' => (string)$warehouse->_id,
                    'name' => $warehouse->name,
                    'code' => $warehouse->code,
                    'address' => $warehouse->address,
                    'city' => $warehouse->city,
                    'state' => $warehouse->state,
                    'pincode' => $warehouse->pincode,
                    'phone' => $warehouse->phone,
                    'email' => $warehouse->email,
                    'manager_name' => $warehouse->manager_name,
                    'is_main' => $warehouse->is_main,
                    'status' => $warehouse->status,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Warehouse update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update warehouse: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete warehouse
     */
/**
 * Delete warehouse
 */
public function destroy($id)
{
    try {
        $warehouse = Warehouse::findOrFail($id);

        // Check if warehouse has ANY stock with quantity > 0
        $stockCount = WarehouseStock::where('warehouse_id', $id)
            ->where('quantity', '>', 0)  // Only check products with quantity
            ->count();

        if ($stockCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete warehouse with existing stock. Please transfer or remove all products first.'
            ], 400);
        }

        // Delete all zero-quantity stock records for this warehouse
        WarehouseStock::where('warehouse_id', $id)->delete();

        // Check if this is the main warehouse
        $isMain = $warehouse->is_main;

        // Delete the warehouse
        $warehouse->delete();

        // If this was the main warehouse, set another warehouse as main
        if ($isMain) {
            $nextWarehouse = Warehouse::first();
            if ($nextWarehouse) {
                $nextWarehouse->is_main = true;
                $nextWarehouse->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Warehouse deleted successfully',
            'newMainWarehouseId' => $isMain && Warehouse::exists() ? (string)Warehouse::where('is_main', true)->first()->_id : null
        ]);

    } catch (\Exception $e) {
        Log::error('Warehouse deletion failed: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to delete warehouse: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Set warehouse as main
     */
    public function setAsMain($id)
    {
        try {
            $warehouse = Warehouse::findOrFail($id);

            if ($warehouse->is_main) {
                return response()->json([
                    'success' => false,
                    'message' => 'This warehouse is already the main warehouse'
                ]);
            }

            Warehouse::where('is_main', true)->update(['is_main' => false]);
            $warehouse->update(['is_main' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Warehouse set as main successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Set as main failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to set as main warehouse'
            ], 500);
        }
    }

    /**
     * Get all active warehouses
     */
    public function getActiveWarehouses()
    {
        try {
            $warehouses = Warehouse::where('status', 'active')
                ->orderBy('is_main', 'desc')
                ->orderBy('name', 'asc')
                ->get(['_id', 'name', 'code', 'is_main']);

            return response()->json([
                'success' => true,
                'warehouses' => $warehouses
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load warehouses'
            ], 500);
        }
    }

    /**
     * Helper method to convert MongoDB Decimal128 to float
     */
    private function convertToFloat($value)
    {
        if ($value instanceof \MongoDB\BSON\Decimal128) {
            return (float) $value->__toString();
        }
        return (float) $value;
    }
}
