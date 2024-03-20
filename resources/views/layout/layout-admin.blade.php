{{--
    The view was copied-pasted from e-support project, afterwards was slighlt modified.
    So resecting the convention naming and for better maintainability I keep the file as is.
    Because also many projects do use same view organizing as well.
 --}}
@extends('layout.layout-common')

@section('nav-items')
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
                <a class="nav-link" href="{{route('user.list')}}">Διαχείρηση Χρηστών</a>
            </li>
        </ul>
@endsection
