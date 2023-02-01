@extends('errors::forbidden-layout')

@section('code', '404')

@section('title', __('Page Not Found'))

@section('image')

<div style="background-image: url('error.png')" class="absolute pin bg-no-repeat md:bg-left lg:bg-center">
</div>

@endsection

@section('message', __('Sorry, the page you are looking for could not be found.'))
