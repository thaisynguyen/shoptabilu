@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.IMPORTANT_LEVEL') . ' ' . Config::get('constant.COMPANY'))
@section('section')
    @include('alerts.errors')
    @include('alerts.success')
    <?php
    $view = \Utils\commonUtils::checkIsView();
    $currentCompanyId = Session::get('scompany_id');
    $accessLevel = Session::get('saccess_level');

    $hiddenExportExcel =  ($accessLevel <= 2 && $view == 0) ? '' : 'hidden';
    $hiddenARCompany   =  ($accessLevel == 1 && $view == 0) ? '' : 'hidden';

    ?>
    <div id="wrapper">
        <div class="row margin-form">
            <div class="col-sm-12">
                <div class="col-sm-2 control-label padding-top-frm padding-right-15 font-label-form text-right">
                    Phòng/Đài/MBF HCM:
                </div>
                <div class="col-sm-3 form-group">
                    <select class="form-control combobox-99" id="cboCompany"
                            onchange="reloadPageWithParam('{{action('MajorController@managePriorityCompany')}}'
                                    , 'managePriorityCompany'
                                    , 'cboCompany/cboGoal/cboApplyDate')">
                        <option value="0">Chọn Phòng/Đài/MBF HCM</option>
                        <?php if($accessLevel > 1){
                            foreach($companies as $company){
                            if($currentCompanyId == $company->id){?>
                        <option value="<?php echo $company->id; ?>" <?php if ($company->id == $selectedCompany) {echo 'selected';} ?>>
                            <?php echo $company->company_name; $selectedCompanyName = $company->company_name;?>
                        </option>
                        <?php }}} else {
                            foreach($companies as $company){?>
                            <option value="<?php echo $company->id; ?>" <?php if ($company->id == $selectedCompany) {echo 'selected';} ?>>
                                <?php echo $company->company_name; $selectedCompanyName = $company->company_name;?>
                            </option>
                        <?php }} ?>
                    </select>
                </div>
                <div class="col-sm-1 control-label padding-top-frm padding-right-15 font-label-form text-right">Mục
                    Tiêu:
                </div>
                <div class="col-sm-2 form-group">
                    <select class="form-control combobox-99" id="cboGoal"
                            onchange="reloadPageWithParam('{{action('MajorController@managePriorityCompany')}}'
                                    , 'managePriorityCompany'
                                    , 'cboCompany/cboGoal/cboApplyDate')">
                        <option value="0" class="option-bold">Tất cả</option>
                        <?php foreach ($gOnes as $gOne){?>
                        <option value="<?php echo $gOne->id; ?>" <?php if ($gOne->id == $selectedGoal) {
                            echo 'selected';
                        } ?>><?php echo $gOne->goal_name; ?></option>
                        <?php
                        foreach ($gTwos as $gTwo) {
                        if($gTwo->parent_id == $gOne->id) {?>
                        <option value="<?php echo $gTwo->id; ?>" <?php if ($gTwo->id == $selectedGoal) {echo 'selected';} ?>>
                            <?php echo $gTwo->goal_name; ?>
                        </option>
                        <?php }
                        }
                        }?>

                    </select>
                </div>
                <div class="col-sm-2 control-label padding-top-frm padding-right-15 font-label-form text-right">Ngày áp
                    dụng:
                </div>
                <div class='col-sm-2'>
                    <div class="form-group">
                        <select class="form-control" name="year" id="cboApplyDate"
                                onchange="reloadPageWithParam('{{action('MajorController@managePriorityCompany')}}'
                                        , 'managePriorityCompany'
                                        , 'cboCompany/cboGoal/cboApplyDate')">
                            <?php
                            if(count($date) == 0){
                            $fDay = date('d') . '/' . date('m') . '/' . date('Y');
                            $nDay = date('Y-m-d');
                            ?>
                            <option value="<?php echo $nDay; ?>" <?php if ($nDay == $selectedApplyDate) {
                                echo 'selected';
                            } ?>><?php echo $fDay; ?></option>
                            <?php }else{
                            foreach($date as $row){
                            $dayFormat  = $row->apply_date;
                            $year       = substr($dayFormat, 0, 4);
                            $month      = substr($dayFormat, 5, 2);
                            $day        = substr($dayFormat, 8, 2);
                            $dayFormat  = $day . '/' . $month . '/' . $year; ?>
                            <option value="<?php echo $row->apply_date; ?>" <?php if ($row->apply_date == $selectedApplyDate) {
                                echo 'selected';
                            } ?>><?php echo $dayFormat; ?></option>
                            <?php
                            }
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-sm-12 col-offset-3">
                <div class="col-sm-2 control-label padding-top-frm padding-right-15 font-label-form text-right"></div>
                <div class="col-sm-5 form-group font-label-form hidden" style="color: red !important;" id="NoteExport">Xuất Excel để xem dữ liệu!</div>
                <div class="col-sm-5 "></div>

            </div>

            <a role="button" id="expCompany" class="btn btn-primary pull-right margin-btnexport {{ $hiddenExportExcel }}"
               onclick="reloadPageWithParam('{{action('ExportExcelController@exportTargetCompany')}}'
                       , 'exportTargetCompany'
                       , 'cboCompany/cboGoal/cboApplyDate')"><i class="fa fa-sign-out"></i> Xuất Excel</a>

            <a role="button" id="addCompany" class="btn btn-primary pull-right change-color style-btn-addnew hidden {{ $hiddenARCompany }}"
               href="#"><i class="fa fa-plus " ></i> Thêm Mới</a>

            <table id="tblPriorityCompany" class="table-common" style="margin-top: 100px;">
                <thead>
                <tr class="text-center">
                    <th>STT</th>
                    <th class="col-sm-1">Mã Mục Tiêu</th>
                    <th class="col-sm-3">Tên Mục Tiêu</th>
                    <th class="col-sm-2">Kế hoạch</th>
                    <th class="col-sm-1">Tỷ Trọng</th>
                    <th class="col-sm-1">Điểm Chuẩn</th>
                    <th class="col-sm-1">Điểm Chuẩn KPI</th>
                    <th class="col-sm-1">ĐTH KPI</th>
                    <th class="col-sm-1">Tỷ lệ đạt</th>
                    <th class="col-sm-1">Điểm thực hiện</th>
                    <?php
                    if(!$view && $accessLevel < 2){
                    ?>
                    <th></th>
                    <th></th>
                    <?php
                    }
                    ?>
                </tr>
                </thead>
                <tbody>
                <?php
                if($isParent != 0 ){
                    foreach($data as $row){
                        if($row->parent_id == 0){

                        if(\Utils\commonUtils::compareTwoString($row->unit_code, \Utils\commonUtils::PERCENT_CODE) == 1){
                            $pTargetValue       = ($row->target_value != 0) ? \Utils\commonUtils::formatFloatValue($row->target_value, \Utils\commonUtils::NUMBER_AFTER_DOT).'%' : '-';
                        }else{
                            $pTargetValue       = ($row->target_value != 0) ? \Utils\commonUtils::formatFloatValue($row->target_value, \Utils\commonUtils::DF_NUMBER_AFTER_DOT) : '-';
                        }

                        $pGoalName          = ($row->goal_name != "") ? $row->goal_name : "-";
                        $pImportantLevel    = ($row->important_level != 0) ? $row->important_level : '-';
                        $pBenchmark         = ($row->benchmark != 0) ? \Utils\commonUtils::formatFloatValue($row->benchmark, \Utils\commonUtils::NUMBER_AFTER_DOT) : '-';
                        $pCalBenchmark      = ($row->cal_benchmark != 0) ? \Utils\commonUtils::formatFloatValue($row->cal_benchmark, \Utils\commonUtils::NUMBER_AFTER_DOT) : '-';
                        $pCalImplementPoint = ($row->cal_implement_point != 0) ? \Utils\commonUtils::formatFloatValue($row->cal_implement_point, \Utils\commonUtils::NUMBER_AFTER_DOT) : '-';
                        $pRealPercent       = ($row->real_percent != 0) ? \Utils\commonUtils::formatFloatValue($row->real_percent*100, \Utils\commonUtils::NUMBER_AFTER_DOT).'%' : '-';
                        $pImplementPoint    = ($row->implement_point != 0) ? \Utils\commonUtils::formatFloatValue($row->implement_point, \Utils\commonUtils::NUMBER_AFTER_DOT) : '-';
                ?>
                <tr style="background-color: #D8E4BC">
                    <td colspan="3">{{ $pGoalName }}</td>
                    <td class="text-right">{{ $pTargetValue }}</td>
                    <td class="text-right">{{ $pImportantLevel }}</td>
                    <td class="text-right">{{ $pBenchmark }}</td>
                    <td class="text-right">{{ $pCalBenchmark }}</td>
                    <td class="text-right">{{ $pCalImplementPoint }}</td>
                    <td class="text-right">{{ $pRealPercent }}</td>
                    <td class="text-right">{{ $pImplementPoint }}</td>
                    <?php
                    if(!$view && $accessLevel < 2){
                    ?>
                    <td class="table-icon text-center">
                        <a href="{{url('updatePriorityCompany').'/'.$row->id}}" role="button" title="Cập nhật">
                            <i class="fa fa-pencil-square-o"></i>
                        </a>
                    </td>

                    <td class="table-icon text-center" >
                        <a role="button" title="Xóa" style="cursor: pointer;" data-target=".dialog-delete-goal-<?php echo $row->id; ?>" data-toggle="modal">
                            <i class="fa fa-trash"></i>
                        </a>
                        @include('popup.confirmDelete', array('rowId' => $row->id
                                                      , 'data' => $row->id
                                                      , 'strName' => $row->goal_name.'<br/><br/><span style="color:red;">* Chú ý:</span> Sau khi xóa thì Tỷ trọng/Kế hoạch/Thực hiện của Tổ/Quận/Huyện, Chức danh, Nhân viên đối với mục tiêu: '.$row->goal_name.' thuộc Phòng/Đài/MBF HCM: '.$row->company_name.' áp dụng từ ngày: '.\Utils\commonUtils::formatDate($row->apply_date).' năm: '.date('Y', strtotime($row->apply_date)).' sẽ bị xóa'
                                                      , 'actionName'  => 'MajorController@deletePriorityCompany'
                                                      ))
                    </td>
                    <?php
                    }
                    ?>
                </tr>

                <?php
                $stt = 1;
                foreach($data as $child){
                        //\Utils\commonUtils::pr($child);
                    if($child->parent_id == $row->goal_id){

                        if(\Utils\commonUtils::compareTwoString($child->unit_code, \Utils\commonUtils::PERCENT_CODE) == 1){
                            $cTargetValue       = ($child->target_value != 0) ? \Utils\commonUtils::formatFloatValue($child->target_value, \Utils\commonUtils::NUMBER_AFTER_DOT).'%' : '-';
                        }else{
                            $cTargetValue       = ($child->target_value != 0) ? \Utils\commonUtils::formatFloatValue($child->target_value, \Utils\commonUtils::DF_NUMBER_AFTER_DOT) : '-';
                        }

                        $cGoalCode          = ($child->goal_code != "") ? $child->goal_code : "-";
                        $cGoalName          = ($child->goal_name != "") ? $child->goal_name : "-";
                        $cImportantLevel    = ($child->important_level != 0) ? $child->important_level : '-';
                        $cBenchmark         = ($child->benchmark != 0) ? \Utils\commonUtils::formatFloatValue($child->benchmark, \Utils\commonUtils::NUMBER_AFTER_DOT) : '-';
                        $cCalBenchmark      = ($child->cal_benchmark != 0) ? \Utils\commonUtils::formatFloatValue($child->cal_benchmark, \Utils\commonUtils::NUMBER_AFTER_DOT) : '-';
                        $cCalImplementPoint = ($child->cal_implement_point != 0) ? \Utils\commonUtils::formatFloatValue($child->cal_implement_point, \Utils\commonUtils::NUMBER_AFTER_DOT) : '-';
                        $cRealPercent       = ($child->real_percent != 0) ? \Utils\commonUtils::formatFloatValue($child->real_percent*100, \Utils\commonUtils::NUMBER_AFTER_DOT).'%' : '-';
                        $cImplementPoint    = ($child->implement_point != 0) ? \Utils\commonUtils::formatFloatValue($child->implement_point, \Utils\commonUtils::NUMBER_AFTER_DOT) : '-';
                ?>
                <tr style="background-color: white">

                    <td class="order-column">{{ $stt++ }}</td>
                    <td class="text-left">{{ $cGoalCode }}</td>
                    <td class="text-left">{{ $cGoalName }}</td>
                    <td class="text-right">{{ $cTargetValue }}</td>
                    <td class="text-right">{{ $cImportantLevel }}</td>
                    <td class="text-right">{{ $cBenchmark }}</td>
                    <td class="text-right">{{ $cCalBenchmark }}</td>
                    <td class="text-right">{{ $cCalImplementPoint }}</td>
                    <td class="text-right">{{ $cRealPercent }}</td>
                    <td class="text-right">{{ $cImplementPoint }}</td>
                    <?php
                    if(!$view && $accessLevel < 2){
                    ?>
                    <td class="table-icon text-center">
                        <a role="button" title="Cập nhật">
                            <a href="{{url('updatePriorityCompany').'/'.$child->id}}" role="button" title="Cập nhật">
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
                                                      , 'strName' => $child->goal_name.'<br/><br/><span style="color:red;">* Chú ý:</span> Sau khi xóa thì Tỷ trọng/Kế hoạch/Thực hiện của Tổ/Quận/Huyện, Chức danh, Nhân viên đối với mục tiêu: '.$child->goal_name.' thuộc Phòng/Đài/MBF HCM: '.$child->company_name.' áp dụng từ ngày: '.\Utils\commonUtils::formatDate($child->apply_date).' năm: '.date('Y', strtotime($child->apply_date)).' sẽ bị xóa'
                                                      , 'actionName'  => 'MajorController@deletePriorityCompany'
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

                    if(\Utils\commonUtils::compareTwoString($child->unit_code, \Utils\commonUtils::PERCENT_CODE) == 1){
                        $cTargetValue       = ($child->target_value != 0) ? \Utils\commonUtils::formatFloatValue($child->target_value, \Utils\commonUtils::NUMBER_AFTER_DOT).'%' : '-';
                    }else{
                        $cTargetValue       = ($child->target_value != 0) ? \Utils\commonUtils::formatFloatValue($child->target_value, \Utils\commonUtils::DF_NUMBER_AFTER_DOT) : '-';
                    }

                    $cGoalCode          = ($child->goal_code != "") ? $child->goal_code : "-";
                    $cGoalName          = ($child->goal_name != "") ? $child->goal_name : "-";
                    $cImportantLevel    = ($child->important_level != 0) ? $child->important_level : '-';
                    $cBenchmark         = ($child->benchmark != 0) ? \Utils\commonUtils::formatFloatValue($child->benchmark, \Utils\commonUtils::NUMBER_AFTER_DOT) : '-';
                    $cCalBenchmark      = ($child->cal_benchmark != 0) ? \Utils\commonUtils::formatFloatValue($child->cal_benchmark, \Utils\commonUtils::NUMBER_AFTER_DOT) : '-';
                    $cCalImplementPoint = ($child->cal_implement_point != 0) ? \Utils\commonUtils::formatFloatValue($child->cal_implement_point, \Utils\commonUtils::NUMBER_AFTER_DOT) : '-';
                    $cRealPercent       = ($child->real_percent != 0) ? \Utils\commonUtils::formatFloatValue($child->real_percent*100, \Utils\commonUtils::NUMBER_AFTER_DOT).'%' : '-';
                    $cImplementPoint    = ($child->implement_point != 0) ? \Utils\commonUtils::formatFloatValue($child->implement_point, \Utils\commonUtils::NUMBER_AFTER_DOT) : '-';
                ?>
                <tr style="background-color: white">

                    <td class="order-column">{{ $stt++ }}</td>
                    <td class="text-left">{{ $cGoalCode }}</td>
                    <td class="text-left">{{ $cGoalName }}</td>
                    <td class="text-right">{{ $cTargetValue }}</td>
                    <td class="text-right">{{ $cImportantLevel }}</td>
                    <td class="text-right">{{ $cBenchmark }}</td>
                    <td class="text-right">{{ $cCalBenchmark }}</td>
                    <td class="text-right">{{ $cCalImplementPoint }}</td>
                    <td class="text-right">{{ $cRealPercent }}</td>
                    <td class="text-right">{{ $cImplementPoint }}</td>
                    <?php
                    if(!$view && $accessLevel < 2){
                    ?>
                    <td class="table-icon text-center">
                        <a href="{{url('updatePriorityCompany').'/'.$child->id}}" role="button" title="Cập nhật">
                            <i class="fa fa-pencil-square-o"></i>
                        </a>
                    </td>
                    <td class="table-icon text-center" >
                        <a role="button" title="Xóa" style="cursor: pointer;" data-target=".dialog-delete-goal-<?php echo $child->id; ?>" data-toggle="modal">
                            <i class="fa fa-trash"></i>
                        </a>
                        @include('popup.confirmDelete', array('rowId' => $child->id
                                                      , 'data' => $child->id
                                                      , 'strName' => $child->goal_name.'<br/><br/><span style="color:red;">* Chú ý:</span> Sau khi xóa thì Tỷ trọng/Kế hoạch/Thực hiện của Tổ/Quận/Huyện, Chức danh, Nhân viên đối với mục tiêu: '.$child->goal_name.' thuộc Phòng/Đài/MBF HCM: '.$child->company_name.' áp dụng từ ngày: '.\Utils\commonUtils::formatDate($child->apply_date).' năm: '.date('Y', strtotime($child->apply_date)).' sẽ bị xóa'
                                                      , 'actionName'  => 'MajorController@deletePriorityCompany'
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
            <table id="header-fixed"></table>
        </div>
    </div>
    <script>
        fixHeader($("#tblPriorityCompany"), $("#tblPriorityCompany > thead"), $("#header-fixed"));
        $( document ).ready(function() {
            var valCompany = $('#cboCompany').val();
            if(valCompany == 0 ){
                $('#NoteExport').removeClass('hidden');
            }
        });
    </script>
@stop