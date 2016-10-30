@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.CATEGORIES') . ' ' . Config::get('constant.GROUP'))
@section('section')
@include('alerts.success')

<?php
use Utils\commonUtils;
$curpage =  $data->currentPage();
$accessLevel = Session::get('saccess_level');
$isView = Session::get('sis_view');
?>
    <div id="wrapper" >
        <div class="row margin-form">
            <div class="col-sm-12">
                <span class="pull-right pading-right-22">
                    <?php if($accessLevel < 2 && $isView == 0) {?>
                        <a role="button" id="addGoal"  class="btn btn-primary change-color" href="<?= URL::to('addGroup');?>"><i class="fa fa-plus"></i> Thêm Mới</a>
                    <?php } ?>
                </span>
            </div>
            <div class="col-sm-12">
                <span class="pull-right pading-right-22">
                    <?php
                        echo $data->setPath(action('CategoriesController@groupCategories'))->render();
                    ?>
                </span>
            </div>
            <table class="table-common">
                <thead>
                <tr>
                    <?php if($accessLevel < 2 && $isView == 0) {?>
                    <th class="col-sm-1" >STT</th>
                    <th class="col-sm-2" sort_key="area_id">Tổ/Quận/Huyện<i class="fa pull-right unsort fa-sort"></i></th>
                    <th class="col-sm-2" sort_key="group_code">Mã<i class="fa pull-right unsort fa-sort"></i></th>
                    <th class="col-sm-5" sort_key="group_name">Tên<i class="fa pull-right unsort fa-sort"></i></th>
                    <th class="col-sm-1"></th>
                    <th class="col-sm-1"></th>
                    <?php } else {?>
                    <th class="col-sm-1" >STT</th>
                    <th class="col-sm-3" sort_key="area_id">Tổ/Quận/Huyện<i class="fa pull-right unsort fa-sort"></i></th>
                    <th class="col-sm-3" sort_key="group_code">Mã<i class="fa pull-right unsort fa-sort"></i></th>
                    <th class="col-sm-5" sort_key="group_code">Tên<i class="fa pull-right unsort fa-sort"></i></th>
                    <?php } ?>
                </tr>
                </thead>
                <tbody>
                <?php
                    $i=0;
                    $stt = (($curpage-1) * CommonUtils::ITEM_PER_PAGE_DEFAULT) + 1;
                    foreach($data as $row){
                    $i++;
                    $area_name = '';
                    foreach($area as $a){
                        if($a->id == $row->area_id){
                            $area_name = $a->area_name;
                            break;
                        }
                    }
                ?>
                <?php if($i % 2 == 0):?>
                <tr class="background-color-smoke">
                <?php else:?>
                <tr>
                    <?php endif; ?>
                    <td class="text-center col-sm-1"><?php  echo $stt; $stt++; ?></td>
                    <td class="col-sm-2"><?php echo $area_name; ?></td>
                    <td class="col-sm-2"><?php echo $row->group_code; ?></td>
                    <td class="col-sm-5"><?php echo $row->group_name; ?></td>
                    <?php if($accessLevel < 2 && $isView == 0) {?>
                    <td class="col-sm-1 table-icon text-center">
                        <a href="<?php echo 'updateGroup/'.$row->id ?>" role="button" title="Cập nhật">
                            <i class="fa fa-pencil-square-o"></i>
                        </a>
                    </td>
                    <td class="col-sm-1 table-icon text-center" >
                        <a role="button"  href="#" data-target=".dialog-delete-goal-<?php echo $row->id; ?>" data-toggle="modal">
                            <i class="fa fa-trash" title="Xóa" ></i>
                        </a>
                        @include('popup.confirmDelete', array('rowId' => $row->id
                                                      , 'data' => $row->id
                                                      , 'strName' => $row->group_name
                                                      , 'actionName'  => 'CategoriesController@deleteGroup'
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
                        echo $data->setPath(action('CategoriesController@groupCategories'))->render();
                    ?>
                </span>
            </div>
        </div>
        <script>
            $(document).ready(function(){
                sortOnPageLoad();
            });
        </script>
    </div>
@stop