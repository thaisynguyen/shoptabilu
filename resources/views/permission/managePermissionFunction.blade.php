@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.TITLE_PERMISSION_FUNCTION'))
@section('section')
    @include('alerts.errors')
    @include('alerts.success')

@stop