@csrf
<div class="mb-3">
    <label CLASS="form-label">Ον/νυμο Χρήστη*</label>
    <input type="text" name="name" class="form-control" value="{{$user->name??""}}" placeholder="Ον/νυμο Χρήστη">
</div>
<div class="mb-3">
    <label CLASS="form-label">Email Χρήστη*</label>
    <input type="email" name="email" class="form-control" value="{{$user->email??""}}" placeholder="email χρήστη">
</div>
<div class="mb-3">
    <label CLASS="form-label">Password Χρήστη*</label>
    <input type="password" name="password" class="form-control" placeholder="password χρήστη">
    <span class="form-text">Ο κωδικός αυτός δεν αποστέλλετε μέσω email</span>
</div>
