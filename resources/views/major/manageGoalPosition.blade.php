@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.TARGET_VALUE_SECTION') . ' Cho ' . Config::get('constant.POSITION'))
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
                            onchange="reloadPageWithParam('{{action('MajorController@manageGoalPosition')}}'
                                    , 'manageGoalPosition'
                                    , 'cboCompany/cboArea/cboPosition/cboGoal/cboGoalType/cboYear/cboMonth')">
                        <option value = '0' class="default-selected">----- Chọn Phòng/Đài/MBF HCM -----</option>
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

                <div class="col-sm-1 control-label padding-top-frm padding-right-15 font-label-form text-right">Loại mục
                    tiêu:
                </div>
                <div class="col-sm-2 form-group">
                    <select class="form-control combobox-99" id="cboGoalType"
                            onchange="reloadPageWithParam('{{action('MajorController@manageGoalPosition')}}'
                                    , 'manageGoalPosition'
                                    , 'cboCompany/cboArea/cboPosition/cboGoal/cboGoalType/cboYear/cboMonth')">
                        <option value="-1">Tất cả</option>
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
                <div class="col-sm-2 control-label padding-top-frm padding-right-15 font-label-form text-right">
                    Tổ/Quận/Huyện (*):
                </div>
                <div class="col-sm-4 form-group">
                    <select class="form-control combobox-99" id="cboArea"
                            onchange="reloadPageWithParam('{{action('MajorController@manageGoalPosition')}}'
                                    , 'manageGoalPosition'
                                    , 'cboCompany/cboArea/cboPosition/cboGoal/cboGoalType/cboYear/cboMonth')">
                        <option value="0"> --- Chọn Tổ/Quận/Huyện --- </option>
                        <?php
                        if($accessLevel > 2){
                            foreach ($areas as $area) {
                                    if($currentAreaId == $area->id){?>
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
                    Tiêu:
                </div>
                <div class="col-sm-5 form-group">
                    <select class="form-control combobox-99" id="cboGoal"
                            onchange="reloadPageWithParam('{{action('MajorController@manageGoalPosition')}}'
                                    , 'manageGoalPosition'
                                    , 'cboCompany/cboArea/cboPosition/cboGoal/cboGoalType/cboYear/cboMonth')">
                        <option value="0" class="option-bold">Tất cả</option>
                        <?php foreach ($gOnes as $gOne){?>
                        <option value="<?php echo $gOne->id; ?>"
                        <?php if ($gOne->id == $selectedGoal) {
                            echo 'selected';
                        } ?>
                                ><?php echo $gOne->goal_name ?></option>
                        <?php
                        foreach ($gTwos as $gTwo){
                        if($gTwo->parent_id == $gOne->id){?>
                        <option value="<?php echo $gTwo->id; ?>"
                        <?php if ($gTwo->id == $selectedGoal) {echo 'selected';} ?>>
                            <?php echo $gTwo->goal_name ?>
                        </option>
                        <?php }
                        }
                        }?>
                    </select>
                </div>
            </div>

            <div class="col-sm-12">
                <div class="col-sm-2 control-label padding-top-frm padding-right-15 font-label-form text-right">Chức
                    danh:
                </div>
                <div class="col-sm-4 form-group">
                    <select class="form-control combobox-99" id="cboPosition"
                            onchange="reloadPageWithParam('{{action('MajorController@manageGoalPosition')}}'
                                    , 'manageGoalPosition'
                                    , 'cboCompany/cboArea/cboPosition/cboGoal/cboGoalType/cboYear/cboMonth')">
                        <option value="0">Tất cả</option>
                        <?php foreach ($positions as $position) { ?>
                        <option value="<?php echo $position->id;?>" <?php if ($position->id == $selectedPosition) {
                            echo 'selected';
                        } ?>><?php echo $position->position_name;?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-sm-1 control-label padding-top-frm padding-right-15 font-label-form text-right">Năm (*):
                </div>
                <div class="col-sm-2 form-group">
                    <select class="form-control text-left combobox-99" id="cboYear"
                            onchange="reloadPageWithParam('{{action('MajorController@manageGoalPosition')}}'
                                    , 'manageGoalPosition'
                                    , 'cboCompany/cboArea/cboPosition/cboGoal/cboGoalType/cboYear/cboMonth')">

                        <?php $arrYear = \Utils\commonUtils::getArrYear($dataYears) ; foreach($arrYear as $year) { ?>
                        <option value="<?php echo $year;?>" class="text-left" <?php if ($year == $selectedYear) {
                            echo 'selected';
                        } ?>><?php echo $year;?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-sm-1 control-label padding-top-frm padding-right-15 font-label-form text-right">Tháng (*):
                </div>
                <div class="col-sm-2 form-group ">
                    <select class="form-control text-left combobox-99" id="cboMonth"
                            onchange="reloadPageWithParam('{{action('MajorController@manageGoalPosition')}}'
                                    , 'manageGoalPosition'
                                    , 'cboCompany/cboArea/cboPosition/cboGoal/cboGoalType/cboYear/cboMonth')">
                        <option value="0">Tất cả</option>
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
            <a role="button" id="expGoalPosition" class="btn btn-primary pull-right marg-bottom-10"
               onclick="reloadPageWithParam('{{action('ExportExcelController@exportGoalPosition')}}'
                       , 'exportGoalPosition'
                       , 'cboCompany/cboArea/cboPosition/cboGoal/cboGoalType/cboYear/cboMonth')"><i class="fa fa-sign-out"></i> Xuất Excel</a>

            <a  role="button" id="addGoalPosition" class="btn btn-primary pull-right change-color style-btn-addnew"
                onclick="reloadPageWithParam('{{action('MajorController@addGoalPosition')}}'
                        , 'addGoalPosition'
                        , 'cboCompany/cboArea/cboPosition/cboGoal/cboGoalType/cboMonth')"
            ><i class="fa fa-plus"></i> Thêm Mới</a>
            <?php
            }
            ?>
            <table id="tblGoalPosition" class="table-common">
                <thead>
                <tr class="text-center">
                    <th>STT</th>
                    <th class="col-md-1">Mã</th>
                    <th class="col-md-3">Mục Tiêu</th>
                    <th class="col-md-1">Loại Mục Tiêu</th>
                    <th class="col-md-1">Tỷ trọng</th>
                    <th class="col-md-1">Điểm chuẩn</th>
                    <th class="col-md-1">Điểm chuẩn KPI</th>
                    <th class="col-md-1">Kế hoạch</th>
                    <th class="col-md-1">Đơn vị tính</th>
                    <th class="col-md-1">Thực hiện</th>
                    <th class="col-md-1">Điểm thực hiện</th>
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
                foreach($arrParent as $prow){ ?>
                <tr style="background-color: #D8E4BC">
                    <td colspan="4"><?php echo $prow['pName']; ?></td>
                    <td class="order-column"><?php echo $prow['il']; ?></td>
                    <td class="text-align-right"><?php echo \Utils\commonUtils::formatFloatValue($prow['bm'], \Utils\commonUtils::NUMBER_AFTER_DOT); ?></td>
                    <td class="text-align-right"><?php echo \Utils\commonUtils::formatFloatValue($prow['cbm'], \Utils\commonUtils::NUMBER_AFTER_DOT); ?></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <?php
                    if(!$view && $accessLevel < 3){
                    ?>
                    <td></td>
                    <!--<td></td>-->
                    <?php
                    }
                    ?>
                </tr>

                <?php
                $stt = 1;
                foreach($data as $child){
                if($child->parent_id == $prow['pId']){

                if(\Utils\commonUtils::compareTwoString($child->unit_code, \Utils\commonUtils::PERCENT_CODE) == 1){
                    $numAfterDot = commonUtils::NUMBER_AFTER_DOT;
                }else{
                    $numAfterDot = commonUtils::DF_NUMBER_AFTER_DOT;
                }
                ?>
                <tr style="background-color: white">
                    <td class="order-column"><?php echo $stt++; ?></td>
                    <td><?php echo $child->goal_code; ?></td>
                    <td><?php echo $child->goal_name; ?></td>
                    <td><?php echo \Utils\commonUtils::renderGoalTypeName($child->goal_type); ?></td>
                    <td class="text-center"><?php echo $child->important_level; ?></td>
                    <td class="text-right"><?php echo \Utils\commonUtils::formatFloatValue($child->benchmark, \Utils\commonUtils::NUMBER_AFTER_DOT); ?></td>
                    <td class="text-right"><?php echo \Utils\commonUtils::formatFloatValue($child->cal_benchmark, \Utils\commonUtils::NUMBER_AFTER_DOT); ?></td>
                    <td class="text-right"><?php echo \Utils\commonUtils::formatFloatValue($child->target_value, $numAfterDot);  ?></td>
                    <td class="text-left"><?php echo $child->unit_name; ?></td>
                    <td class="text-right"><?php echo \Utils\commonUtils::formatFloatValue($child->implement, $numAfterDot); ?></td>
                    <td class="text-right"><?php echo \Utils\commonUtils::formatFloatValue($child->implement_point, \Utils\commonUtils::NUMBER_AFTER_DOT); ?></td>
                    <?php
                    if(!$view && $accessLevel < 3){
                    ?>
                    <td class="table-icon text-center">
                        <a href="{{url('updateGoalPosition').'/'.$child->id}}" role="button" title="Cập nhật">
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
                foreach($data as $child){ ?>
                <tr style="background-color: white">
                    <td class="order-column"><?php echo $stt++; ?></td>
                    <td><?php echo $child->goal_code; ?></td>
                    <td><?php echo $child->goal_name; ?></td>
                    <td><?php echo \Utils\commonUtils::renderGoalTypeName($child->goal_type); ?></td>
                    <td class="text-center"><?php echo $child->important_level; ?></td>
                    <td class="text-right"><?php echo \Utils\commonUtils::formatFloatValue($child->benchmark, \Utils\commonUtils::NUMBER_AFTER_DOT); ?></td>
                    <td class="text-right"><?php echo \Utils\commonUtils::formatFloatValue($child->cal_benchmark, \Utils\commonUtils::NUMBER_AFTER_DOT); ?></td>
                    <td class="text-right"><?php echo \Utils\commonUtils::formatFloatValue($child->target_value, \Utils\commonUtils::NUMBER_AFTER_DOT);  ?></td>
                    <td class="text-left"><?php echo $child->unit_name; ?></td>
                    <td class="text-right"><?php echo \Utils\commonUtils::formatFloatValue($child->implement, \Utils\commonUtils::NUMBER_AFTER_DOT); ?></td>
                    <td class="text-right"><?php echo \Utils\commonUtils::formatFloatValue($child->implement_point, \Utils\commonUtils::NUMBER_AFTER_DOT); ?></td>
                    <?php
                    if(!$view && $accessLevel < 3){
                    ?>
                    <td class="table-icon text-center">
                        <a href="{{url('updateGoalPosition').'/'.$child->id}}" role="button" title="Cập nhật">
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
            <!-- fixed header for scroll -->
            <table id="header-fixed"></table>
        </div>
        <script>
            fixHeader($("#tblGoalPosition"), $("#tblGoalPosition > thead"), $("#header-fixed"));
            $( document ).ready(function() {

                var valArea = $('#cboArea').val();
                var valPosition = $('#cboPosition').val();
                var valCompany = $('#cboCompany').val();
                var valGoalType = $('#cboGoalType').val();
                var valGoalId   = $('#cboGoal').val();
                var valMonth    = $('#cboMonth').val();

                if(valArea != 0 && valCompany != 0 && valPosition==0 ){
                    $('#NoteExport').removeClass('hidden');
                }

                /*--------------------------------------------------------------------------------------------------------*/
                var accessLevel = "<?php echo Session::get('saccess_level');?>";
                if(accessLevel < 3 && accessLevel > 4){
                    document.getElementById('addGoalPosition').style.display = 'none';
                }

                if(
                        valGoalType     == -1
                        || valGoalId    == 0
                        || valMonth     == 0
                        || valPosition  == 0
                        || valCompany   == 0
                        || valArea      == 0
                ){
                    $('#addGoalPosition').attr('disabled','disabled');
                }else{
                    $('#addGoalPosition').removeAttr('disabled');
                }

                /*--------------------------------------------------------------------------------------------------------*/

            });
        </script>
    </div>
@stop