@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.CATEGORIES'). ' ' .Config::get('constant.AREA'))
@section('section')
@include('alerts.success')
@include('alerts.errors')

<?php
use Utils\commonUtils;
$curpage =  $data->currentPage();

$accessLevel = Session::get('saccess_level');
$isView = Session::get('sis_view');
$hiddenExportExcel =  ($accessLevel <= 3 && $isView == 0) ? '' : 'hidden';
$hiddenARCompany   =  ($accessLevel == 1 && $isView == 0) ? '' : 'hidden';

?>

    <div id="wrapper" >
        <form id='frmArea' action="{{action('CategoriesController@areaCategories')}}" method="POST" class="form-horizontal margin-left-15 margin-top-a40">
            <input type="hidden" name="_token" value="<?= csrf_token();?>"/>
            <div class="row margin-top-55">
                <div class="form-group col-sm-12">
                    <div class="col-sm-10">
                        <?php if(isset($key)){?>
                        <input type="text" class="col-xs-9 col-sm-5" id="txtKeySearch" name="txtKeySearch" value="<?php echo $key?>">
                        <?php } else {?>
                        <input type="text" class="col-xs-9 col-sm-5" id="txtKeySearch" name="txtKeySearch" placeholder="Tìm tổ/quận/huyện.....">
                        <?php }?>
                        <input type="hidden" class="form-control" id="countArea" value="<?php echo count($data);?>"></a>
                        <button  type="submit" id="btnSearch" class="btn btn-primary btn_search_emp"><i class="glyphicon glyphicon-search"></i> Tìm</button>
                    </div>
                </div>
            </div>
        </form>
        <div class="row margin-form">
            <div class="col-sm-12">
                <span class="pull-right pading-right-22">
                    <a role="button" id="exportArea" class="btn btn-primary pull-right {{ $hiddenExportExcel }}"
                       href="<?= URL::to('exportArea');?>"><i class="fa fa-plus"></i> Xuất Excel</a>
                    <a role="button" id="addArea" class="btn btn-primary pull-right change-color margin-btnadd {{ $hiddenARCompany }}"
                       href="<?= URL::to('addArea');?>"><i class="fa fa-plus"></i> Thêm Mới</a>
                    <a role="button" id="deleteArea" class="btn btn-primary pull-right change-color margin-btnadd {{ $hiddenARCompany }}"
                    ><i class="fa fa-times"></i> Xóa Nhiều</a>
                </span>
            </div>
            <div class="col-sm-12">
                <span class="pull-right pading-right-22">
                    <?php
                        echo $data->setPath(action('CategoriesController@areaCategories'))->render();
                    ?>
                </span>
            </div>
            <table class="table-common" id="tblArea">
                <thead>
                <tr>
                    <?php if($accessLevel < 2 && $isView == 0) {?>
                    <th class="col-sm-1">STT</th>
                    <th class="col-sm-3 sortable" sort_key="company_name">Phòng/Đài/MBF HCM<i class="fa pull-right unsort fa-sort"></i></th>
                    <th class="col-sm-2 sortable" sort_key="area_code">Mã<i class="fa pull-right unsort fa-sort"></i></th>
                    <th class="col-sm-3 sortable" sort_key="area_name">Tên<i class="fa pull-right unsort fa-sort"></i></th>
                    <th class="col-sm-1"></th>
                    <th class="col-sm-1"></th>
                    <th ></th>
                    <?php } else {?>
                    <th class="col-sm-1">STT</th>
                    <th class="col-sm-4 sortable" sort_key="company_name">Phòng/Đài/MBF HCM<i class="fa pull-right unsort fa-sort"></i></th>
                    <th class="col-sm-3 sortable" sort_key="area_code">Mã<i class="fa pull-right unsort fa-sort"></i></th>
                    <th class="col-sm-4 sortable" sort_key="area_name">Tên<i class="fa pull-right unsort fa-sort"></i></th>
                    <?php } ?>
                </tr>
                </thead>
                <tbody>

                <?php
                    $i = 0;
                    $stt = (($curpage-1) * CommonUtils::ITEM_PER_PAGE_DEFAULT) + 1;
                    foreach($data as $row){
                     $i++;
                ?>
                <?php if($i % 2 == 0):?>
                <tr class="background-color-smoke">
                <?php else:?>
                <tr>
                    <?php endif; ?>
                    <td class="text-center"><?php  echo $stt; $stt++; ?>  </td>
                    <td><?php echo $row->company_name; ?></td>
                    <td><?php echo $row->area_code; ?></td>
                    <td><?php echo $row->area_name; ?></td>
                    <?php if($accessLevel < 2 && $isView == 0) {?>
                    <td class="order-column text-center"><input id="chkSelectedRow" type="checkbox" rowId="{{$row->id;}}" rowCode="{{$row->area_code;}}" rowName="{{$row->area_name;}}"> </td>
                    <td class="table-icon text-center">
                        <a href="<?php echo 'updateArea/'.$row->id ?>" role="button" title="Cập nhật">
                            <i class="fa fa-pencil-square-o"></i>
                        </a>
                    </td>
                    <td class="order-column table-icon text-center" >
                        <a role="button"  href="#" data-target=".dialog-delete-goal-<?php echo $row->id; ?>" data-toggle="modal">
                            <i class="fa fa-trash" title="Xóa" ></i>
                        </a>
                        @include('popup.confirmDelete', array('rowId' => $row->id
                                                      , 'data' => $row->id
                                                      , 'strName' => $row->area_name
                                                      , 'actionName'  => 'CategoriesController@deleteArea'
                                                      ))

                    </td>
                    <?php }?>
                </tr>
                <?php }?>
                </tbody>
            </table>
            <div class="col-sm-12">
                <span class="pull-right pading-right-22">
                    <?php
                        echo $data->setPath(action('CategoriesController@areaCategories'))->render();
                    ?>
                </span>
            </div>
        </div>
        @include('popup.confirmDeleteSelected', array('actionName' => 'CategoriesController@deleteSelectedArea'))
        <script>
            $(document).ready(function() {
                //sort on column
                sortOnPageLoad();

                //delete multi row
                btnDeleteMultiRowClick($("#deleteArea"), $("#tblArea"));

                $('#btnSearch').click(function(){
                    var pathname = window.location.pathname;
                    var action = $('#frmArea').attr('action', pathname + '?search=' + $('#txtKeySearch').val());
                });
            });
        </script>
    </div>
@stop