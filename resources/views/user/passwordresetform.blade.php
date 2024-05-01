@extends('layout.fullpage')

@section('css')
    @parent
    @vite(['node_modules/@fortawesome/fontawesome-free/css/all.css'])
@endsection

@section('main')
     <form method="POST" action="{{$route??route('password.reset.submit')}}" >
        @csrf
        <input type="hidden" name="token" value="{{$token}}">
        @if(!empty($email))
            <input type="hidden" name="email" value="{{$email}}">
        @endif
         <div id="passwordVisible" class="input-group mb-1">
            <input  id="password" type="password" name="password"  class="form-control" placeholder="Νέος Κωδικός Πρόβασης" required/>
             <button class="btn btn-secondary" type="button"  onclick="toogleEyePasword('password','passwordEye')">
                 <i id="passwordEye" class="fa-regular fa-eye"></i>
             </button>
         </div>
        <div class="mb-3">
           <input type="password" name="password_confirmation" placeholder="Επανάληψη κωδικού όπως και πάνω" class="form-control" required/>
        </div>
        <div class="mb-3 d-grid gap-2">
            <button type="submit" class="btn btn-primary">Αλλαγή κωδικού</button>
        </div>
    </form>
@endsection

@section('js')
    @vite(['resources/js/user/profile.js'])
@endsection
