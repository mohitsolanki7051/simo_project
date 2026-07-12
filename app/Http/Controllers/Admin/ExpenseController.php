<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\InvoiceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ExpenseController extends Controller
{
    public static $categories = [
        'Office Rent',
        'Petrol & Transport',
        'Salaries & Wages',
        'Electricity & Utilities',
        'Office Supplies & Stationery',
        'Tea & Snacks',
        'Marketing & Ads',
        'Repairs & Maintenance',
        'Miscellaneous'
    ];

    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $category = $request->input('category', 'all');
        $paymentMethod = $request->input('payment_method', 'all');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $query = Expense::query();

        // Search in description or reference
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('description', 'LIKE', "%{$search}%")
                  ->orWhere('reference_no', 'LIKE', "%{$search}%");
            });
        }

        // Filter Category
        if ($category !== 'all') {
            $query->where('category_name', $category);
        }

        // Filter Payment Method
        if ($paymentMethod !== 'all') {
            $query->where('payment_method', $paymentMethod);
        }

        // Filter Date Range
        if (!empty($fromDate)) {
            $query->where('expense_date', '>=', Carbon::parse($fromDate)->startOfDay());
        }
        if (!empty($toDate)) {
            $query->where('expense_date', '<=', Carbon::parse($toDate)->endOfDay());
        }

        // Retrieve and calculate
        $expenses = $query->orderBy('expense_date', 'desc')->get();

        $totalExpenses = $expenses->sum('amount');
        $expenseCount = $expenses->count();
        $avgExpense = $expenseCount > 0 ? $totalExpenses / $expenseCount : 0;

        $categories = self::$categories;

        return view('admin.expenses.index', compact(
            'expenses',
            'totalExpenses',
            'expenseCount',
            'avgExpense',
            'categories',
            'search',
            'category',
            'paymentMethod',
            'fromDate',
            'toDate'
        ));
    }

    public function create()
    {
        $categories = self::$categories;
        return view('admin.expenses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_name' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'payment_method' => 'required|in:Cash,Bank,UPI',
            'reference_no' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500'
        ]);

        Expense::create([
            'category_name' => $validated['category_name'],
            'amount' => (float)$validated['amount'],
            'expense_date' => Carbon::parse($validated['expense_date']),
            'payment_method' => $validated['payment_method'],
            'reference_no' => $validated['reference_no'] ?? null,
            'description' => $validated['description'] ?? null
        ]);

        return redirect()->route('admin.expenses.index')
            ->with('success', 'Expense transaction created successfully.');
    }

    public function edit($id)
    {
        $expense = Expense::findOrFail($id);
        $categories = self::$categories;
        return view('admin.expenses.edit', compact('expense', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);

        $validated = $request->validate([
            'category_name' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'payment_method' => 'required|in:Cash,Bank,UPI',
            'reference_no' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500'
        ]);

        $expense->update([
            'category_name' => $validated['category_name'],
            'amount' => (float)$validated['amount'],
            'expense_date' => Carbon::parse($validated['expense_date']),
            'payment_method' => $validated['payment_method'],
            'reference_no' => $validated['reference_no'] ?? null,
            'description' => $validated['description'] ?? null
        ]);

        return redirect()->route('admin.expenses.index')
            ->with('success', 'Expense transaction updated successfully.');
    }

    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();

        return redirect()->route('admin.expenses.index')
            ->with('success', 'Expense transaction deleted successfully.');
    }

    public function pdf(Request $request)
    {
        $search = $request->input('search', '');
        $category = $request->input('category', 'all');
        $paymentMethod = $request->input('payment_method', 'all');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $query = Expense::query();

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('description', 'LIKE', "%{$search}%")
                  ->orWhere('reference_no', 'LIKE', "%{$search}%");
            });
        }
        if ($category !== 'all') {
            $query->where('category_name', $category);
        }
        if ($paymentMethod !== 'all') {
            $query->where('payment_method', $paymentMethod);
        }
        if (!empty($fromDate)) {
            $query->where('expense_date', '>=', Carbon::parse($fromDate)->startOfDay());
        }
        if (!empty($toDate)) {
            $query->where('expense_date', '<=', Carbon::parse($toDate)->endOfDay());
        }

        $expenses = $query->orderBy('expense_date', 'desc')->get();
        $totalExpenses = $expenses->sum('amount');

        $settings = InvoiceSetting::first();

        return view('admin.expenses.pdf', compact('expenses', 'totalExpenses', 'settings', 'fromDate', 'toDate'));
    }
}
