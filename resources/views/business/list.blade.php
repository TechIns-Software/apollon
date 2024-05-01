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
    <h1>Επιχειρήσεις</h1>
    <div id="business_container" class="scrollWrapper">
       <table class="table table-striped">
           <thead>
                <tr>
                    <th>Όνομα Επιχείρησης</th>
                    <th>#</th>
                </tr>
           </thead>
           <tbody>
                @include('components.listBusiness',['rows'=>$businesses])
           </tbody>
        </table>
    </div>
@endsection

@section('js')
    @vite(["node_modules/jscroll/dist/jquery.jscroll.min.js",'resources/js/business/list.js'])
@endsection
