@foreach($rows as $item)
    @include('business.components.userListItem',['item'=>$item])
@endforeach
