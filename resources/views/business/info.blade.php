@extends('layout.layout-admin')

@section('css')
    <style>
        .scrollWrapper {
            overflow-y: scroll;
            max-height: 70vh;
        }

        .scrollWrapper thead tr th {
            position: sticky;
            top: 0;
            z-index: 99;
        }
    </style>
@endsection

@section('main')
    <h1>{{$business->name}}</h1>

    <div id="msg">

    </div>

    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active"
               aria-current="page"
               data-bs-toggle="tab"
               data-bs-target="#home-tab-pane"
               href="#home-tab-pane"
               type="button"
               role="tab"
               aria-controls="home-tab-pane"
               aria-selected="true">
                Προφίλ Εταιρείας
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a
                class="nav-link"
                aria-current="page"
                data-bs-toggle="tab"
                data-bs-target="#products-tab-pane"
                href="#products-tab-pane"
                type="button"
                role="tab"
                aria-controls="products-tab-pane" aria-selected="true">
                Διαχείρηση Προϊόντων
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a
                class="nav-link"
                aria-current="page"
                data-bs-toggle="tab"
                data-bs-target="#stats-tab-pane"
                href="#stats-tab-pane"
                type="button"
                role="tab"
                aria-controls="stats-tab-pane"
                aria-selected="true"
            >
                Στατιστικά Παραγγελιών
            </a>
        </li>
    </ul>

    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
            <form id="infoForm" class="mt-2" method="POST" action="{{route('business.edit')}}">
                @csrf
                <input type="hidden" name="business_id" value="{{$business->id}}" />
                <div class="row mb-1 g-3 align-items-center">
                    <div class="col-2">
                        <label for="name" class="col-form-label">Όνομα Εταιρείας*</label>
                    </div>
                    <div class="col">
                        <input type="text" id="name" name="name" value="{{$business->name}}" class="form-control" required>
                        <div class="invalid-tooltip">
                        </div>
                    </div>
                </div>
                <div class="row mb-1 g-3 align-items-center">
                    <div class="col-2">
                        <label for="active" class="col-form-label">Ενεργό</label>
                    </div>
                    <div class="col">
                        <input type="checkbox" id="active" @if($business->is_active) checked @endif  name="active" class="form-check-input">
                        <div class="invalid-tooltip">
                        </div>
                    </div>
                </div>
                <div class="row mb-1 g-3 align-items-center">
                    <div class="col-2">
                        <label for="expiration_date" class="col-form-label">Ημ/νια Λήξης Συνδρομής</label>
                        <div class="invalid-tooltip">
                        </div>
                    </div>
                    <div class="col">
                        <input type="date" id="expiration_date" name="expiration_date" value="{{$business->expiration_date}}" class="form-control">
                    </div>
                </div>
                <div class="row mb-1 g-3 align-items-center">
                    <div class="col-2">
                        <label for="afm" class="col-form-label">ΑΦΜ</label>
                    </div>
                    <div class="col">
                        <input type="text" id="afm" name="var_num" value="{{$business->vat}}" class="form-control">
                        <div class="invalid-tooltip">
                        </div>
                    </div>
                </div>
                <div class="row mb-1 g-3 align-items-center">
                    <div class="col-2">
                        <label for="doy" class="col-form-label">ΔΟΥ</label>
                    </div>
                    <div class="col">
                        <input type="text" id="doy" name="doy" value="{{$business->doy}}" class="form-control">
                        <div class="invalid-tooltip">
                        </div>
                    </div>
                </div>
                <button class="btn btn-success">Αποθήκευση</button>
            </form>
        </div>
        <div class="tab-pane fade show" id="products-tab-pane" role="tabpanel" aria-labelledby="products-tab" tabindex="1">
            <form id="productSearchform" method="get" class="mt-2 mb-2" action="{{route('products.fetch')}}">
                @csrf
                <input type="hidden" name="business_id" value="{{$business->id}}">
                <div class="input-group mb-3">
                    <input id="inputSearchField" name="name" class="form-control" placeholder="Αναζητήστε ένα προϊόν ">
                    <button id="cleanSearch" class="btn btn-outline-secondary" type="submit"><i class="fa fa-x"></i></button>
                    <button class="btn btn-secondary" type="submit"><i class="fa fa-search"></i></button>
                </div>
            </form>
            <button class="btn btn-success" type="button" data-bs-toggle="modal" data-bs-target="#createProduct" >
                <i class="fa fa-plus"></i>&nbsp;Προσθήκη Προϊόντος
            </button>
            <div id="productScroll" class="scrollWrapper">
                <table id="productListTable" class="table">
                    <thead>
                        <tr>
                            <th>Ονομα Προϊόντος</th>
                            <th>#</th>
                        </tr>
                    </thead>
                    <tbody>
                        @include('components.listProducts',['rows'=>$products])
                    </tbody>
                </table>
            </div>
        </div>
        <div class="tab-pane fade show" id="stats-tab-pane" role="tabpanel" aria-labelledby="stats-tab" tabindex="1">
            <div class="row mt-1">
                <div class="col-3">
                    <form id="statsForm" method="get" action="{{route('order.stats',['id'=>$business->id])}}">
                        <div class="input-group mb-3">
                            <input id="year" type="text" pattern="\d{4}" class="form-control yearInput"  placeholder="Εισαγωγή έτους" required/>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-calendar-plus"></i></button>
                        </div>
                        <ul class="form-years">
                        </ul>
                    </form>
                </div>
                <div id="orderStatsWrapper" class="col" style="height: 70vh;"></div>
            </div>
        </div>
    </div>

        <div class="modal fade" id="createProduct" tabindex="-1" aria-labelledby="Δημιουργία Προϊόντος" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Προσθήκη Προϊόντος</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="createProductForm" method="post" action="{{route('product.add')}}">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="business_id" value="{{$business->id}}">
                            <input name="name" class="form-control" placeholder="Ονομα Προϊόντος ">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ακύρωση</button>
                            <button type="submit" class="btn btn-success"><i class="fa fa-save"></i>&nbsp;Αποθήκευση</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
@endsection

@section('js')
    @vite(['resources/js/business/info.js'])
@endsection
