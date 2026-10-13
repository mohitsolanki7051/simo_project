@extends('frontend.layouts.app')

@section('title', 'Product Details')

@push('styles')
<style>
    .product-detail-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 2.5rem;
        max-width: 800px;
        margin: 0 auto 3rem auto;
    }
    .product-detail-header {
        display: flex;
        gap: 2rem;
        align-items: center;
        margin-bottom: 2rem;
    }
    .product-detail-icon {
        width: 120px;
        height: 120px;
        background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3.5rem;
        color: #4f46e5;
    }
    .product-detail-info h1 {
        font-size: 2rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.5rem;
    }
    .product-detail-price {
        font-size: 1.5rem;
        font-weight: 700;
        color: #4f46e5;
    }
    .product-detail-body {
        line-height: 1.7;
        color: #475569;
        margin-bottom: 2rem;
        font-size: 1.05rem;
    }
    .btn-back {
        display: inline-block;
        padding: 0.75rem 1.5rem;
        background-color: #f1f5f9;
        color: #334155;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        transition: background-color 0.2s;
    }
    .btn-back:hover {
        background-color: #e2e8f0;
    }
</style>
@endpush

@section('content')
<div class="product-detail-card">
    <div class="product-detail-header">
        <div class="product-detail-icon">
            <i class="fa-solid fa-box-open"></i>
        </div>
        <div class="product-detail-info">
            <h1>Product Item #{{ $id }}</h1>
            <div class="product-detail-price">Featured Item</div>
        </div>
    </div>

    <div class="product-detail-body">
        <p>This is a detailed overview page for product item #{{ $id }}. Full database integration and dynamic inventory controls can be linked here as needed.</p>
    </div>

    <a href="{{ route('frontend.products.index') }}" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> Back to Products
    </a>
</div>
@endsection
