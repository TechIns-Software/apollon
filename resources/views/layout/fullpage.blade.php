<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title> @yield('title')</title>
    @section('css')
        <style>
            html {
                width: 100%;
                height: 100%;
            }
            body {
                width: 100%;
                height: 100%;
            }
            main {
                display: flex;
                justify-content:center;
                align-items:center;
                flex-direction: column;
                width: 100%;
                height: 100%;
            }
        </style>
    @show
    @vite(['node_modules/bootstrap/dist/css/bootstrap.css'])
</head>
<body>
<main>
    @if($errors->any())
        @foreach($errors->all() as $error)
            <div class="alert alert-danger" role="alert">
                {{$error}}
            </div>
        @endforeach
    @endif

    @if (session('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif
    @section('main')
    @show
</main>
    @section('js')
    @show
</body>
</html>
