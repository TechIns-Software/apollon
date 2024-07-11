<div id="msg">
    @if($errors->any())
        {!! implode('', $errors->all('<div class="alert alert-danger" role="alert">:message</div>')) !!}
    @endif
    @if(session()->has('message'))
        <div class="alert alert-success">
            {{ session()->get('message') }}
        </div>
    @endif
</div>
