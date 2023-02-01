@extends('errors::forbidden-layout')

@section('title', __('Unauthorized'))
@section('code', '401')
@section('message', __('Unauthorized Access'))
