<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    /**
     * Display a listing of products/services for the frontend.
     */
    public function index()
    {
        return view('frontend.products.index');
    }

    /**
     * Display the specified item details.
     */
    public function show($id)
    {
        return view('frontend.products.show', compact('id'));
    }
}
