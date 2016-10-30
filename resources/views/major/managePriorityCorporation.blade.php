@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.IMPORTANT_LEVEL') . ' ' . Config::get('constant.CORPORATION'))
@section('section')
    @include('alerts.errors')
    @include('alerts.success')
<?php
    use Utils\CommonUtils;
    $view = \Utils\commonUtils::checkIsView();
?>
    <div id="wrapper">
        <div class="row margin-form">
            <div class="col-sm-12">
                <div class="col-sm-1 control-label padding-top-frm padding-right-15 font-label-form text-right">Mục
                    Tiêu:
                </div>
                <div class="col-sm-4 form-group">
                    <select class="form-control combobox-99" id="cboGoal"
                            onchange="reloadPageWithParam('{{action('MajorController@managePriorityCorporation')}}'
                                    , 'managePriorityCorporation'
                                    , 'cboGoal/cboApplyDate')">
                        <option value="0" class="option-bold">Tất cả</option>
                        <?php foreach ($gOnes as $gOne){?>
                            <option value="<?php echo $gOne->id; ?>" <?php if ($gOne->id == $selectedGoal) {
                            echo 'selected';
                        } ?>><?php echo $gOne->goal_name; ?></option>
                        <?php
                        foreach ($gTwos as $gTwo) {
                            if($gTwo->parent_id == $gOne->id) {?>
                            <option value="<?php echo $gTwo->id; ?>" <?php if ($gTwo->id == $selectedGoal) {
                                echo 'selected';
                            } ?>><?php echo $gTwo->goal_name; ?></option>
                            <?php }
                        }
                        }?>

                    </select>
                </div>
                <div class="col-sm-2 control-label padding-top-frm padding-right-15 font-label-form text-right">Ngày áp
                    dụng:
                </div>
                <div class='col-sm-3'>
                    <div class="form-group">
                        <select class="form-control" name="year" id="cboApplyDate"
                                onchange="reloadPageWithParam('{{action('MajorController@managePriorityCorporation')}}'
                                        , 'managePriorityCorporation'
                                        , 'cboGoal/cboApplyDate')">
                            <option value="0"> --- Chọn Ngày áp dụng --- </option>
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
                            $date = date_create($row->apply_date);
                            $dayFormat = date_format($date, "d/m/Y");
                            ?>
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
                <div class="col-sm-2"></div>
            </div>
            <div class="col-sm-12 col-offset-3">
                <div class="col-sm-3 control-label padding-top-frm padding-right-15 font-label-form text-right"></div>
                <div class="col-sm-4 form-group font-label-form hidden" style="color: red !important;" id="NoteExport">Xuất Excel để xem dữ liệu!</div>
                <div class="col-sm-5 "></div>
            </div>
            <a role="button" id="expCompany" class="btn btn-primary pull-right margin-btnexport"
               onclick="reloadPageWithParam('{{action('ExportExcelController@exportTargetCorporation')}}'
                       , 'exportTargetCorporation'
                       , 'cboGoal/cboApplyDate')"><i class="fa fa-sign-out"></i> Xuất Excel</a>
            <?php
            if(!$view){
            ?>
            <a role="button" id="addCompany" class="btn btn-primary pull-right change-color style-btn-addnew hidden"
               href="#"><i class="fa fa-plus "></i> Thêm Mới</a>
            <?php
            }
            ?>
            <table id="tblPriorityCorporation" class="table-common" style="margin-top: 100px;">
                <thead>
                <tr class="text-center">
                    <th class="col-sm-1">STT</th>
                    <th class="col-sm-1">Mã Mục Tiêu</th>
                    <th class="col-sm-3">Tên Mục Tiêu</th>
                    <th class="col-sm-2">Kế hoạch</th>
                    <th class="col-sm-1">Tỷ trọng</th>
                    <th class="col-sm-1">Điểm Chuẩn</th>
                    <th class="col-sm-1">Tỷ lệ đạt</th>
                    <th class="col-sm-1">Điểm thực hiện</th>
                    <?php
                    if(!$view){
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
                $stt = 1;
                    foreach($data as $row){
                        if($row->parent_id == 0){
                        $pBenchmark         = ($row->benchmark != 0) ? CommonUtils::formatFloatValue($row->benchmark, \Utils\commonUtils::NUMBER_AFTER_DOT) : '-';
                        $pPercentComplete   = ($row->percent_complete != 0) ? CommonUtils::formatFloatValue($row->percent_complete * 100, \Utils\commonUtils::NUMBER_AFTER_DOT).'%' : '-';
                        $pImplementPoint    = ($row->implement_point != 0) ? CommonUtils::formatFloatValue($row->implement_point, \Utils\commonUtils::NUMBER_AFTER_DOT) : '-';
                ?>
                <tr style="background-color: #D8E4BC">
                    <td colspan="4">{{ $row->goal_name }}</td>
                    <td class="text-right">{{ $row->important_level }}</td>
                    <td class="text-right">{{ $pBenchmark }}</td>
                    <td class="text-right">{{ $pPercentComplete }}</td>
                    <td class="text-right">{{ $pImplementPoint }}</td>
                    <?php
                    if(!$view){
                    ?>
                    <td class="table-icon text-center">
                        <a href="{{url('updatePriorityCorporation').'/'.$row->id}}" role="button" title="Cập nhật">
                            <i class="fa fa-pencil-square-o"></i>
                        </a>
                    </td>
                    <td class="table-icon text-center">
                        <a role="button" title="Xóa" style="cursor: pointer;" data-target=".dialog-delete-goal-<?php echo $row->id; ?>" data-toggle="modal">
                            <i class="fa fa-trash"></i>
                        </a>
                        @include('popup.confirmDelete', array('rowId' => $row->id
                                                      , 'data' => $row->id
                                                      , 'strName' => $row->goal_name.'<br/><br/><span style="color:red;">* Chú ý:</span> Sau khi xóa thì Tỷ trọng/Kế hoạch/Thực hiện của Phòng/Đài/MBF HCM, Tổ/Quận/Huyện, Chức danh, Nhân viên đối với mục tiêu: '.$row->goal_name.' áp dụng từ ngày: '.\Utils\commonUtils::formatDate($row->apply_date).' năm: '.date('Y', strtotime($row->apply_date)).' sẽ bị xóa'
                                                      , 'actionName'  => 'MajorController@deletePriorityCorporation'
                                                      ))
                    </td>
                    <?php
                    }
                    ?>
                </tr>

                <?php

                foreach($data as $child){
                    if($child->parent_id == $row->goal_id){

                        if(\Utils\commonUtils::compareTwoString($child->unit_code, \Utils\commonUtils::PERCENT_CODE) == 1){
                            $cTargetValue       = ($child->target_value != 0) ? \Utils\commonUtils::formatFloatValue($child->target_value, \Utils\commonUtils::NUMBER_AFTER_DOT).'%' : '-';
                        }else{
                            $cTargetValue       = ($child->target_value != 0) ? \Utils\commonUtils::formatFloatValue($child->target_value, \Utils\commonUtils::DF_NUMBER_AFTER_DOT) : '-';
                        }

                        $cBenchmark         = ($child->benchmark != 0) ? CommonUtils::formatFloatValue($child->benchmark, \Utils\commonUtils::NUMBER_AFTER_DOT) : '-';
                        $cPercentComplete   = ($child->percent_complete != 0) ? CommonUtils::formatFloatValue($child->percent_complete * 100, \Utils\commonUtils::NUMBER_AFTER_DOT).'%' : '-';
                        $cImplementPoint    = ($child->implement_point != 0) ? CommonUtils::formatFloatValue($child->implement_point, \Utils\commonUtils::NUMBER_AFTER_DOT) : '-';
                ?>
                <tr style="background-color: white">

                    <td class="order-column">{{ $stt++ }}</td>
                    <td class="text-left">{{ $child->goal_code }}</td>
                    <td class="text-left">{{ $child->goal_name }}</td>
                    <td class="text-right">{{ $cTargetValue }}</td>
                    <td class="text-right">{{ $child->important_level }}</td>
                    <td class="text-right">{{ $cBenchmark }}</td>
                    <td class="text-right">{{$cPercentComplete  }}</td>
                    <td class="text-right">{{ $cImplementPoint }}</td>

                    <?php
                        if(!$view){
                    ?>
                    <td class="table-icon text-center">
                        <a href="{{url('updatePriorityCorporation').'/'.$child->id}}" role="button" title="Cập nhật">
                            <i class="fa fa-pencil-square-o"></i>
                        </a>
                    </td>
                    <td class="table-icon text-center">
                        <a role="button" title="Xóa" style="cursor: pointer;" data-target=".dialog-delete-goal-<?php echo $child->id; ?>" data-toggle="modal">
                            <i class="fa fa-trash"></i>
                        </a>
                        @include('popup.confirmDelete', array('rowId' => $child->id
                                                      , 'data' => $child->id
                                                      , 'strName' => $child->goal_name.'<br/><br/><span style="color:red;">* Chú ý:</span> Sau khi xóa thì Tỷ trọng/Kế hoạch/Thực hiện của Phòng/Đài/MBF HCM, Tổ/Quận/Huyện, Chức danh, Nhân viên đối với mục tiêu: '.$child->goal_name.' áp dụng từ ngày: '.\Utils\commonUtils::formatDate($child->apply_date).' năm: '.date('Y', strtotime($child->apply_date)).' sẽ bị xóa'
                                                      , 'actionName'  => 'MajorController@deletePriorityCorporation'
                                                      ))
                    </td>
                </tr>
                <?php               }
                                }
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

                    $cBenchmark         = ($child->benchmark != 0) ? CommonUtils::formatFloatValue($child->benchmark, \Utils\commonUtils::NUMBER_AFTER_DOT) : '-';
                    $cPercentComplete   = ($child->percent_complete != 0) ? CommonUtils::formatFloatValue($child->percent_complete * 100, \Utils\commonUtils::NUMBER_AFTER_DOT).'%' : '-';
                    $cImplementPoint    = ($child->implement_point != 0) ? CommonUtils::formatFloatValue($child->implement_point, \Utils\commonUtils::NUMBER_AFTER_DOT) : '-';
                ?>
                <tr style="background-color: white">

                    <td class="order-column">{{ $stt++ }}</td>
                    <td class="text-left">{{ $child->goal_code }}</td>
                    <td class="text-left">{{ $child->goal_name }}</td>
                    <td class="text-right">{{ $cTargetValue }}</td>
                    <td class="text-right">{{ $child->important_level }}</td>
                    <td class="text-right">{{ $cBenchmark }}</td>
                    <td class="text-right">{{$cPercentComplete  }}</td>
                    <td class="text-right">{{ $cImplementPoint }}</td>

                    <td class="table-icon text-center">
                        <a href="{{url('updatePriorityCorporation').'/'.$child->id}}" role="button" title="Cập nhật">
                            <i class="fa fa-pencil-square-o"></i>
                        </a>
                    </td>
                    <td class="table-icon text-center">
                        <a role="button" title="Xóa" style="cursor: pointer;" data-target=".dialog-delete-goal-<?php echo $child->id; ?>" data-toggle="modal">
                            <i class="fa fa-trash"></i>
                        </a>
                        @include('popup.confirmDelete', array('rowId' => $child->id
                                                      , 'data' => $child->id
                                                      , 'strName' => $child->goal_name.'<br/><br/><span style="color:red;">* Chú ý:</span> Sau khi xóa thì Tỷ trọng/Kế hoạch/Thực hiện của Phòng/Đài/MBF HCM, Tổ/Quận/Huyện, Chức danh, Nhân viên đối với mục tiêu: '.$child->goal_name.' áp dụng từ ngày: '.\Utils\commonUtils::formatDate($child->apply_date).' năm: '.date('Y', strtotime($child->apply_date)).' sẽ bị xóa'
                                                      , 'actionName'  => 'MajorController@deletePriorityCorporation'
                                                      ))
                    </td>
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
        fixHeader($("#tblPriorityCorporation"), $("#tblPriorityCorporation > thead"), $("#header-fixed"));

    </script>
@stop