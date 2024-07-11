@csrf
<div class="mb-3">
    <label CLASS="form-label">Ον/νυμο Χρήστη*</label>
    <input type="text" name="name" class="form-control @if($errors->has('name')) is-invalid @endif" value="{{$user->name??""}}" placeholder="Ον/νυμο Χρήστη">
    @if($errors->has('name'))
        <div class="invalid-feedback">
            {{$errors->first('name')}}
        </div>
    @endif
</div>
<div class="mb-3">
    <label CLASS="form-label">Email Χρήστη*</label>
    <input type="email" name="email" class="form-control @if($errors->has('email')) is-invalid @endif" value="{{$user->email??""}}" placeholder="email χρήστη">
    @if($errors->has('email'))
        <div class="invalid-feedback">
            {{$errors->first('email')}}
        </div>
    @endif
</div>
<div class="mb-3">
    <label CLASS="form-label">Password Χρήστη*</label>
    <input type="password" name="password" class="form-control @if($errors->has('password')) is-invalid @endif" placeholder="password χρήστη">
    @if($errors->has('password'))
        <div class="invalid-feedback">
            {{$errors->first('password')}}
        </div>
    @endif
    <span class="form-text">Ο κωδικός αυτός δεν αποστέλλετε μέσω email</span>
</div>
