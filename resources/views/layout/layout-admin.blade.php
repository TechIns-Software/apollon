@extends('layout.layout-common')

@section('nav-items')
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
                <a class="nav-link" href="{{route('user.list')}}">User Management</a>
            </li>
        </ul>
@endsection
