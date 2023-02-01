@extends('layouts.app')

@section('content')

<form method='post' action="{{ route('removeUser', ['eventid' => $event->eventid]) }}" enctype="multipart/form-data"> 

 @csrf

<h2> Remove a user from an event </h2>    

<div class="form-group mb-3">
    <label for="usremail" class="form-label">Email</label>
    <input id="usremail" type="email" name="usremail" placeholder="Email of the user you want to remove from this event" class="input-group form-control">
</div>
<button type="submit" class="input-group btn btn-danger">
    Remove
</button>
</form>
@endsection