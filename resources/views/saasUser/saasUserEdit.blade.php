@extends('layout.layout-admin')

@section('main')
    <h1>Επεξεργασία χρήστη {{$user->name}}</h1>
    <a href="{{route('business.info',['id'=>$user->business_id])}}">Πίσω στην Σταιρεία {{$user->business->name}}</a>
    <form>
        @include('saasUser.components.userFormContents',['user'=>$user])
        <button type="submit" class="btn btn-success">Αποθήκευση</button>
    </form>
@endsection
