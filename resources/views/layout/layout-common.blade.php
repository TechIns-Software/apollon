<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">


    <title> @yield('title')</title>
    @vite([
        'node_modules/bootstrap/dist/css/bootstrap.css',
        'node_modules/@fortawesome/fontawesome-free/css/all.css',
    ])
    @section('css')
    @show
    @section('js')
    @show
</head>
<body>
<header>
    <nav class="navbar bg-dark navbar navbar-expand-lg" data-bs-theme="dark">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <a class="navbar-brand" href="{{ url("/") }}">E-Support</a>

            <div class="dropdown d-block d-sm-none">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                    {{ auth()->user()->name }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                    <li><a class="dropdown-item" href="{{ route('user.profile') }}"><i class="fa-solid fa-lock"></i> Αλλαγή Password</a></li>
                    <li><a class="dropdown-item bg-danger" href="{{ route('auth.logout') }}"><i class="fa-solid fa-power-off"></i> Logout</a></li>
                </ul>
            </div>

            <div class="dropdown d-none d-sm-block d-md-none">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                    {{ auth()->user()->name }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                    <li><a class="dropdown-item" href="{{ route('user.profile') }}"><i class="fa-solid fa-lock"></i> Αλλαγή Password</a></li>
                    <li><a class="dropdown-item bg-danger" href="{{ route('auth.logout') }}"><i class="fa-solid fa-power-off"></i> Logout</a></li>
                </ul>
            </div>
            <div id="navbarScroll" class="collapse navbar-collapse" id="navbarNav">
                @section('nav-items')
                @show
            </div>
            <div class="dropdown d-none d-md-block">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                    @empty(auth()->user()->name)
                        {{ auth()->user()->fullname }}
                    @else
                        {{ auth()->user()->name }}
                    @endif
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                    <li><a class="dropdown-item" href="{{ route('user.profile') }}"><i class="fa-solid fa-lock"></i> Αλλαγή Password</a></li>
                    <li><a class="dropdown-item bg-danger" href="{{ route('auth.logout') }}"><i class="fa-solid fa-power-off"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
</header>
@section("main-wrapper")
    <main class="container">
        @section('main')
        @show
    </main>
@show
</body>
</html>
