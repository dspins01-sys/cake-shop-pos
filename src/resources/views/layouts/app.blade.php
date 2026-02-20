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

        /* Navbar mobile styles */
        @media (max-width: 991.98px) {
            .navbar-nav {
                padding: 1rem 0;
            }
            .nav-item {
                padding: 0.5rem 0;
                border-bottom: 1px solid rgba(255,255,255,0.1);
            }
            .nav-item:last-child {
                border-bottom: none;
            }
            .dropdown-menu {
                background: transparent;
                border: none;
                padding-left: 1rem;
            }
            .dropdown-item {
                color: rgba(255,255,255,0.8);
                padding: 0.5rem 1rem;
            }
            .dropdown-item:hover {
                background: rgba(255,255,255,0.1);
                color: white;
            }
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
                <!-- Left Menu - Categories -->
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}">Home</a>
                    </li>
                    
                    @php
                        $categories = App\Models\Category::all();
                    @endphp
                    
                    @foreach($categories as $cat)
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('products.category', $cat->slug) }}">
                                {{ $cat->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                <!-- Right Menu - User & Cart -->
                <ul class="navbar-nav align-items-lg-center">
                    <!-- Track Order -->
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('order.track.form') }}">
                            <i class="fas fa-search me-1"></i> Track Order
                        </a>
                    </li>

                    <!-- Cart Icon -->
                    <li class="nav-item position-relative mx-lg-2">
                        <a class="nav-link" href="{{ route('cart.index') }}">
                            <i class="fas fa-shopping-cart me-1"></i> Cart
                            @php
                                $cartCount = 0;
                                if (class_exists('App\Helpers\CartHelper')) {
                                    $cartCount = App\Helpers\CartHelper::totalItems();
                                }
                            @endphp
                            @if($cartCount > 0)
                                <span class="badge bg-danger ms-1">{{ $cartCount }}</span>
                            @endif
                        </a>
                    </li>

                    @auth
                        <!-- User Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i> {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @if(Auth::user()->role == 'admin')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.products.index') }}">
                                            <i class="fas fa-cog me-2"></i>Admin Panel
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                @endif
                                
                                <li>
                                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                        <i class="fas fa-user me-2"></i>Profile
                                    </a>
                                </li>
                                
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="fas fa-history me-2"></i>My Orders
                                    </a>
                                </li>
                                
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
                                <i class="fas fa-sign-in-alt me-1"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">
                                <i class="fas fa-user-plus me-1"></i> Register
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="py-4">
        @yield('content')
    </main>

    <!-- Bootstrap JS (untuk hamburger menu) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
</body>
</html>