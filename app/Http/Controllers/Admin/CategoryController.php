<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
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
        $categories = Category::with(['parent', 'children'])
            ->ordered()
            ->get();

        // Group categories by parent
        $mainCategories = $categories->whereNull('parent_id');

        return view('admin.categories.index', compact('categories', 'mainCategories'));
    }

    /**
     * Show the form for creating a new category
     */
    public function create()
    {
        $categories = Category::active()
            ->mainCategories()
            ->ordered()
            ->get();

        return view('admin.categories.create', compact('categories'));
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:active,inactive',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        try {
            // Generate slug from name
            $slug = Str::slug($validated['name']);

            // Check for duplicate slug
            $slugCount = Category::where('slug', 'like', $slug . '%')->count();
            if ($slugCount > 0) {
                $slug = $slug . '-' . ($slugCount + 1);
            }

            $data = [
                'name' => $validated['name'],
                'slug' => $slug,
                'description' => $validated['description'] ?? null,
                'parent_id' => $validated['parent_id'] ?? null,
                'status' => $validated['status'],
                'meta_title' => $validated['meta_title'] ?? null,
                'meta_description' => $validated['meta_description'] ?? null,
                'meta_keywords' => $validated['meta_keywords'] ?? null,
                'sort_order' => $validated['sort_order'] ?? 0
            ];

            // Upload image if provided
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('categories', 'public');
                $data['image'] = $imagePath;
            }

            $category = Category::create($data);

            return redirect()->route('admin.categories.index')
                ->with('success', 'Category created successfully!');

        } catch (\Exception $e) {
            Log::error('Category creation failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Failed to create category. Please try again.');
        }
    }

    /**
     * Display the specified category
     */
    public function show($id)
    {
        try {
            $category = Category::with(['parent', 'children', 'products'])
                ->findOrFail($id);

            return view('admin.categories.show', compact('category'));

        } catch (\Exception $e) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Category not found.');
        }
    }

    /**
     * Show the form for editing the category
     */
    public function edit($id)
    {
        $category = Category::findOrFail($id);

        $categories = Category::active()
            ->where('id', '!=', $id) // Exclude current category
            ->mainCategories()
            ->ordered()
            ->get();

        return view('admin.categories.edit', compact('category', 'categories'));
    }

    /**
     * Update the category
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id|not_in:' . $id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:active,inactive',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        try {
            // Check for circular reference (category can't be its own parent or child's child)
            if ($validated['parent_id']) {
                $parent = Category::find($validated['parent_id']);
                $allChildren = $this->getAllChildrenIds($parent);

                if (in_array($id, $allChildren)) {
                    return back()->withInput()
                        ->with('error', 'Cannot assign category to its own child.');
                }
            }

            $data = [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'parent_id' => $validated['parent_id'] ?? null,
                'status' => $validated['status'],
                'meta_title' => $validated['meta_title'] ?? null,
                'meta_description' => $validated['meta_description'] ?? null,
                'meta_keywords' => $validated['meta_keywords'] ?? null,
                'sort_order' => $validated['sort_order'] ?? 0
            ];

            // Update slug if name changed
            if ($category->name !== $validated['name']) {
                $slug = Str::slug($validated['name']);
                $slugCount = Category::where('slug', 'like', $slug . '%')->where('id', '!=', $id)->count();
                if ($slugCount > 0) {
                    $slug = $slug . '-' . ($slugCount + 1);
                }
                $data['slug'] = $slug;
            }

            // Update image if provided
            if ($request->hasFile('image')) {
                // Delete old image
                if ($category->image) {
                    Storage::disk('public')->delete($category->image);
                }

                // Upload new image
                $imagePath = $request->file('image')->store('categories', 'public');
                $data['image'] = $imagePath;
            }

            $category->update($data);

            return redirect()->route('admin.categories.index')
                ->with('success', 'Category updated successfully!');

        } catch (\Exception $e) {
            Log::error('Category update failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Failed to update category. Please try again.');
        }
    }

    /**
     * Remove the category
     */
    public function destroy($id)
    {
        try {
            $category = Category::with(['children', 'products'])->findOrFail($id);

            // Check if category can be deleted
            if ($category->hasChildren()) {
                return back()->with('error', 'Cannot delete category with sub-categories.');
            }

            if ($category->hasProducts()) {
                return back()->with('error', 'Cannot delete category with products.');
            }

            // Delete image if exists
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }

            $category->delete();

            return redirect()->route('admin.categories.index')
                ->with('success', 'Category deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Category deletion failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete category. Please try again.');
        }
    }

    /**
     * Get all children IDs recursively
     */
    private function getAllChildrenIds($category, &$ids = [])
    {
        $ids[] = $category->id;

        foreach ($category->children as $child) {
            $this->getAllChildrenIds($child, $ids);
        }

        return $ids;
    }

    /**
     * Bulk delete categories
     */
    public function bulkDestroy(Request $request)
    {
        try {
            $request->validate([
                'category_ids' => 'required|array'
            ]);

            $categories = Category::with(['children', 'products'])
                ->whereIn('id', $request->category_ids)
                ->get();

            $deleted = 0;
            $errors = [];

            foreach ($categories as $category) {
                // Check if category can be deleted
                if ($category->hasChildren()) {
                    $errors[] = "Cannot delete '{$category->name}' - has sub-categories.";
                    continue;
                }

                if ($category->hasProducts()) {
                    $errors[] = "Cannot delete '{$category->name}' - has products.";
                    continue;
                }

                // Delete image if exists
                if ($category->image && Storage::disk('public')->exists($category->image)) {
                    Storage::disk('public')->delete($category->image);
                }

                $category->delete();
                $deleted++;
            }

            $response = [
                'success' => true,
                'message' => "{$deleted} category(s) deleted successfully."
            ];

            if (!empty($errors)) {
                $response['errors'] = $errors;
            }

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Bulk category deletion failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete categories: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update categories status
     */
    public function bulkUpdateStatus(Request $request)
    {
        try {
            $request->validate([
                'category_ids' => 'required|array',
                'status' => 'required|in:active,inactive'
            ]);

            Category::whereIn('id', $request->category_ids)
                ->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Categories status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update categories status'
            ], 500);
        }
    }

    /**
     * Get categories for dropdown (AJAX)
     */
    public function getCategories(Request $request)
    {
        try {
            $categories = Category::active()
                ->mainCategories()
                ->ordered()
                ->get(['id', 'name']);

            return response()->json([
                'success' => true,
                'categories' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories'
            ], 500);
        }
    }
}
