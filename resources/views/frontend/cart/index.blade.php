@extends('frontend.layouts.app')

@section('title', 'Shopping Cart')

@push('styles')
<style>
    .cart-header {
        margin-bottom: 2rem;
    }
    .cart-container {
        background: var(--surface);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 2rem;
    }
    .empty-cart {
        text-align: center;
        padding: 3rem 1rem;
        color: var(--text-muted);
    }
    .btn-shop {
        display: inline-block;
        margin-top: 1rem;
        padding: 0.75rem 1.5rem;
        background-color: var(--primary);
        color: white;
        text-decoration: none;
        border-radius: 6px;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="cart-header">
    <h1>Shopping Cart</h1>
</div>

<div class="cart-container">
    @if(empty($cartItems))
        <div class="empty-cart">
            <i class="fa-solid fa-basket-shopping" style="font-size: 3rem; margin-bottom: 1rem; color: var(--border-color);"></i>
            <h2>Your cart is currently empty</h2>
            <p>Browse our products and add items to your cart.</p>
            <a href="{{ route('frontend.products.index') }}" class="btn-shop">Explore Products</a>
        </div>
    @else
        <!-- Cart Items List will render here -->
        <p>Items in cart: {{ count($cartItems) }}</p>
    @endif
</div>
@endsection
