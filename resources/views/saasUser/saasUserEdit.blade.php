@extends('layout.layout-admin')

@section('main')
    <h1>Επεξεργασία χρήστη {{$user->name}}</h1>
    <a href="{{route('business.info',['id'=>$user->business_id])}}" class="btn btn-link"><i class="fa fa-arrow-left"></i>Πίσω στην Eταιρεία {{$user->business->name}}</a>
    @include('components.msg')
    <form method="POST" action="{{route('business.user.edit',['id'=>$user->id])}}">
        @include('saasUser.components.userFormContents',['user'=>$user])
        <button type="submit" class="btn btn-success">Αποθήκευση</button>
    </form>
@endsection
