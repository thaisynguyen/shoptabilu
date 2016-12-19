@extends('admin.layouts.admindashboard')
@section('section')
@include('alerts.errors')
@include('alerts.success')

<?php
    use Utils\commonUtils;
    $curpage =  $data->currentPage();
?>
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="page-content-wrapper">
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->

        <!-- BEGIN PAGE BAR -->
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li>
                    <a href="{{url('/adminhome')}}">Trang chủ</a>
                    <i class="fa fa-circle"></i>
                </li>
                <li>
                    <a href="#">Danh mục</a>
                    <i class="fa fa-circle"></i>
                </li>
                <li>
                    <span>Tiền tệ</span>
                </li>
            </ul>
        </div>
        <!-- END PAGE BAR -->
        </BR>
        @include('admin.categories.currency.currencyContent')
    </div>
    <!-- END CONTENT BODY -->

</div>

    @include('admin.categories.currency.addCurrency')
@stop

@section('custom_js')
    <script>
        var path = '{{url('/')}}';
        sortOnPageLoad();
    </script>
    {{ HTML::script('public/assets/scripts/categories/currency.js') }}
@stop