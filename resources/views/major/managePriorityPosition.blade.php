@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.IMPORTANT_LEVEL') . ' Cho ' . Config::get('constant.POSITION'))
@section('section')
@include('alerts.errors')
@include('alerts.success')
<?php
//$view = \Utils\commonUtils::checkIsView();
$currentCompanyId = Session::get('scompany_id');
$currentAreaId = Session::get('sarea_id');
$accessLevel = Session::get('saccess_level');
        $sDataUser = Session::get('sDataUser');
$view = $sDataUser->is_view;

$hiddenExportExcel =  ($view == 0) ? '' : 'hidden';
$hiddenARCompany   =  ($accessLevel == 1 && $view == 0) ? '' : 'hidden';
?>
    <div id="wrapper">
        <div class="row margin-form">

            <div class="col-sm-12">
                <div class="col-sm-2 control-label padding-top-frm font-label-form text-right">
                    Phòng/Đài/MBF HCM (*):
                </div>
                <div class="col-sm-4 form-group">
                    <select class="form-control combobox-99" id="cboCompany"
                            onchange="reloadPageWithParam('{{action('MajorController@managePriorityPosition')}}'
                                    , 'managePriorityPosition'
                                    , 'cboCompany/cboArea/cboPosition/cboGoal/cboYear/cboMonth')">
                        <option value="0">Chọn Phòng/Đài/MBF HCM</option>
                        <?php
                        if($accessLevel > 1){
                        foreach ($companies as $company) {
                                if($currentCompanyId == $company->id){?>
                                    <option value="<?php echo $company->id; ?>" <?php if ($company->id == $selectedCompany) {echo 'selected';} ?>>
                                        <?php echo $company->company_name; ?>
                                    </option>
                        <?php }}} else {
                        foreach ($companies as $company) {?>
                        <option value="<?php echo $company->id; ?>" <?php if ($company->id == $selectedCompany) {echo 'selected';} ?>>
                            <?php echo $company->company_name; ?>
                        </option>
                        <?php }} ?>
                    </select>
                </div>

                <div class="col-sm-5"></div>
            </div>

            <div class="col-sm-12">
                <div class="col-sm-2 control-label padding-top-frm font-label-form text-right">
                    Tổ/Quận/Huyện (*):
                </div>
                <div class="col-sm-4 form-group">
                    <select class="form-control combobox-99" id="cboArea"
                            onchange="reloadPageWithParam('{{action('MajorController@managePriorityPosition')}}'
                                    , 'managePriorityPosition'
                                    , 'cboCompany/cboArea/cboPosition/cboGoal/cboYear/cboMonth')">
                        <option value="0">Chọn Tổ/Quận/Huyện</option>
                        <?php
                        if($accessLevel > 2){
                            foreach ($areas as $area) {
                            if($currentAreaId == $area->id){?>
                            <option value="<?php echo $area->id; ?>" <?php if ($area->id == $selectedArea) {echo 'selected';} ?>>
                                <?php echo $area->area_name; ?>
                            </option>
                        <?php }}} else {
                            foreach ($areas as $area) {?>
                                <option value="<?php echo $area->id; ?>" <?php if ($area->id == $selectedArea) {echo 'selected';} ?>>
                                    <?php echo $area->area_name; ?>
                                </option>
                        <?php }} ?>
                    </select>
                </div>


                <div class="col-sm-1 control-label padding-top-frm padding-right-15 font-label-form text-right">Mục
                    Tiêu:
                </div>
                <div class="col-sm-5 form-group">
                    <select class="form-control combobox-99" id="cboGoal"
                            onchange="reloadPageWithParam('{{action('MajorController@managePriorityPosition')}}'
                                    , 'managePriorityPosition'
                                    , 'cboCompany/cboArea/cboPosition/cboGoal/cboYear/cboMonth')">
                        <option value="0" class="option-bold">Tất cả</option>
                        <?php foreach ($gOnes as $gOne){?>
                        <option value="<?php echo $gOne->id; ?>" <?php if ($gOne->id == $selectedGoal) {
                            echo 'selected';
                        } ?>><?php echo $gOne->goal_name; ?></option>
                        <?php
                        foreach ($gTwos as $gTwo){
                        if($gTwo->parent_id == $gOne->id){?>
                        <option value="<?php echo $gTwo->id; ?>" <?php if ($gTwo->id == $selectedGoal) {
                            echo 'selected';
                        } ?>><?php echo $gTwo->goal_name; ?></option>
                        <?php }
                        }
                        }?>
                    </select>
                </div>
            </div>

            <div class="col-sm-12">
                <div class="col-sm-2 control-label padding-top-frm font-label-form text-right">Chức
                    danh:
                </div>
                <div class="col-sm-4 form-group">
                    <select class="form-control combobox-99" id="cboPosition"
                            onchange="reloadPageWithParam('{{action('MajorController@managePriorityPosition')}}'
                                    , 'managePriorityPosition'
                                    , 'cboCompany/cboArea/cboPosition/cboGoal/cboYear/cboMonth')">
                        <option value="0">Tất cả</option>
                        <?php foreach ($positions as $position) { ?>
                        <option value="<?php echo $position->id; ?>" <?php if ($position->id == $selectedPosition) {
                            echo 'selected';
                        } ?>><?php echo $position->position_name; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-sm-1 control-label padding-top-frm padding-right-15 font-label-form text-right">Năm (*):
                </div>
                <div class="col-sm-2 form-group">
                    <select class="form-control text-left  combobox-99" id="cboYear"
                            onchange="reloadPageWithParam('{{action('MajorController@managePriorityPosition')}}'
                                    , 'managePriorityPosition'
                                    , 'cboCompany/cboArea/cboPosition/cboGoal/cboYear/cboMonth')">
                        <?php $arrYear = \Utils\commonUtils::getArrYear($dataYears) ; foreach($arrYear as $year) { ?>
                        <option value="<?php echo $year; ?>" <?php if ($year == $selectedYear) {
                            echo 'selected';
                        } ?>><?php echo $year; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-sm-1 control-label padding-top-frm padding-right-15 font-label-form text-right">Tháng (*):
                </div>
                <div class="col-sm-2 form-group">
                    <select class="form-control text-left combobox-99" id="cboMonth"
                            onchange="reloadPageWithParam('{{action('MajorController@managePriorityPosition')}}'
                                    , 'managePriorityPosition'
                                    , 'cboCompany/cboArea/cboPosition/cboGoal/cboYear/cboMonth')">
                        <?php for($m = 1 ; $m <= 12 ; $m++) { ?>
                        <option value="<?php echo $m; ?>" <?php if ($m == $selectedMonth) {
                            echo 'selected';
                        } ?>><?php echo $m; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>


            <div class="col-sm-12 col-offset-3">
                <div class="col-sm-3 control-label padding-top-frm padding-right-15 font-label-form text-right"></div>
                <div class="col-sm-4 form-group font-label-form hidden" style="color: red !important;" id="NoteExport">Xuất Excel để xem dữ liệu!</div>
                <div class="col-sm-5 "></div>
            </div>

            <a role="button" id="expGoal"  class="btn btn-primary pull-right margin-btnexport {{ $hiddenExportExcel }}"
               onclick="reloadPageWithParam('{{action('ExportExcelController@exportPriorityPosition')}}'
                       , 'exportPriorityPosition'
                       , 'cboCompany/cboArea/cboPosition/cboGoal/cboYear/cboMonth')"><i class="fa fa-sign-out"></i> Xuất Excel</a>

            <a role="button" id="addGoal" class="btn btn-primary pull-right change-color style-btn-addnew hidden"
               href="#"><i class="fa fa-plus"></i> Thêm Mới</a>

            <table id="tblPriorityPosition" class="table-common" style="margin-top: 100px;">
                <thead>
                <tr class="text-center">
                    <th class="col-sm-1">STT</th>
                    <th class="col-sm-1">Mã Mục Tiêu</th>
                    <th class="col-sm-5">Tên Mục Tiêu</th>
                    <th class="col-sm-1">Tỷ Trọng</th>
                    <th class="col-sm-1">Điểm Chuẩn</th>
                    <th class="col-sm-1">Điểm Chuẩn KPI</th>
                    <?php
                    if(!$view && $accessLevel < 2){
                    ?>
                    <th class="col-sm-1"></th>
                    <th class="col-sm-1"></th>
                    <?php
                    }
                    ?>
                </tr>
                </thead>
                <tbody>
                <?php
                if($isParent != 0){
                foreach($data as $row){
                if($row->parent_id == 0){
                    $pGoalName          = ($row->goal_name != "") ? $row->goal_name : "-";
                    $pImportantLevel    = ($row->important_level != 0) ? $row->important_level : '-';
                    $pBenchmark         = ($row->benchmark != 0) ? \Utils\commonUtils::formatFloatValue($row->benchmark, \Utils\commonUtils::NUMBER_AFTER_DOT) : '-';
                    $pCalBenchmark      = ($row->cal_benchmark != 0) ? \Utils\commonUtils::formatFloatValue($row->cal_benchmark, \Utils\commonUtils::NUMBER_AFTER_DOT) : '-';
                ?>
                <tr style="background-color: #D8E4BC">

                    <td colspan="3">{{ $pGoalName }}</td>
                    <td class="text-right">{{ $pImportantLevel }}</td>
                    <td class="text-right">{{ $pBenchmark }}</td>
                    <td class="text-right">{{ $pCalBenchmark }}</td>
                    <?php
                    if(!$view && $accessLevel < 2){
                    ?>
                    <td class="table-icon text-center">
                        <a role="button" title="Cập nhật">
                            <a href="{{url('updatePriorityPosition').'/'.$row->id}}" role="button" title="Cập nhật">
                                <i class="fa fa-pencil-square-o"></i>
                            </a>
                        </a>
                    </td>

                    <td class="table-icon text-center" >

                        <a role="button" title="Xóa" style="cursor: pointer;" data-target=".dialog-delete-goal-<?php echo $row->id; ?>" data-toggle="modal">
                            <i class="fa fa-trash"></i>
                        </a>
                        @include('popup.confirmDelete', array('rowId' => $row->id
                                                      , 'data' => $row->id
                                                      , 'strName' => $row->goal_name.'<br/><br/><span style="color:red;">* Chú ý:</span> Sau khi xóa thì Tỷ trọng/Kế hoạch/Thực hiện của Chức danh, Nhân viên đối với mục tiêu: '.$row->goal_name.' áp dụng tháng: '.$row->month.'/'.$row->year.' sẽ bị xóa'
                                                      , 'actionName'  => 'MajorController@deletePriorityPosition'
                                                      ))

                    </td>
                    <?php
                    }
                    ?>
                </tr>

                <?php
                $stt = 1;
                foreach($data as $child){
                if($child->parent_id == $row->goal_id){

                    $cGoalCode          = ($child->goal_code != "") ? $child->goal_code : "-";
                    $cGoalName          = ($child->goal_name != "") ? $child->goal_name : "-";
                    $cImportantLevel    = ($child->important_level != 0) ? $child->important_level : '-';
                    $cBenchmark         = ($child->benchmark != 0) ? \Utils\commonUtils::formatFloatValue($child->benchmark, \Utils\commonUtils::NUMBER_AFTER_DOT) : '-';
                    $cCalBenchmark      = ($child->cal_benchmark != 0) ? \Utils\commonUtils::formatFloatValue($child->cal_benchmark, \Utils\commonUtils::NUMBER_AFTER_DOT) : '-';

                ?>
                <tr style="background-color: white">

                    <td class="order-column">{{ $stt++ }}</td>
                    <td class="text-left">{{ $cGoalCode }}</td>
                    <td class="text-left">{{ $cGoalName }}</td>
                    <td class="text-right">{{ $cImportantLevel }}</td>
                    <td class="text-right">{{ $cBenchmark }}</td>
                    <td class="text-right">{{ $cCalBenchmark }}</td>
                    <?php
                    if(!$view && $accessLevel < 2){
                    ?>
                    <td class="table-icon text-center">
                        <a role="button" title="Cập nhật">
                            <a href="{{url('updatePriorityPosition').'/'.$child->id}}" role="button" title="Cập nhật">
                                <i class="fa fa-pencil-square-o"></i>
                            </a>
                        </a>
                    </td>

                    <td class="table-icon text-center" >
                        <a role="button" title="Xóa" style="cursor: pointer;" data-target=".dialog-delete-goal-<?php echo $child->id; ?>" data-toggle="modal">
                            <i class="fa fa-trash"></i>
                        </a>
                        @include('popup.confirmDelete', array('rowId' => $child->id
                                                      , 'data' => $child->id
                                                      , 'strName' => $child->goal_name.'<br/><br/><span style="color:red;">* Chú ý:</span> Sau khi xóa thì Tỷ trọng/Kế hoạch/Thực hiện của Chức danh, Nhân viên đối với mục tiêu: '.$child->goal_name.' áp dụng tháng: '.$child->month.'/'.$child->year.' sẽ bị xóa'
                                                      , 'actionName'  => 'MajorController@deletePriorityPosition'
                                                      ))
                    </td>
                    <?php
                    }
                    ?>
                </tr>
                <?php }
                }
                }
                }
                }else{
                $stt = 1;
                foreach($data as $child){

                    $cGoalCode          = ($child->goal_code != "") ? $child->goal_code : "-";
                    $cGoalName          = ($child->goal_name != "") ? $child->goal_name : "-";
                    $cImportantLevel    = ($child->important_level != 0) ? $child->important_level : '-';
                    $cBenchmark         = ($child->benchmark != 0) ? \Utils\commonUtils::formatFloatValue($child->benchmark, \Utils\commonUtils::NUMBER_AFTER_DOT) : '-';
                    $cCalBenchmark      = ($child->cal_benchmark != 0) ? \Utils\commonUtils::formatFloatValue($child->cal_benchmark, \Utils\commonUtils::NUMBER_AFTER_DOT) : '-';
                ?>
                <tr style="background-color: white">

                    <td class="order-column">{{ $stt++ }}</td>
                    <td class="text-left">{{ $cGoalCode }}</td>
                    <td class="text-left">{{ $cGoalName }}</td>
                    <td class="text-right">{{ $cImportantLevel }}</td>
                    <td class="text-right">{{ $cBenchmark }}</td>
                    <td class="text-right">{{ $cCalBenchmark }}</td>
                    <?php
                    if(!$view && $accessLevel < 2){
                    ?>
                    <td class="table-icon text-center">
                        <a role="button" title="Cập nhật">
                            <a href="{{url('updatePriorityPosition').'/'.$child->id}}" role="button" title="Cập nhật">
                                <i class="fa fa-pencil-square-o"></i>
                            </a>
                        </a>
                    </td>

                    <td class="table-icon text-center" >
                        <a role="button" title="Xóa" style="cursor: pointer;" data-target=".dialog-delete-goal-<?php echo $child->id; ?>" data-toggle="modal">
                            <i class="fa fa-trash"></i>
                        </a>
                        @include('popup.confirmDelete', array('rowId' => $child->id
                                                      , 'data' => $child->id
                                                      , 'strName' => $child->goal_name.'<br/><br/><span style="color:red;">* Chú ý:</span> Sau khi xóa thì Tỷ trọng/Kế hoạch/Thực hiện của Chức danh, Nhân viên đối với mục tiêu: '.$child->goal_name.' áp dụng tháng: '.$child->month.'/'.$child->year.' sẽ bị xóa'
                                                      , 'actionName'  => 'MajorController@deletePriorityPosition'
                                                      ))
                    </td>
                    <?php
                    }
                    ?>
                </tr>
                <?php }
                }
                ?>
                </tbody>
            </table>
        </div>
        <table id="header-fixed"></table>
        <script>
            fixHeader($("#tblPriorityPosition"), $("#tblPriorityPosition > thead"), $("#header-fixed"));
            $( document ).ready(function() {
                var valArea = $('#cboArea').val();
                var valPosition = $('#cboPosition').val();
                var valCompany = $('#cboCompany').val();
                if(valArea != 0 && valCompany != 0 && valPosition==0 ){
                    $('#NoteExport').removeClass('hidden');
                }
            });
        </script>
    </div>
@stop