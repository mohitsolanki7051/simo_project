<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SimpleProduct;
use App\Models\VariantProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories
     */
    public function index()
    {
        $categories = Category::orderBy('name', 'asc')->get();

        // Manually count products for each category
        foreach ($categories as $category) {
            $category->simple_products_count = SimpleProduct::where('category_id', $category->id)->count();
            $category->variant_products_count = VariantProduct::where('category_id', $category->id)->count();
            $category->total_products = $category->simple_products_count + $category->variant_products_count;
        }

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category
     */
    public function create()
    {
        return view('admin.categories.create');
    }

/**
 * Store a newly created category
 */
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255|unique:categories,name',
        'slug' => 'nullable|string|max:255|unique:categories,slug',
        'description' => 'nullable|string',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        'status' => 'required|in:active,inactive'
    ]);

    try {
        // Generate slug if not provided or if it's empty
        if (empty($validated['slug'])) {
            $slug = Str::slug($validated['name']);

            // Check if slug already exists
            $existingSlug = Category::where('slug', $slug)->first();
            if ($existingSlug) {
                $slugCount = Category::where('slug', 'like', $slug . '%')->count();
                $slug = $slug . '-' . ($slugCount + 1);
            }

            $validated['slug'] = $slug;
        } else {
            // If slug is provided, make sure it's in proper format
            $validated['slug'] = Str::slug($validated['slug']);
        }

        // Upload image if provided
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
            $validated['image'] = $imagePath;
        }

        Category::create($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully!');

    } catch (\Exception $e) {
        Log::error('Category creation failed: ' . $e->getMessage());
        return back()->withInput()
            ->with('error', 'Failed to create category. Please try again.');
    }
}
    /**
     * Show the form for editing the category
     */
    public function edit($id)
    {
        $category = Category::findOrFail($id);
        return view('admin.categories.edit', compact('category'));
    }

/**
 * Update the category
 */
public function update(Request $request, $id)
{
    $category = Category::findOrFail($id);

    $validated = $request->validate([
        'name' => 'required|string|max:255|unique:categories,name,' . $id . ',_id',
        'slug' => 'nullable|string|max:255|unique:categories,slug,' . $id . ',_id',
        'description' => 'nullable|string',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        'status' => 'required|in:active,inactive'
    ]);

    try {
        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $slug = Str::slug($validated['name']);
            $slugCount = Category::where('slug', 'like', $slug . '%')
                ->where('_id', '!=', $id)
                ->count();
            if ($slugCount > 0) {
                $slug = $slug . '-' . ($slugCount + 1);
            }
            $validated['slug'] = $slug;
        } else {
            // If slug is provided, make sure it's in proper format
            $validated['slug'] = Str::slug($validated['slug']);
        }

        // Handle image removal
        if ($request->has('remove_image') && $request->remove_image == '1') {
            // Delete old image
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }
            $validated['image'] = null;
        }
        // Update image if new one provided
        elseif ($request->hasFile('image')) {
            // Delete old image
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }

            // Upload new image
            $imagePath = $request->file('image')->store('categories', 'public');
            $validated['image'] = $imagePath;
        } else {
            // If no new image and not removed, keep the old one
            $validated['image'] = $category->image;
        }

        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully!');

    } catch (\Exception $e) {
        Log::error('Category update failed: ' . $e->getMessage());
        return back()->withInput()
            ->with('error', 'Failed to update category. Please try again.');
    }
}
    /**
     * Bulk update categories status
     */
    // public function bulkUpdateStatus(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'category_ids' => 'required|array',
    //             'status' => 'required|in:active,inactive'
    //         ]);

    //         Category::whereIn('_id', $request->category_ids)
    //             ->update(['status' => $request->status]);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Categories status updated successfully'
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to update categories status'
    //         ], 500);
    //     }
    // }
     public function show($id)
    {
        $category = Category::findOrFail($id);

        // Get all products with pagination
        $simpleProducts = SimpleProduct::where('category_id', $id)
            ->orderBy('name', 'asc')
            ->paginate(20, ['*'], 'simple_page');

        $variantProducts = VariantProduct::where('category_id', $id)
            ->orderBy('name', 'asc')
            ->paginate(20, ['*'], 'variant_page');

        // Get counts
        $category->simple_products_count = $simpleProducts->total();
        $category->variant_products_count = $variantProducts->total();
        $category->total_products = $category->simple_products_count + $category->variant_products_count;

        return view('admin.categories.view', compact('category', 'simpleProducts', 'variantProducts'));
    }
}
