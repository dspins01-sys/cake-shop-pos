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
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">SweetCake</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}">Home</a>
                    </li>
                    
                    <!-- CART ICON - UNTUK SEMUA (dikasih comment biar gampang dicari) -->
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('cart.index') }}">
                            <i class="fas fa-shopping-cart"></i>
                            @php
                                $cartCount = 0;
                                if (class_exists('App\Helpers\CartHelper')) {
                                    $cartCount = App\Helpers\CartHelper::totalItems();
                                }
                            @endphp
                            @if($cartCount > 0)
                                <span class="badge bg-danger">{{ $cartCount }}</span>
                            @endif
                        </a>
                    </li>

                    @auth
                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-link nav-link">Logout ({{ Auth::user()->name }})</button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">Register</a>
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
