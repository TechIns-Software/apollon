@extends('layout.layout-admin')

@section('main')
    <h1>{{$business->name}}</h1>
    <form method="POST" action="{{route('business.edit')}}">
        @csrf
        <input type="hidden" name="business_id" value="{{$business->id}}" />
        <div class="row mb-1 g-3 align-items-center">
            <div class="col-2">
                <label for="name" class="col-form-label">Όνομα Εταιρείας*</label>
            </div>
            <div class="col">
                <input type="text" id="name" name="name" value="{{$business->name}}" class="form-control" required>
            </div>
        </div>
        <div class="row mb-1 g-3 align-items-center">
            <div class="col-2">
                <label for="active" class="col-form-label">Ενεργό</label>
            </div>
            <div class="col">
                <input type="checkbox" id="active" @if($business->is_active) checked @endif value="true"  class="form-check-input">
            </div>
        </div>
        <div class="row mb-1 g-3 align-items-center">
            <div class="col-2">
                <label for="expiration_date" class="col-form-label">Ημ/νια Λήξη Συνδρομής</label>
            </div>
            <div class="col">
                <input type="text" id="expiration_date" name="expiration_date" value="{{$business->expiration_date}}" class="form-control">
            </div>
        </div>
        <div class="row mb-1 g-3 align-items-center">
            <div class="col-2">
                <label for="afm" class="col-form-label">ΑΦΜ</label>
            </div>
            <div class="col">
                <input type="text" id="afm" name="var_num" value="{{$business->vat}}" class="form-control">
            </div>
        </div>
        <div class="row mb-1 g-3 align-items-center">
            <div class="col-2">
                <label for="doy" class="col-form-label">ΔΟΥ</label>
            </div>
            <div class="col">
                <input type="text" id="doy" name="doy" value="{{$business->doy}}" class="form-control">
            </div>
        </div>
        <button class="btn btn-success">Αποθήκευση</button>
    </form>
@endsection
