@extends('admin.layouts.admindashboard')
@section('section')
@include('alerts.errors')
@include('alerts.success')

<?php
    use Utils\commonUtils;
    $curpage =  $data->currentPage();
?>
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
                    <span>Đơn vị tính</span>
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
                            <span class="caption-subject bold uppercase">Basic</span>
                        </div>
                        <div class="tools"> </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover dt-responsive nowrap" width="100%" id="sample_2">
                            <thead>
                            <tr>
                                <th></th>
                                <th >Mã SP</th>
                                <th >Tên SP</th>
                                <th >Loại</th>
                                <th >Nhà SX</th>
                                <th >Đơn VT</th>
                                <th >Barcode</th>
                                <th >Giá</th>
                                <th >Loại tiền tệ</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <th></th>
                                <td>NK001</td>
                                <td>Nikon D90</td>
                                <td>Máy Ảnh</td>
                                <td>Nikon</td>
                                <td>cái</td>
                                <td>2343241213</td>
                                <td>10,000,000</td>
                                <td>VNĐ</td>
                            </tr>
                            <tr>
                                <th></th>
                                <td>NK001</td>
                                <td>Nikon D90</td>
                                <td>Máy Ảnh</td>
                                <td>Nikon</td>
                                <td>cái</td>
                                <td>2343241213</td>
                                <td>10,000,000</td>
                                <td>VNĐ</td>
                            </tr>

                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- END EXAMPLE TABLE PORTLET-->

    </div>
    <!-- END CONTENT BODY -->
</div>

<script>
$(document).ready(function(){
    sortOnPageLoad();
});
</script>
@stop