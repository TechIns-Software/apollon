
@foreach ($rows as $row)
   @include('business.components.listBusinessItem',['row'=>$row])
@endforeach
@if ($rows->hasMorePages())
    <tr style="display: none">
        <td>
            <a class="jscroll-next" href="{{ $rows->nextPageUrl() }}"></a>
        </td>
    </tr>
@endif
