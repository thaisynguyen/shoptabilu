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
        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet light portlet-fit bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="icon-settings font-red"></i>
                            <span class="caption-subject font-red sbold uppercase">DANH MỤC ĐƠN VỊ TÍNH</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-toolbar">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="btn-group">
                                        <button id="sample_editable_1_new" class="btn green"> Thêm mới
                                            <i class="fa fa-plus"></i>
                                        </button>
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
                        <table class="table table-striped table-hover table-bordered" id="sample_editable_1">
                            <thead>
                            <tr>
                                <th> STT </th>
                                <th> Mã </th>
                                <th> Tên </th>
                                <th> Ghi Chú </th>
                                <th> Sửa </th>
                                <th> Xóa </th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td> alex </td>
                                <td> Alex Nilson </td>
                                <td> 1234 </td>
                                <td class="center"> power user </td>
                                <td>
                                    <a class="edit" href="javascript:;"> Edit </a>
                                </td>
                                <td>
                                    <a class="delete" href="javascript:;"> Delete </a>
                                </td>
                            </tr>
                            <tr>
                                <td> lisa </td>
                                <td> Lisa Wong </td>
                                <td> 434 </td>
                                <td class="center"> new user </td>
                                <td>
                                    <a class="edit" href="javascript:;"> Edit </a>
                                </td>
                                <td>
                                    <a class="delete" href="javascript:;"> Delete </a>
                                </td>
                            </tr>
                            <tr>
                                <td> nick12 </td>
                                <td> Nick Roberts </td>
                                <td> 232 </td>
                                <td class="center"> power user </td>
                                <td>
                                    <a class="edit" href="javascript:;"> Edit </a>
                                </td>
                                <td>
                                    <a class="delete" href="javascript:;"> Delete </a>
                                </td>
                            </tr>
                            <tr>
                                <td> goldweb </td>
                                <td> Sergio Jackson </td>
                                <td> 132 </td>
                                <td class="center"> elite user </td>
                                <td>
                                    <a class="edit" href="javascript:;"> Edit </a>
                                </td>
                                <td>
                                    <a class="delete" href="javascript:;"> Delete </a>
                                </td>
                            </tr>
                            <tr>
                                <td> alex </td>
                                <td> Alex Nilson </td>
                                <td> 1234 </td>
                                <td class="center"> power user </td>
                                <td>
                                    <a class="edit" href="javascript:;"> Edit </a>
                                </td>
                                <td>
                                    <a class="delete" href="javascript:;"> Delete </a>
                                </td>
                            </tr>
                            <tr>
                                <td> webriver </td>
                                <td> Antonio Sanches </td>
                                <td> 462 </td>
                                <td class="center"> new user </td>
                                <td>
                                    <a class="edit" href="javascript:;"> Edit </a>
                                </td>
                                <td>
                                    <a class="delete" href="javascript:;"> Delete </a>
                                </td>
                            </tr>
                            <tr>
                                <td> gist124 </td>
                                <td> Nick Roberts </td>
                                <td> 62 </td>
                                <td class="center"> new user </td>
                                <td>
                                    <a class="edit" href="javascript:;"> Edit </a>
                                </td>
                                <td>
                                    <a class="delete" href="javascript:;"> Delete </a>
                                </td>
                            </tr>
                            <tr>
                                <td> alex </td>
                                <td> Alex Nilson </td>
                                <td> 1234 </td>
                                <td class="center"> power user </td>
                                <td>
                                    <a class="edit" href="javascript:;"> Edit </a>
                                </td>
                                <td>
                                    <a class="delete" href="javascript:;"> Delete </a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- END EXAMPLE TABLE PORTLET-->
            </div>
        </div>
    </div>
    <!-- END CONTENT BODY -->
</div>

<script>
$(document).ready(function(){
    sortOnPageLoad();
});
</script>
@stop