@foreach ($rows as $row)
    <tr>
        <td>
            <span id="title-product-{{$row->id}}">{{$row->name}}</span>
            <form method="POST" id="edit-form-{{$row->id}}" data-success="title-product-{{$row->id}}" class="productEditForm" action="{{route('product.edit')}}" style="display: none">
                @csrf
                <div class="input-group mb-3">
                    <input name="products[{{$row->id}}]" class="form-control"  value="{{$row->name}}">
                    <button class="btn btn-success" type="submit" id="button-addon1"><i class="fa fa-save"></i>Αποθήκευση</button>
                </div>
            </form>
        </td>
        <td>
            <a class="btn btn-success toggle-visibility" href="#edit-form-{{$row->id}}" data-hide="title-product-{{$row->id}}"><i class="fa fa-pencil"></i>Επεξεργασία</a>
        </td>
    </tr>
@endforeach
@if ($rows->hasMorePages())
    <tr style="display: none">
        <td>
            <a class="jscroll-next" href="{{ $rows->nextPageUrl() }}"></a>
        </td>
    </tr>
@endif
