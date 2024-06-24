@extends('layout.layout-admin')

@section("css")
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
    <h1 class="mt-1">Επιχειρήσεις</h1>

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
                Λίστα Εταιρείων
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link"
               aria-current="page"
               data-bs-toggle="tab"
               data-bs-target="#stats-tab-pane"
               href="#stats-tab-pane"
               type="button"
               role="tab"
               aria-controls="stats-tab-pane"
               aria-selected="true">
                Στατιστικά
            </a>
        </li>
    </ul>
    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade" id="stats-tab-pane" role="tabpanel" aria-labelledby="stats-tab" tabindex="1">
            <div class="row mt-1">
                <div class="col-3">
                    <form id="statsForm" method="get" action="{{route('business.stats')}}">
                        <div class="input-group mb-3">
                            <input id="year" type="text" pattern="\d{4}" class="form-control yearInput"  placeholder="Εισαγωγή έτους" required/>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-calendar-plus"></i></button>
                        </div>
                        <ul class="form-years">
                        </ul>
                    </form>
                </div>
                <div id="statsContainer" class="col" style="height: 70vh;"></div>
            </div>
        </div>
        <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
            <div id="business_container" class="mt-1">
               <h2>Λίστα Επιχειρήσεων</h2>
                <form id="businessSearchForm" method="get" class="mt-2 mb-2" action="{{route('business.list')}}">
                    @csrf
                    <div class="input-group mb-3">
                        <input id="inputSearchField" name="name" class="form-control" value="{{$name}}" placeholder="Αναζητήση.. ">
                        <button id="cleanSearch" class="btn btn-outline-secondary" type="submit"><i class="fa fa-x"></i></button>
                        <button class="btn btn-secondary" type="submit"><i class="fa fa-search"></i></button>
                    </div>
                </form>
                <button class="btn btn-success" type="button" data-bs-toggle="modal" data-bs-target="#createBusiness" >
                    <i class="fa fa-plus"></i>&nbsp;Προσθήκη
                </button>
                <div class="scrollWrapper">
                   <table id="businessTable" class="table table-striped">
                       <thead>
                            <tr>
                                <th>Όνομα Επιχείρησης</th>
                                <th>#</th>
                            </tr>
                       </thead>
                       <tbody>
                            @include('business.components.listBusiness',['rows'=>$businesses])
                       </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="modal modal-lg fade" id="createBusiness" tabindex="-1" aria-labelledby="Δημιουργία Εταιρείας" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Προσθήκη Εταιρείας</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="infoForm" class="mt-2" method="POST" action="{{route('business.create')}}">

                    <div class="modal-body">
                        @include('business.components.businessAddEditForm',['route'=>'business.create'])
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-success">Αποθήκευση</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    @vite(["node_modules/jscroll/dist/jquery.jscroll.min.js",'resources/js/business/list.js'])
@endsection
