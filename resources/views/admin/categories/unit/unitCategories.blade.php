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
                                        <a  id="btnAddUnit" class="btn green btn-outline sbold uppercase" > Thêm mới
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
                        <table class="table table-striped table-hover table-bordered" id="sample_editable_1">
                            <thead>
                            <tr>
                                <th> STT </th>
                                <th> Mã </th>
                                <th> Tên </th>
                                <th> Sửa </th>
                                <th> Xóa </th>
                            </tr>
                            </thead>
                            <tbody>

                            <?php
                            $i = 0;
                            $stt = (($curpage-1) * CommonUtils::ITEM_PER_PAGE_DEFAULT) + 1;
                            foreach($data as $row){
                            $i++;
                            ?>



                            <tr>
                                <td class="text-center"> <?php  echo $stt; $stt++; ?>  </td>
                                <td id="td-code-{{$row->unit_id}}"> <?php echo $row->unit_code; ?> </td>
                                <td id="td-name-{{$row->unit_id}}"> <?php echo $row->unit_name; ?> </td>
                                <td>
                                    <a class="td-edit-unit" data-id="{{$row->unit_id}}"> Sửa </a>

                                </td>
                                @include('admin.categories.unit.updateUnit', array('unit_id' => $row->unit_id,
                                                                                    'unit_code' => $row->unit_code,
                                                                                    'unit_name' => $row->unit_name,

                                ))
                                @include('admin.categories.unit.deleteUnit', array('unit_id' => $row->unit_id,
                                                                                    'unit_code' => $row->unit_code,
                                                                                    'unit_name' => $row->unit_name,

                                ))
                                <td>
                                    <a class='td-delete-unit' data-id="{{$row->unit_id}}"> Xóa </a>
                                </td>
                            </tr>
                            <?php
                            }
                            ?>
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

    @include('admin.categories.unit.addUnit')
@stop

@section('custom_js')
    <script>
        var path = '{{url('/')}}';
    </script>
    {{ HTML::script('public/assets/scripts/categories/unit.js') }}
@stop