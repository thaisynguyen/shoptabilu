@extends('admin.layouts.admindashboard')

@section('section')
    @include('alerts.errors')
    @include('alerts.success')

    <?php
        use Utils\commonUtils;
        //$curpage =  $data->currentPage();
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
                        <span>Sản phẩm</span>
                    </li>
                </ul>
            </div>
            <!-- END PAGE BAR -->
            </BR>

                    <!-- BEGIN EXAMPLE TABLE PORTLET-->
                    <div class="portlet light bordered">
                        <div class="portlet-title">
                            <div class="caption font-green">
                                <i class="icon-settings font-green"></i>
                                <span class="caption-subject ">Danh mục Sản phẩm</span>
                            </div>
                            <div class="tools"> </div>
                        </div>
                        <div class="portlet-body">
                            <table id="tblProduct" class="table table-striped table-bordered table-hover dt-responsive nowrap" width="100%">
                                <thead>
                                <tr>
                                    <th></th>
                                    <th >Product ID</th>
                                    <th >Mã SP</th>
                                    <th >Tên SP</th>
                                    <th >Barcode</th>
                                    <th >Loại</th>
                                    <th >Nhà SX</th>
                                    <th >Cân nặng</th>
									<th >Màu</th>
                                    <th class="all"></th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <!-- END EXAMPLE TABLE PORTLET-->

        </div>
        <!-- END CONTENT BODY -->
    </div>
@stop

@section('custom_js')
    <script>
        var path = '{{url('/')}}';
		 console.log(path);
    </script>

    {{ HTML::script('public/assets/scripts/categories/product.js') }}
@stop