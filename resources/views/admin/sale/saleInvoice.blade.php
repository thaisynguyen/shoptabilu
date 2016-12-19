@extends('admin.layouts.admindashboard')
@section('section')
@include('alerts.errors')
@include('alerts.success')

<?php
    use Utils\commonUtils;
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
                    <a href="#">Bán hàng</a>
                    <i class="fa fa-circle"></i>
                </li>
                <li>
                    <span>Quản lý bán hàng</span>
                </li>
            </ul>
        </div>
        <!-- END PAGE BAR -->
        </BR>
        @include('admin.sale.saleInvoiceList')
    </div>
    <!-- END CONTENT BODY -->

</div>

    @include('admin.sale.addSaleInvoice', array('optionProductType' => $optionProductType, 'optionProduct' => $optionProduct))
@stop

@section('custom_js')
    <script>
        var path = '{{url('/')}}';
        sortOnPageLoad();
    </script>
    {{ HTML::script('public/assets/scripts/sale/saleInvoice.js') }}
@stop