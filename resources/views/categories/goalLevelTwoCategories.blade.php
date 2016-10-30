@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.CATEGORIES') . ' ' . Config::get('constant.GOAL_2'))
@section('section')
@include('alerts.errors')
@include('alerts.success')
<?php
use Utils\commonUtils;
$curpage =  $data->currentPage();
$accessLevel = Session::get('saccess_level');
$isView = Session::get('sis_view');

$hiddenExportExcel =  ($accessLevel <= 3 && $isView == 0) ? '' : 'hidden';
$hiddenARCompany   =  ($accessLevel == 1 && $isView == 0) ? '' : 'hidden';
?>

    <div id="wrapper" xmlns="http://www.w3.org/1999/html">
        <div class="row margin-form">
            <div class="col-sm-12">

                <label class="col-sm-2 text-label padding-top-frm no-padding-left"><span class="pull-right padding-right-15 padding-top-5 font-label-form">Chọn mục tiêu cấp 1:<span></label>
                <div class="col-sm-4 form-group">
                    <select class="form-control combobox-99" id="goalLevelOne" onChange="reloadPageWithNewElements()">
                        <option value="0">TẤT CẢ</option>
                        <?php  foreach($dataCombobox as $value){ ?>
                        <option value="<?php echo $value->id; ?>"  <?php if($value->id == $valueSelect){  echo 'selected'; } ?>><?php echo $value->goal_name; ?></option>
                        <?php } ?>

                    </select>
                </div>
                <div class="col-sm-6"></div>
            </div>
            <div class="col-sm-12 pull-right">
                <span class="pull-right pading-right-22">
                    <a role="button" id="exportGoal" class="btn btn-primary pull-right {{ $hiddenExportExcel }}"
                       onclick="reloadPageWithParam('{{action('ExportExcelController@exportGoalLevelTwo')}}'
                               , 'exportGoalLevelTwo'
                               , 'goalLevelOne')"><i
                                class="fa fa-sign-out"></i> Xuất Excel</a>

                        <a role="button" id="addGoal" class="btn btn-primary pull-right change-color margin-btnadd {{ $hiddenARCompany }}"
                           href="<?= URL::to('addGoalLevelTwoCategories');?>"><i class="fa fa-plus"></i> Thêm Mới</a>
                </span>

            </div>
            <div class="col-sm-12">
                <span class="pull-right pading-right-22">
                    <?php
                    echo $data->setPath(action('CategoriesController@goalLevelTwoCategories'))->render();
                    ?>
                </span>
            </div>

            <table class="table-common" style="margin-top: 100px;">
                <thead>
                <tr class="text-center">
                    <?php if($accessLevel < 2 && $isView == 0) {?>
                    <th class="col-sm-1">STT</th>
                    <th class="col-sm-1">Mã</th>
                    <th class="col-sm-3">Tên</th>
                    <th class="col-sm-2">Công thức</th>
                    <th class="col-sm-2">Loại mục tiêu</th>
                    <th class="col-sm-1">ĐVT</th>
                    <th class="col-sm-1"></th>
                    <th class="col-sm-1"></th>
                    <?php } else { ?>
                    <th class="col-sm-1">STT</th>
                    <th class="col-sm-1">Mã</th>
                    <th class="col-sm-4">Tên</th>
                    <th class="col-sm-2">Công thức</th>
                    <th class="col-sm-2">Loại mục tiêu</th>
                    <th class="col-sm-2">ĐVT</th>
                    <?php } ?>
                </tr>
                </thead>
                <tbody>
                <?php
                        $no = 1;
                    foreach($parent_data as $prow){
                ?>
                        <!-- Parent row ----------------------------------------------------------->
                <tr style="background-color: #D8E4BC">
                    <td colspan="8"><?php echo $prow['name'];?></td>
                </tr>
                <?php
                    $i = 0;
                    $stt = (($curpage-1) * CommonUtils::ITEM_PER_PAGE_DEFAULT) + 1;
                    foreach($data as $row){
                        $i++;
                        if($row->parent_id == $prow['id']){
                ?>
                <?php

                if($i % 2 == 0){
                ?>
                <tr class="background-color-smoke">
                <?php }else{?>
                <tr>
                    <?php } ?>
                    <td class="order-column"><?php echo $no++;  ?></td>
                    <td><?php echo $row->goal_code;?></td>
                    <td><?php echo $row->goal_name;?></td>
                    <td><?php echo commonUtils::renderFormulaOfGoalType($row->formula); ?></td>
                    <td><?php echo commonUtils::renderGoalTypeName($row->goal_type); ?></td>
                    <td>{{$row->unit_name}}</td>
                    <?php if($accessLevel < 2 && $isView == 0) {?>
                    <td class="table-icon text-center">
                        <a href="<?php echo 'updateGoalLevelTwo/' . $row->id;?>" role="button" title="Cập nhật">
                            <i class="fa fa-pencil-square-o"></i>
                        </a>
                    </td>

                    <td class="table-icon text-center">
                        <a role="button" href="#"
                           data-target=".dialog-delete-goal-<?php echo $row->id; ?>" data-toggle="modal">
                            <i class="fa fa-trash" title="Xóa"></i>
                        </a>
                        @include('popup.confirmDelete', array('rowId' => $row->id
                                                                              , 'data' => $row->id
                                                                              , 'strName' => $row->goal_name
                                                                              , 'actionName'  => 'CategoriesController@deleteGoalLevelTwo'
                                                                              ))
                    </td>
                    <?php } ?>
                </tr>
                <?php } ?>
                <?php } ?>
                <?php } ?>

                </tbody>
            </table>
            <div class="col-sm-12">
                <span class="pull-right pading-right-22">
                    <?php
                    echo $data->setPath(action('CategoriesController@goalLevelTwoCategories'))->render();
                    ?>
                </span>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        function reloadPageWithNewElements() {
            var selectedValue = document.getElementById('goalLevelOne').value;
            // refresh page and send value as param
            var path = '<?php echo action('CategoriesController@goalLevelTwoCategories');?>';
            path = path.replace("%7BparentId%7D", "");
            window.location.href = path + selectedValue;
        }
    </script>
@stop