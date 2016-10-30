@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.TITLE_PERMISSION_USER'))
@section('section')
    @include('alerts.errors')
    @include('alerts.success')

@stop