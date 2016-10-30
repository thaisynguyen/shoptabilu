@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.TARGET_VALUE_SECTION') . ' Cho ' . Config::get('constant.AREA'))
@section('section')
@include('alerts.errors')
@include('alerts.success')
    <?php
    use Utils\CommonUtils;
    $goalTypes = CommonUtils::arrGoalType(0);
    $view = \Utils\commonUtils::checkIsView();
    $currentCompanyId = Session::get('scompany_id');
    $currentAreaId = Session::get('sarea_id');
    $accessLevel = Session::get('saccess_level');

    ?>
    <div id="wrapper">
        <div class="row margin-form">

            <div class="col-sm-12">
                <div class="col-sm-2 control-label padding-top-frm padding-right-15 font-label-form text-right">
                    Phòng/Đài/MBF HCM (*):
                </div>
                <div class="col-sm-4 form-group">
                    <select class="form-control combobox-99" id="cboCompany"
                            onchange="reloadPageWithParam('{{action('MajorController@manageGoalArea')}}'
                                    , 'manageGoalArea'
                                    , 'cboCompany/cboArea/cboGoal/cboGoalType/cboYear/cboMonth')">
                        <option value="0"> --- Chọn Phòng/Đài/MBF HCM --- </option>
                        <?php
                        if($accessLevel > 1){
                        foreach ($companies as $company) {
                             if($currentCompanyId == $company->id){?>
                                <option value="<?php echo $company->id;?>" <?php if ($company->id == $selectedCompany) {echo 'selected';} ?>>
                                    <?php echo $company->company_name;?>
                                </option>
                        <?php }}} else {
                            foreach ($companies as $company) {?>
                            <option value="<?php echo $company->id;?>" <?php if ($company->id == $selectedCompany) {echo 'selected';} ?>>
                                <?php echo $company->company_name;?>
                            </option>
                        <?php }} ?>
                    </select>
                </div>
                <div class="col-sm-6"></div>
            </div>

            <div class="col-sm-12">
                <div class="col-sm-2 control-label padding-top-frm padding-right-15 font-label-form text-right">
                    Tổ/Quận/Huyện:
                </div>
                <div class="col-sm-4 form-group">
                    <select class="form-control combobox-99" id="cboArea"
                            onchange="reloadPageWithParam('{{action('MajorController@manageGoalArea')}}'
                                    , 'manageGoalArea'
                                    , 'cboCompany/cboArea/cboGoal/cboGoalType/cboYear/cboMonth')">
                        <option value="0">Tất cả</option>
                        <?php
                        if($accessLevel > 2){
                        foreach ($areas as $area) {
                                if($currentAreaId == $area->id){ ?>
                        <option value="<?php echo $area->id;?>" <?php if ($area->id == $selectedArea) {echo 'selected';} ?>>
                            <?php echo $area->area_name;?>
                        </option>
                        <?php }}} else {
                        foreach ($areas as $area) {?>
                            <option value="<?php echo $area->id;?>" <?php if ($area->id == $selectedArea) {echo 'selected';} ?>>
                                <?php echo $area->area_name;?>
                            </option>
                        <?php }}?>
                    </select>
                </div>
                <div class="col-sm-1 control-label padding-top-frm padding-right-15 font-label-form text-right">Mục
                    tiêu:
                </div>
                <div class="col-sm-5 form-group">
                    <select class="form-control combobox-99" id="cboGoal"
                            onchange="reloadPageWithParam('{{action('MajorController@manageGoalArea')}}'
                                    , 'manageGoalArea'
                                    , 'cboCompany/cboArea/cboGoal/cboGoalType/cboYear/cboMonth')">
                        <option value="0">Tất cả</option>
                        <?php foreach ($gOnes as $gOne) { ?>
                        <option value="<?php echo $gOne->id;?>" <?php if ($gOne->id == $selectedGoal) {
                            echo 'selected';
                        } ?>><?php echo $gOne->goal_name;?></option>
                        <?php
                        foreach ($gTwos as $gTwo){
                        if($gTwo->parent_id == $gOne->id){?>
                        <option value="<?php echo $gTwo->id; ?>" <?php if($gTwo->id == $selectedGoal){  echo 'selected'; } ?> class="margin-left-20">
                            <?php echo $gTwo->goal_name; ?>
                        </option>
                        <?php }
                        } ?>
                        <?php } ?>
                    </select>
                </div>

            </div>

            <div class="col-sm-12">
                <div class="col-sm-2 control-label padding-top-frm padding-right-15 font-label-form text-right">Loại mục
                    tiêu:
                </div>
                <div class="col-sm-4 form-group">
                    <select class="form-control combobox-99" id="cboGoalType"
                            onchange="reloadPageWithParam('{{action('MajorController@manageGoalArea')}}'
                                    , 'manageGoalArea'
                                    , 'cboCompany/cboArea/cboGoal/cboGoalType/cboYear/cboMonth')">
                        <option value="-1">Tất cả</option>
                        <?php
                        foreach($goalTypes as $goalType ){ ?>
                        <option value="<?php echo $goalType['id'];?>" <?php if ($goalType['id'] == $selectedGoalType) {
                            echo 'selected';
                        } ?>><?php echo $goalType['name'];?></option>
                        <?php  } ?>
                    </select>
                </div>

                <div class="col-sm-1 control-label padding-top-frm padding-right-15 font-label-form text-right">Năm (*):
                </div>
                <div class="col-sm-2 form-group">
                    <select class="form-control text-left combobox-99" id="cboYear"
                            onchange="reloadPageWithParam('{{action('MajorController@manageGoalArea')}}'
                                    , 'manageGoalArea'
                                    , 'cboCompany/cboArea/cboGoal/cboGoalType/cboYear/cboMonth')">
                        <?php $arrYear = \Utils\commonUtils::getArrYear($dataYears) ; foreach($arrYear as $year) { ?>
                        <option value="<?php echo $year;?>" class="text-left" <?php if ($year == $selectedYear) {
                            echo 'selected';
                        } ?>><?php echo $year;?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-sm-1 control-label padding-top-frm padding-right-15 font-label-form text-right">Tháng (*):
                </div>
                <div class="col-sm-2 form-group">
                    <select class="form-control text-left combobox-99" id="cboMonth"
                            onchange="reloadPageWithParam('{{action('MajorController@manageGoalArea')}}'
                                    , 'manageGoalArea'
                                    , 'cboCompany/cboArea/cboGoal/cboGoalType/cboYear/cboMonth')">
                        <?php for($m = 1 ; $m <= 12 ; $m++) { ?>
                        <option value="<?php echo $m;?>" class="text-left" <?php if ($m == $selectedMonth) {
                            echo 'selected';
                        } ?>><?php echo $m;?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="col-sm-12 col-offset-3">
                <div class="col-sm-3 control-label padding-top-frm padding-right-15 font-label-form text-right"></div>
                <div class="col-sm-4 form-group font-label-form hidden" style="color: red !important;" id="NoteExport">Xuất Excel để xem dữ liệu!</div>
                <div class="col-sm-5 "></div>
            </div>
                <?php
                    if(!$view && $accessLevel <= 3){
                ?>
            <a role="button" id="expGoal" class="btn btn-primary pull-right marg-bottom-10"
               onclick="reloadPageWithParam('{{action('ExportExcelController@exportGoalArea')}}'
                       , 'exportGoalArea'
                       , 'cboCompany/cboArea/cboGoal/cboGoalType/cboYear/cboMonth')"><i
                        class="fa fa-sign-out"></i> Xuất Excel</a>

            <a role="button" id="addGoalArea" class="btn btn-primary pull-right marg-bottom-10"
               onclick="reloadPageWithParam('{{action('MajorController@addGoalArea')}}'
                       , 'addGoalArea'
                       , 'cboCompany/cboArea/cboGoal/cboGoalType/cboMonth')"
               ><i class="fa fa-plus"></i> Thêm mới</a>
            <?php
            }
            ?>
            <table id="tblGoalArea" class="table-common">
                <thead>
                <tr class="text-center">
                    <th>STT</th>
                    <th class="col-sm-1">Mã</th>
                    <th class="col-sm-3">Tên Mục Tiêu</th>
                    <th class="col-sm-1">Loại Mục Tiêu</th>
                    <th class="col-sm-1">Kế hoạch</th>
                    <th class="col-sm-1">ĐTH KPI</th>
                    <th class="col-sm-1">Tỷ trọng</th>
                    <th class="col-sm-1">Điểm chuẩn</th>
                    <th class="col-sm-1">Điểm chuẩn KPI</th>
                    <th class="col-sm-1">Điểm thực hiện</th>
                    <th class="col-sm-1">Tỉ lệ đạt</th>
                    <?php
                    if(!$view && $accessLevel < 3){
                    ?>
                    <th></th>
                    <!--<th></th>-->
                    <?php
                    }
                    ?>
                </tr>
                </thead>
                <tbody>
                <?php
                if($isParent != 0 && count($arrILA) > 0){
                foreach($arrILA as $ila){ ?>
                <tr style="background-color: #D8E4BC">
                    <td colspan="6">{{$ila['goalName']}}</td>
                    <td class="text-right">{{$ila['importantLevel'];}}</td>
                    <td class="text-right">{{ \Utils\commonUtils::formatFloatValue($ila['benchmark'], \Utils\commonUtils::NUMBER_AFTER_DOT); }}</td>
                    <td class="text-right">{{ \Utils\commonUtils::formatFloatValue($ila['cal_benchmark'], \Utils\commonUtils::NUMBER_AFTER_DOT); }}</td>
                    <td class="text-right">&nbsp;</td>
                    <td class="text-right">&nbsp;</td>
                    <?php
                    if(!$view && $accessLevel < 3){
                    ?>
                    <td class="text-right">&nbsp;</td>
                    <!--<td class="text-right">&nbsp;</td>-->
                    <?php
                    }
                    ?>
                </tr>
                <?php $stt = 1;
                foreach($data as $ta){
                $taParentId = $ta->parent_id;
                #Percent require

                if(\Utils\commonUtils::compareTwoString($ta->unit_code, \Utils\commonUtils::PERCENT_CODE) == 1){
                    $cTargetValue       = ($ta->target_value != 0) ? \Utils\commonUtils::formatFloatValue($ta->target_value, \Utils\commonUtils::NUMBER_AFTER_DOT).'%' : '-';
                }else{
                    $cTargetValue       = ($ta->target_value != 0) ? \Utils\commonUtils::formatFloatValue($ta->target_value, \Utils\commonUtils::DF_NUMBER_AFTER_DOT) : '-';
                }

                $cRealPercent       = ($ta->real_percent != 0) ? round(($ta->real_percent * 100), 2).'%' : '-';
                $cBenchmark         = ($ta->benchmark != 0) ? \Utils\commonUtils::formatFloatValue($ta->benchmark, \Utils\commonUtils::NUMBER_AFTER_DOT) : '-';
                $cCalBenchmark      = ($ta->cal_benchmark != 0) ? \Utils\commonUtils::formatFloatValue($ta->cal_benchmark, \Utils\commonUtils::NUMBER_AFTER_DOT) : '-';
                $cImplementPoint    = ($ta->implement_point != 0) ? \Utils\commonUtils::formatFloatValue($ta->implement_point, \Utils\commonUtils::NUMBER_AFTER_DOT) : '-';
                //$cTargetValue       = ($ta->target_value != 0) ? \Utils\commonUtils::formatFloatValue($ta->target_value, \Utils\commonUtils::NUMBER_AFTER_DOT) : '-';
                $cCalIP             = ($ta->cal_implement_point != 0) ? \Utils\commonUtils::formatFloatValue($ta->cal_implement_point, \Utils\commonUtils::NUMBER_AFTER_DOT) : '-';

                if($taParentId == $ila['goalId']){ ?>
                <tr style="background-color: white">
                    <td >{{ $stt++ }}</td>
                    <td >{{ $ta->goal_code; }}</td>
                    <td >{{ $ta->goal_name; }}</td>
                    <td >{{ \Utils\commonUtils::renderGoalTypeName($ta->goal_type); }}</td>
                    <td class="text-right">{{ $cTargetValue; }}</td>
                    <td class="text-right">{{ $cCalIP; }}</td>
                    <td class="text-right">{{ $ta->important_level; }}</td>
                    <td class="text-right">{{ $cBenchmark; }}</td>
                    <td class="text-right">{{ $cCalBenchmark; }}</td>
                    <td class="text-right">{{ $cImplementPoint; }}</td>
                    <td class="text-right">{{ $cRealPercent; }}</td>
                    <?php
                    if(!$view && $accessLevel < 3){
                    ?>
                    <td class="table-icon text-center">
                        <a href="{{url('updateGoalArea').'/'.$ta->id}}" role="button" title="Cập nhật">
                            <i class="fa fa-pencil-square-o"></i>
                        </a>
                    </td>

                    <?php
                    }
                    ?>
                </tr>
                <?php }
                }
                }
                }else{
                $stt = 1;
                foreach($data as $child){
                $implementPoint = 0;
                if($child->target_value != 0){
                    $implementPoint = $child->implement / $child->target_value;
                }

                #Percent require
                $cPercentRequire = round(($child->percent_required * 100), 2).'%';
                $cRealPercent = round(($child->real_percent * 100), 2).'%';

                if(\Utils\commonUtils::compareTwoString($child->unit_code, \Utils\commonUtils::PERCENT_CODE) == 1){
                    $cTargetValue       = ($child->target_value != 0) ? \Utils\commonUtils::formatFloatValue($child->target_value, \Utils\commonUtils::NUMBER_AFTER_DOT).'%' : '-';
                }else{
                    $cTargetValue       = ($child->target_value != 0) ? \Utils\commonUtils::formatFloatValue($child->target_value, \Utils\commonUtils::DF_NUMBER_AFTER_DOT) : '-';
                }

                $cRealPercent       = ($child->real_percent != 0) ? round(($child->real_percent * 100), 2).'%' : '-';
                $cBenchmark         = ($child->benchmark != 0) ? \Utils\commonUtils::formatFloatValue($child->benchmark, \Utils\commonUtils::NUMBER_AFTER_DOT) : '-';
                $cCalBenchmark      = ($child->cal_benchmark != 0) ? \Utils\commonUtils::formatFloatValue($child->cal_benchmark, \Utils\commonUtils::NUMBER_AFTER_DOT) : '-';
                $cImplementPoint    = ($child->implement_point != 0) ? \Utils\commonUtils::formatFloatValue($child->implement_point, 8) : '-';
                //$cTargetValue       = ($child->target_value != 0) ? \Utils\commonUtils::formatFloatValue($child->target_value, \Utils\commonUtils::NUMBER_AFTER_DOT) : '-';
                $cCalIP             = ($child->cal_implement_point != 0) ? \Utils\commonUtils::formatFloatValue($child->cal_implement_point, \Utils\commonUtils::NUMBER_AFTER_DOT) : '-';

                ?>
                <tr style="background-color: white">
                    <td><?php echo $stt++; ?></td>
                    <td><?php echo $child->goal_code; ?></td>
                    <td><?php echo $child->goal_name; ?></td>
                    <td><?php echo \Utils\commonUtils::renderGoalTypeName($child->goal_type); ?></td>
                    <td class="text-right">{{ $cTargetValue; }}</td>
                    <td class="text-right">{{ $cCalIP; }}</td>
                    <td class="text-right">{{ $child->important_level; }}</td>
                    <td class="text-right">{{ $cBenchmark; }}</td>
                    <td class="text-right">{{ $cCalBenchmark; }}</td>
                    <td class="text-right">{{ $cImplementPoint; }}</td>
                    <td class="text-right">{{ $cRealPercent; }}</td>
                    <?php
                    if(!$view && $accessLevel < 3){
                    ?>
                    <td class="table-icon text-center" >
                        <a href="{{url('updateGoalArea').'/'.$child->id}}" role="button" title="Cập nhật">
                            <i class="fa fa-pencil-square-o"></i>
                        </a>
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
            <!-- fixed header for scroll ------->
            <table id="header-fixed"></table>
        </div>
    </div>
    <script>
        fixHeader($("#tblGoalArea"), $("#tblGoalArea > thead"), $("#header-fixed"));
        $( document ).ready(function() {

            var valArea     = $('#cboArea').val();
            var valCompany  = $('#cboCompany').val();
            var valGoalType = $('#cboGoalType').val();
            var valGoalId   = $('#cboGoal').val();
            var valMonth    = $('#cboMonth').val();

            if(valArea == 0 && valCompany != 0 ){
                $('#NoteExport').removeClass('hidden');
            }

            /*--------------------------------------------------------------------------------------------------------*/
            var accessLevel = "<?php echo Session::get('saccess_level');?>";
            if(accessLevel > 2){
                document.getElementById('addGoalArea').style.display = 'none';
            }

            if(
                    valGoalType     == -1
                    || valGoalId    == 0
                    || valMonth     == 0
                    || valCompany   == 0
                    || valArea      == 0
            ){
                $('#addGoalArea').attr('disabled','disabled');
            }else{
                $('#addGoalArea').removeAttr('disabled');
            }

            /*--------------------------------------------------------------------------------------------------------*/
        });
    </script>
@stop