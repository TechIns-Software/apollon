@extends('layout.fullpage')
@php($title='Αλλαγή κωδικού')
@section('main')
    <div>
        <h1>@yield('title')</h1>
        <form method="POST" action="{{$route??route('password.email')}}">
            @csrf
            @section('extra_columns')
            @show
            <div class="mb-3">
              <label for="user-email" class="form-label">Email Χρήστη *</label>
              <input id="user-email"  name="email" class="form-control form-control-lg" type="email" placeholder="user@example.com" required>
            </div>
            <div class="mb-3">
                <button type="submit" class="btn btn-block btn-primary" id="submitBtn">Αποστολή Συνδέσμου επαναφοράς</button>
            </div>
        </form>
    </div>
@endsection
