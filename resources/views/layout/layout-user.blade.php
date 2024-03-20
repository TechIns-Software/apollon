@extends('layout.layout-common')

@section('nav-items')
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="{{route('customers')}}">Διαχείρηση Πελατών</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{route('user.page.admin')}}">Σελίδα Χρήστη</a>
            </li>
        </ul>
@endsection
