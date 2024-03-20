@extends('layout.layout-common')

@section('main')
    <div class="row mt-4">
        <div class="col-12">
            <h1 class="pageTitle">Αλλαγή Κωδικού </h1>
        </div>
        <form id="profileForm" method="POST" action="{{route('user.profile')}}"  class="needs-validation" novalidate>
            @csrf
            <div class="row">
                <label for="password" class="col-sm-2" >Password</label>
                <div class="col-sm-12">
                    <div id="passwordVisible" class="input-group mb-1">
                        <input id="password" type="password" class="form-control @if($errors->has('password') || $errors->has('password_confirmation')) is-invalid @endif" name="password" placeholder="Κωδικός Πρόσβασης" required>
                    </div>
                    <input id="password-repeat" type="password" name="password_confirmation" class="form-control @if($errors->has('password')||$errors->has('password_confirmation')) is-invalid @endif" placeholder="Εισάγετε τον κώδικο πρόβασης όπως και απο πάνω" required>
                    <div class="invalid-feedback">
                        @if($errors->has('password'))
                            {{$errors->first('password')}}
                        @elseif($errors->has('password_confirmation'))
                            {{$errors->first('password_confirmation')}}
                        @else
                            Παρακαλώ συμπληρώστε το password του χρήστη.
                        @endif
                    </div>
                </div>
            </div>
            <div class="mt-1">
                <button type="submit" class="btn btn-primary" >Save</button>
            </div>
        </form>
    </div>
@endsection

@section('js')
    @vite(['resources/js/user/profile.js'])
@endsection
