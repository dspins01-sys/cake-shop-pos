<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SweetCake')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- TAMBAHKAN INI - CSS UNTUK FILTER -->
    <style>
        /* Category Filter Styles */
        .category-badge {
            background: #f8f9fa;
            padding: 8px 20px;
            border-radius: 25px;
            color: #6c757d;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-block;
            margin: 5px;
            border: 1px solid #e9ecef;
        }
        .category-badge:hover {
            background: #e9ecef;
            color: #495057;
            transform: translateY(-2px);
        }
        .category-badge.active {
            background: #ff6b6b;
            color: white;
            border-color: #ff6b6b;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">
            <i class="fas fa-cake-candles"></i> SweetCake
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Left Menu -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('home') }}">Home</a>
                </li>
                <!-- Categories -->
                @php
                    $categories = App\Models\Category::all();
                @endphp
                @foreach($categories as $cat)
                    <li class="nav-item d-none d-lg-block">
                        <a class="nav-link" href="{{ route('products.category', $cat->slug) }}">
                            {{ $cat->name }}
                        </a>
                    </li>
                @endforeach
            </ul>

            <!-- Right Menu -->
            <ul class="navbar-nav align-items-center">
                <!-- TRACK ORDER LINK - TAMBAHKAN INI -->
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('order.track.form') }}">
                        <i class="fas fa-search"></i>
                        <span class="d-none d-lg-inline ms-1">Track Order</span>
                    </a>
                </li>

                <!-- Cart Icon -->
                <li class="nav-item position-relative me-2">
                    <a class="nav-link" href="{{ route('cart.index') }}">
                        <i class="fas fa-shopping-cart"></i>
                        @php
                            $cartCount = 0;
                            if (class_exists('App\Helpers\CartHelper')) {
                                $cartCount = App\Helpers\CartHelper::totalItems();
                            }
                        @endphp
                        @if($cartCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                {{ $cartCount }}
                            </span>
                        @endif
                    </a>
                </li>

                @auth
                    <!-- User Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i>
                            <span class="d-none d-lg-inline ms-1">{{ Auth::user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @if(Auth::user()->role == 'admin')
                                <li><a class="dropdown-item" href="{{ route('admin.products.index') }}">
                                    <i class="fas fa-cog me-2"></i>Admin Panel
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                            @endif
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="fas fa-user me-2"></i>Profile
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <!-- Guest Menu -->
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">
                            <i class="fas fa-sign-in-alt"></i>
                            <span class="d-none d-lg-inline ms-1">Login</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}">
                            <i class="fas fa-user-plus"></i>
                            <span class="d-none d-lg-inline ms-1">Register</span>
                        </a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

    <main class="py-4">
        @yield('content')
    </main>
</body>
</html>
