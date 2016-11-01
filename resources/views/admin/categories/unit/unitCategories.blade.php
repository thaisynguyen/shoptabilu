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
                                        <a  id="demo_4" class="btn green btn-outline sbold uppercase" > Thêm mới
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
                                <td> <?php echo $row->unit_code; ?> </td>
                                <td> <?php echo $row->unit_name; ?> </td>
                                <td>
                                    <a class="edit" href="javascript:;"> Edit </a>
                                </td>
                                <td>
                                    <a class="delete" href="javascript:;"> Delete </a>
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
<div class="modal fade" id="addPosition" tabindex="-1" role="basic" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        <form>
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title text-normal text-bold"><?php echo 'Add Position';?></h4>
                </div>
                <div class="modal-body text-normal">
                    <div class="form-group has-success">
                        <label class="control-label"><?php echo 'Code';?> (<span class="input-require">*</span>)</label>
                        <div class="input-icon right">
                            <input type="text" class="form-control add-data-position" id="code" required> </div>
                    </div>
                    <div class="form-group has-success">
                        <label class="control-label"><?php echo 'Name';?> (<span class="input-require">*</span>)</label>
                        <div class="input-icon right">
                            <input type="text" class="form-control add-data-position" id="name" required> </div>
                    </div>
                    <div class="form-group has-success">
                        <label class="control-label"><?php echo 'Description';?> </label>
                        <div class="input-icon right">
                            <textarea type="text" class="form-control" id="description" rows="3"> </textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn blue btn-act btn-smooth" id="savePosition"><?php echo 'Save';?></button>
                    <button type="button" class="btn default btn-act btn-smooth" data-dismiss="modal"><?php echo 'Close';?></button>
                </div>
            </div>
            <!-- /.modal-content -->
        </form>

    </div>
    <!-- /.modal-dialog -->
</div>


{{ HTML::script('public/assets/scripts/categories/unit.js') }}

@stop