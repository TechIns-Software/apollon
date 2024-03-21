
@foreach ($rows as $row)
    <tr>
        <td>{{$row->name}}</td>
        <td>
            <a class="btn btn-link">Καρτέλα Επιχείρησης</a>
        </td>
    </tr>
@endforeach
@if ($rows->hasMorePages())
    <tr style="display: none">
        <td>
            <a class="jscroll-next" href="{{ $versions->nextPageUrl() }}"></a>
        </td>
    </tr>
@endif
