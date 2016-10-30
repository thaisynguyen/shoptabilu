@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.TITLE_PERMISSION_VIEW_DETAIL'))
@section('section')
    @include('alerts.errors')
    @include('alerts.success')

@stop