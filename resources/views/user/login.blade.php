@extends('layout.fullpage')

@section('main')



    <div>
        <h1>@yield('title',$title??'Είσοδος Διαχειριστή')</h1>

        <form method="POST" action="{{route($auth_route??'auth.login')}}">
                @csrf
                @section('extra_fields')
                @show
                    <div class="mb-3">
                        <label id="username" class="form-label">Email</label>
                        <input id="username" class="form-control" type="email" name="email"/>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input id="password" class="form-control" type="password" name="password">
                    </div>

                <div class="mb-3 d-grid gap-2">
                    <button type="submit" class="btn btn-lg btn-primary" id="submitBtn">Είσοδος</button>
                    <a class="btn btn-link btn-block mt-2" href="{{$reset_password_link??route('user.reset-password')}}">Ξεχάσατε τον κωδικό σας;</a>
                </div>
        </form>
    </div>
@endsection

@section('js')
@endsection

