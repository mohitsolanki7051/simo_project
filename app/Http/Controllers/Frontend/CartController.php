<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display the shopping cart page.
     */
    public function index()
    {
        $cartItems = session()->get('cart', []);

        return view('frontend.cart.index', compact('cartItems'));
    }
}
