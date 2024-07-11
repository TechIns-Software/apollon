<form id="{{$id}}" method="get" class="mt-2 mb-2" action="{{route($action)}}">
    @csrf
    <input type="hidden" name="business_id" value="{{$business->id}}">
    <div class="input-group mb-3">
        <input id="{{$inputSearchId}}" name="{{ $searchValName??"name" }}" class="form-control inputSearchField" placeholder="{{$placeholder??""}}">
        <button class="cleanSearch btn btn-outline-secondary" data-clean-id="{{$inputSearchId}}" type="button"><i class="fa fa-x"></i></button>
        <button class="btn btn-secondary" type="submit"><i class="fa fa-search"></i></button>
    </div>
</form>
