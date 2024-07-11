<tr>
    <td>
        {{ $item->name }}
    </td>
    <td>
        {{ $item->email }}
    </td>
    <td>{{ (new \Carbon\Carbon($item->created_at))->format("d-m-Y H:i:s")}}</td>
    <td>
        <a href="{{route("saasuser.info",['id'=>$item->id])}}" class="btn btn-success"><i class="fa fa-pencil"></i> Επεξεργασία</a>
    </td>
</tr>
