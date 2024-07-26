<!DOCTYPE html>
<html>
<head>
    <title>Δρομολόγιο: {{$delivery->title}}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
        }

        header .row {
            width: 100%;
            clear: both;
            overflow: hidden; /* clearfix */
        }

        main {
            clear: both;
            width: 100%;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background-color: #f2f2f2;
            border: 1px solid #ddd;
            padding: 8px;
        }

        td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:nth-child(odd) {
            background-color: #ffffff;
        }
    </style>
</head>
<body>
<header>
        <div class="row clearfix">
            <h1 style="float: left;">{{config('app.name')}}</h1>
            <div style="float: right;">
                Ημ/νια Δημιουργία PDF: {{\Carbon\Carbon::now()->format('d/m/Y H:i:s')}}<br/>
                Καταχώρηση Δρομολογίου: {{(new Carbon\Carbon($delivery->created_at))->format('d/m/Y H:i:s')}}
            </div>
        </div>
        <div class="row clearfix">
            <div style="float: left; width: 50%;"><strong>Δρομολόγιο:</strong>&nbsp;{{$delivery->name}}&nbsp;({{$delivery->id}})</div>
            <div style="float: right;width: 50%;"><strong>Οδηγός:</strong>&nbsp;{{$delivery->driver->driver_name}}</div>
        </div>
</header>
<hr>
<main>
    <table>
        <thead>
            <tr>
                <th>id</th>
                <th>Στοιχεία Πελάτη</th>
                <th colspan="2">Προϊόντα</th>
            </tr>
        </thead>
        <tbody>
            @foreach($delivery->deliveryOrder as $order_sequence)
                @php $productCount = count($order_sequence->order->products); @endphp
                <tr>
                    <td>{{$order_sequence->order->id}}</td>
                    <td>
                        {{$order_sequence->order->client->region??'Άνγωστη'}} ({{$order_sequence->order->client->nomos??"Άγνωστος νομός"}})<br>
                        {{$order_sequence->order->client->name}} {{$order_sequence->order->client->surname}}<br>
                        {{$order_sequence->order->client->telephone}}
                        @if(!empty($order_sequence->order->client->phone_1))
                            <br>{{$order_sequence->order->client->phone_1}}
                        @endif
                        @if(!empty($order_sequence->order->client->phone_2))
                            <br>{{$order_sequence->order->client->phone_2}}
                        @endif
                    {{$order_sequence->order->description??'-'}}</td>
                    <td colspan="2" @if(!empty($productCount)) style="padding: 0px!important;" @endif>
                        @empty($productCount)
                            Καθόλου Προϊόντα
                        @else
                            <table style="margin: 0px;">
                                <thead>
                                    <tr>
                                        <th>Ονομα Προϊόντος</th>
                                        <th>Πόσο</th>
                                    </tr>
                                </thead>
                                @foreach($order_sequence->order->products as $product)
                                    <tr>
                                        <td>
                                            {{$product->product->name}}
                                        </td>
                                        <td>{{$product->ammount}}</td>
                                    </tr>
                                @endforeach
                            </table>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</main>
</body>
</html>
