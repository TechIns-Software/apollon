@extends('layout.layout-admin')

@section('css')
    @vite("resources/css/scrollWrapper.css")
@endsection

@section('main')
    <h1>{{$business->name}}</h1>

    @include('components.msg')

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
                data-bs-target="#users-tab-pane"
                href="#users-tab-pane"
                type="button"
                role="tab"
                aria-controls="users-tab-pane" aria-selected="true">
                Διαχείρηση Χρηστών
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
                @include('business.components.businessAddEditForm',['business'=>$business])
                <button class="btn btn-success">Αποθήκευση</button>
            </form>
        </div>
        <div class="tab-pane fade show" id="products-tab-pane" role="tabpanel" aria-labelledby="products-tab" tabindex="1">
            @include("business.components.searchForm",[
                'action'=>'products.fetch',
                'id'=>"productSearchform",
                'business'=>$business,
                'placeholder'=>"Αναζητήστε έναν προϊόν",
                "inputSearchId"=>"productSearch"
            ])
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
                        @include('business.components.listProducts',['rows'=>$products])
                    </tbody>
                </table>
            </div>
        </div>
        <div class="tab-pane fade show" id="users-tab-pane" role="tabpanel" aria-labelledby="users-tab" tabindex="1">
            @include("business.components.searchForm",[
                'action'=>"business.user",
                'id'=>"userSearchForm",
                'business'=>$business,
                'searchValName'=>'searchterm',
                'placeholder'=>"Αναζητήστε έναν Χρήστη",
                "inputSearchId"=>"userSearch"
            ])
            <button class="btn btn-success" type="button" data-bs-toggle="modal" data-bs-target="#createUser" >
                <i class="fa-solid fa-user-plus"></i>&nbsp;Προσθήκη Χρήστη
            </button>
            <div id="userScroll" class="scrollWrapper" data-url="{{$users->nextPageUrl()}}">
                <table id="userListTable" class="table">
                    <thead>
                    <tr>
                        <th>Ον/νυμο</th>
                        <th>email</th>
                        <th>Ημ/νια Δημιουργίας</th>
                        <th>#</th>
                    </tr>
                    </thead>
                    <tbody>
                        @include('business.components.userList',['rows'=>$users])
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
        <div class="modal fade" id="createUser" tabindex="-1" aria-labelledby="Δημιουργία Χρήστη" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Δημιουργία Νέου χρήστη</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{route('business.user.create',['id'=>$business->id])}}">
                        <div class="modal-body">
                           @include('saasUser.components.userFormContents')
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
