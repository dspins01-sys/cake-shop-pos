<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - @yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('admin.products.index') }}">
                <i class="fas fa-cake-candles"></i> Admin Panel
            </a>
            <div class="text-white">
                {{ Auth::user()->name }}
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-3">
        <div class="row">
            <div class="col-md-2">
                <div class="list-group">
                    <a href="{{ route('admin.products.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-box"></i> Products
                    </a>
                </div>
            </div>
            <div class="col-md-10">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>