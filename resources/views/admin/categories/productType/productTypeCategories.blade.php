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
                    <a href="#">Danh mục</a>
                    <i class="fa fa-circle"></i>
                </li>
                <li>
                    <span>Loại sản phẩm</span>
                </li>
            </ul>
        </div>
        <!-- END PAGE BAR -->
        </BR>
        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="icon-settings font-red"></i>
                            <span class="caption-subject font-red bold uppercase">DANH MỤC LOẠI SẢN PHẨM</span>
                        </div>

                    </div>
                    <div class="portlet-body">
                        <div class="table-toolbar">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="btn-group">
                                        <a  id="btnAddProductType" class="btn green btn-outline sbold uppercase" > Thêm mới
                                            <i class="fa fa-plus"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="btn-group pull-right">
                                        <button class="btn green btn-outline dropdown-toggle" data-toggle="dropdown">Tools
                                            <i class="fa fa-angle-down"></i>
                                        </button>
                                        <ul class="dropdown-menu pull-right">
                                            <li>
                                                <a href="javascript:;"> Xuất to Excel </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="treeProductType" > </div>
                    </div>
                </div>
                <!-- END EXAMPLE TABLE PORTLET-->
            </div>
        </div>
    </div>
    <!-- END CONTENT BODY -->
</div>

    @include('admin.categories.unit.addUnit')
@stop

@section('custom_js')
    <script>
        var path = '{{url('/')}}';
    </script>

    {{ HTML::script('public/assets/admintheme/pages/scripts/ui-tree.min.js') }}
    {{ HTML::script('public/assets/scripts/categories/productType.js') }}
@stop