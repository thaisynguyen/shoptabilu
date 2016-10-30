@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.TARGET_VALUE_SECTION') . ' Cho ' . Config::get('constant.EMPLOYEE'))
@section('section')
@include('alerts.errors')
@include('alerts.success')
<?php
use Utils\CommonUtils;
$goalTypes          = CommonUtils::arrGoalType(0);
//$view               = \Utils\commonUtils::checkIsView();
$currentCompanyId   = Session::get('scompany_id');
$currentAreaId      = Session::get('sarea_id');
$accessLevel        = Session::get('saccess_level');
$sDataUser        = Session::get('sDataUser');
        $view = $sDataUser->is_view;
        $hiddenExportExcel = ($view == 0) ? '' : 'hidden';
        $hiddenAR = ($view == 1 || $accessLevel > 3) ? 'hidden' : '';
?>

    <div id="wrapper">
        <div class="row margin-form">

            <div class="col-sm-12">
                <div class="col-sm-2 control-label padding-top-frm padding-right-15 font-label-form text-right">
                    Phòng/Đài/MBF HCM (*):
                </div>
                <div class="col-sm-4 form-group">
                    <select class="form-control combobox-99" id="cboCompany"
                            onchange="reloadPageWithParam('{{action('MajorController@manageGoalEmployee')}}'
                                    , 'manageGoalEmployee'
                                    , 'cboCompany/cboArea/cboPosition/cboUser/cboGoal/cboGoalType/cboYear/cboMonth')">
                        <option value = '0' class="default-selected">Chọn Phòng/Đài/MBF HCM</option>
                        <?php
                        if($accessLevel > 1){
                        foreach ($companies as $company) {
                                if($company->id == $currentCompanyId){ ?>
                                <option value="<?php echo $company->id;?>" <?php if ($company->id == $selectedCompany) { echo 'selected';} ?>>
                                    <?php echo $company->company_name;?>
                                </option>
                        <?php }}} else {
                        foreach ($companies as $company) {?>
                        <option value="<?php echo $company->id;?>" <?php if ($company->id == $selectedCompany) { echo 'selected';} ?>>
                            <?php echo $company->company_name;?>
                        </option>
                        <?php }}?>
                    </select>
                </div>
                <div class="col-sm-5"></div>
            </div>

            <div class="col-sm-12">
                <div class="col-sm-2 control-label padding-top-frm padding-right-15 font-label-form text-right">
                    Tổ/Quận/Huyện (*):
                </div>
                <div class="col-sm-4 form-group">
                    <select class="form-control combobox-99" id="cboArea"
                            onchange="reloadPageWithParam('{{action('MajorController@manageGoalEmployee')}}'
                                    , 'manageGoalEmployee'
                                    , 'cboCompany/cboArea/cboPosition/cboUser/cboGoal/cboGoalType/cboYear/cboMonth')">
                        <option value="0"> --- Chọn Tổ/Quận/Huyện --- </option>
                        <?php
                        if($accessLevel > 2){
                        foreach ($areas as $area) {
                                if($area->id == $currentAreaId){?>
                                    <option value="<?php echo $area->id;?>" <?php if ($area->id == $selectedArea) {echo 'selected';} ?>>
                                        <?php echo $area->area_name;?>
                                    </option>
                        <?php }}} else {
                            foreach ($areas as $area) {?>
                            <option value="<?php echo $area->id;?>" <?php if ($area->id == $selectedArea) {echo 'selected';} ?>>
                                <?php echo $area->area_name;?>
                            </option>
                         <?php }} ?>
                    </select>
                </div>

                <div class="col-sm-1 control-label padding-top-frm padding-right-15 font-label-form text-right">Loại mục
                    tiêu:
                </div>
                <div class="col-sm-2 form-group">
                    <select class="form-control combobox-99" id="cboGoalType"
                            onchange="reloadPageWithParam('{{action('MajorController@manageGoalEmployee')}}'
                                    , 'manageGoalEmployee'
                                    , 'cboCompany/cboArea/cboPosition/cboUser/cboGoal/cboGoalType/cboYear/cboMonth')">
                        <option value="-1">Tất cả </option>

                        <?php
                        foreach($goalTypes as $goalType ){ ?>
                        <option value="<?php echo $goalType['id'];?>" <?php if ($goalType['id'] == $selectedGoalType) {
                            echo 'selected';
                        } ?>><?php echo $goalType['name'];?></option>
                        <?php  } ?>
                    </select>
                </div>
                <div class="col-sm-3"></div>
            </div>

            <div class="col-sm-12">
                <div class="col-sm-2 control-label padding-top-frm padding-right-15 font-label-form text-right">Chức
                    danh:
                </div>
                <div class="col-sm-4 form-group">
                    <select class="form-control combobox-99" id="cboPosition"
                            onchange="reloadPageWithParam('{{action('MajorController@manageGoalEmployee')}}'
                                    , 'manageGoalEmployee'
                                    , 'cboCompany/cboArea/cboPosition/cboUser/cboGoal/cboGoalType/cboYear/cboMonth')">
                        <option value="0">Tất cả</option>
                        <?php foreach ($positions as $position) { ?>
                        <option value="<?php echo $position->id;?>" <?php if ($position->id == $selectedPosition) {
                            echo 'selected';
                        } ?>><?php echo $position->position_name;?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-sm-1 control-label padding-top-frm padding-right-15 font-label-form text-right">Mục
                    tiêu:
                </div>
                <div class="col-sm-5 form-group">
                    <select class="form-control combobox-99" id="cboGoal"
                            onchange="reloadPageWithParam('{{action('MajorController@manageGoalEmployee')}}'
                                    , 'manageGoalEmployee'
                                    , 'cboCompany/cboArea/cboPosition/cboUser/cboGoal/cboGoalType/cboYear/cboMonth')">
                        <option value="0">Tất cả </option>
                        <?php foreach ($gOnes as $gOne) { ?>
                        <option value="<?php echo $gOne->id;?>" <?php if ($gOne->id == $selectedGoal) {
                            echo 'selected';
                        } ?> class="text-bold"><?php echo $gOne->goal_name;?></option>

                        <?php
                        foreach ($gTwos as $gTwo){
                        if($gTwo->parent_id == $gOne->id){?>
                        <option value="<?php echo $gTwo->id; ?>" <?php if ($gTwo->id == $selectedGoal) {
                            echo 'selected';
                        } ?> class="margin-left-20"><?php echo $gTwo->goal_name; ?></option>
                        <?php }
                        } ?>

                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="col-sm-12">
                <div class="col-sm-2 control-label padding-top-frm padding-right-15 font-label-form text-right">Nhân
                    viên:
                </div>
                <div class="col-sm-4 form-group">
                    <select class="form-control combobox-99" id="cboUser"
                            onchange="reloadPageWithParam('{{action('MajorController@manageGoalEmployee')}}'
                                    , 'manageGoalEmployee'
                                    , 'cboCompany/cboArea/cboPosition/cboUser/cboGoal/cboGoalType/cboYear/cboMonth')">
                        <option value="0">Tất cả</option>
                        <?php foreach ($users as $user) {
                                if($user->id != 0 && $user->id != 1 && $user->id != 2 && $user->id != 3){?>
                        <option value="<?php echo $user->id;?>" <?php if ($user->id == $selectedUser) {
                            echo 'selected';
                        } ?>><?php echo $user->name;?></option>
                        <?php }} ?>
                    </select>
                </div>

                <div class="col-sm-1 control-label padding-top-frm padding-right-15 font-label-form text-right">Năm (*):
                </div>
                <div class="col-sm-2 form-group">
                    <select class="form-control text-left combobox-99" id="cboYear"
                            onchange="reloadPageWithParam('{{action('MajorController@manageGoalEmployee')}}'
                                    , 'manageGoalEmployee'
                                    , 'cboCompany/cboArea/cboPosition/cboUser/cboGoal/cboGoalType/cboYear/cboMonth')">
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
                            onchange="reloadPageWithParam('{{action('MajorController@manageGoalEmployee')}}'
                                    , 'manageGoalEmployee'
                                    , 'cboCompany/cboArea/cboPosition/cboUser/cboGoal/cboGoalType/cboYear/cboMonth')">
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

            <a role="button" id="expGoalEmployee" class="btn btn-primary pull-right marg-bottom-10 {{ $hiddenExportExcel }}"
               onclick="reloadPageWithParam('{{action('ExportExcelController@exportGoalEmployee')}}'
                       , 'exportGoalEmployee'
                       , 'cboCompany/cboArea/cboPosition/cboUser/cboGoal/cboGoalType/cboYear/cboMonth')"><i class="fa fa-sign-out"></i> Xuất Excel</a>

            <a role="button" id="addGoalEmployee" class="btn btn-primary pull-right marg-bottom-10 {{ $hiddenAR }}"
               onclick="reloadPageWithParam('{{action('MajorController@addGoalEmployee')}}'
                       , 'addGoalEmployee'
                       , 'cboCompany/cboArea/cboPosition/cboUser/cboGoal/cboGoalType/cboMonth')"
            ><i class="fa fa-plus"></i> Thêm mới</a>

            <table id="tblGoalEmployee" class="table-common">
                <thead>
                <tr class="text-center">
                    <th class="col-sm-1">STT</th>
                    <th class="col-sm-3">Mục Tiêu</th>
                    <th class="col-sm-1">Loại Mục Tiêu</th>
                    <th class="col-sm-1">Kế hoạch</th>
                    <th class="col-sm-1">Thực hiện</th>
                    <th class="col-sm-1">Điểm thực hiện</th>
                    <th class="col-sm-1">Tỷ trọng</th>
                    <th class="col-sm-1">Điểm chuẩn</th>
                    <th class="col-sm-1">Điểm chuẩn KPI</th>
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
                if($isParent != 0){
                foreach($arrParent as $parent){ ?>
                <tr style="background-color: #D8E4BC">
                    <td colspan="11"><?php echo $parent['pName']; ?></td>
                </tr>
                <?php
                $stt = 1;
                foreach($data as $child){
                if($child->parent_id == $parent['pId']){
                $typeName = CommonUtils::renderGoalTypeName($child->goal_type);
                $targetValue = CommonUtils::formatFloatValue($child->target_value, \Utils\commonUtils::NUMBER_AFTER_DOT);
                $implement = ($child->implement != 0) ? CommonUtils::formatFloatValue($child->implement, \Utils\commonUtils::NUMBER_AFTER_DOT) : '-';
                $implementPoint = ($child->implement_point != 0) ? CommonUtils::formatFloatValue($child->implement_point, \Utils\commonUtils::NUMBER_AFTER_DOT) : '-';
                $benchmark = CommonUtils::formatFloatValue($child->benchmark, \Utils\commonUtils::NUMBER_AFTER_DOT);
                $calBenchmark = CommonUtils::formatFloatValue($child->cal_benchmark, \Utils\commonUtils::NUMBER_AFTER_DOT);
                ?>
                <tr style="background-color: white">
                    <td class="order-column"><?php echo $stt++; ?></td>
                    <td><?php echo $child->goal_name; ?></td>
                    <td><?php echo $typeName; ?></td>
                    <td class="text-right"><?php echo $targetValue;  ?></td>
                    <td class="text-right"><?php echo $implement; ?></td>
                    <td class="text-right"><?php echo $implementPoint; ?></td>
                    <td class="text-right"><?php echo $child->important_level; ?></td>
                    <td class="text-right"><?php echo $benchmark; ?></td>
                    <td class="text-right"><?php echo $calBenchmark; ?></td>
                    <?php
                    if(!$view && $accessLevel < 3){
                    ?>
                    <td class="table-icon text-center">
                        <a href="{{url('updateGoalEmployee').'/'.$child->id}}" role="button" title="Cập nhật">
                            <i class="fa fa-pencil-square-o"></i>
                        </a>
                    </td>

                    <?php
                    }
                    ?>
                </tr>
                <?php  }
                }
                ?>
                <?php }
                }else{
                $stt = 1;
                foreach($data as $child){ ?>
                <tr style="background-color: white">
                    <td class="order-column"><?php echo $stt++; ?></td>
                    <td><?php echo $child->goal_name; ?></td>
                    <td><?php echo \Utils\commonUtils::renderGoalTypeName($child->goal_type); ?></td>
                    <td class="text-right"><?php echo \Utils\commonUtils::formatFloatValue($child->target_value, \Utils\commonUtils::NUMBER_AFTER_DOT);  ?></td>
                    <td class="text-right"><?php echo \Utils\commonUtils::formatFloatValue($child->implement, \Utils\commonUtils::NUMBER_AFTER_DOT);; ?></td>
                    <td class="text-right"><?php echo $child->implement_point; ?></td>
                    <td class="text-right"><?php echo $child->important_level; ?></td>
                    <td class="text-right"><?php echo $child->benchmark; ?></td>
                    <td class="text-right"><?php echo $child->cal_benchmark; ?></td>
                    <?php
                    if(!$view && $accessLevel < 3){
                    ?>
                    <td class="table-icon text-center">
                        <a role="button" title="Cập nhật">
                            <a href="{{url('updateGoalEmployee').'/'.$child->id}}" role="button" title="Cập nhật">
                                <i class="fa fa-pencil-square-o"></i>
                            </a>
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
            <!-- fixed header for scroll -->
            <table id="header-fixed"></table>
        </div>
        <script>
            fixHeader($("#tblGoalEmployee"), $("#tblGoalEmployee > thead"), $("#header-fixed"));
            $( document ).ready(function() {

                var valArea     = $('#cboArea').val();
                var valCompany  = $('#cboCompany').val();
                var valPosition = $('#cboPosition').val();
                var valUser     = $('#cboUser').val();
                var valGoalType = $('#cboGoalType').val();
                var valGoalId   = $('#cboGoal').val();
                var valMonth    = $('#cboMonth').val();

                if(valArea != 0 && valCompany != 0 && (valPosition == 0 || valUser == 0)){
                    $('#NoteExport').removeClass('hidden');
                }

                /*--------------------------------------------------------------------------------------------------------*/
                var accessLevel = "<?php echo Session::get('saccess_level');?>";
                if(accessLevel < 3 && accessLevel > 4){
                    document.getElementById('addGoalEmployee').style.display = 'none';
                }

                if(
                        valGoalType     == -1
                        || valGoalId    == 0
                        || valMonth     == 0
                        || valCompany   == 0
                        || valArea      == 0
                        || valPosition  == 0
                        || valUser      == 0
                ){
                    $('#addGoalEmployee').attr('disabled','disabled');
                }else{
                    $('#addGoalEmployee').removeAttr('disabled');
                }

                /*--------------------------------------------------------------------------------------------------------*/
            });
        </script>
    </div>
@stop