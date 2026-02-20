@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Dashboard Admin</h1>
    
    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5>Total Products</h5>
                    <h2>{{ $totalProducts }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5>Total Categories</h5>
                    <h2>{{ $totalCategories }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('admin.products.index') }}" class="btn btn-primary">
            <i class="fas fa-box"></i> Manage Products
        </a>
        <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary">
            <i class="fas fa-user"></i> Profile
        </a>
    </div>
</div>
@endsection