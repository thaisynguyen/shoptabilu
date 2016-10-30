@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.IMPORT'))
@section('section')
    @include('alerts.errors')
    @include('alerts.success')
    @include('alerts.errorsImport')
    <?php
    use Utils\commonUtils;
    $listType   = CommonUtils::arrMethodImport();
    $arrKey     = commonUtils::setPermissionImport(Session::get('saccess_level'));
    ?>
    <form action="{{action('ImportExcelController@postImport')}}" method="POST" class="form-horizontal margin-left-20"
          enctype="multipart/form-data">
        <input type="hidden" name="_token" value="<?= csrf_token();?>"/>

        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Loại
                import: </label>

            <div class="col-sm-3">
                <select class="form-control" id="selTypeImport" name="selTypeImport" onchange="changeLink();">
                    <?php
                    if(Session::has('type')){
                        foreach ($listType as $key => $value) {
                            if(Session::get('type') == $value['id'] && (in_array($key, $arrKey) || Session::get('sid') == 0)){?>
                                <option value="<?php echo $value['id'];?>" selected><?php echo $value['name'];?></option>
                            <?php } else if(in_array($key, $arrKey) || Session::get('sid') == 0){ ?>
                                <option value="<?php echo $value['id'];?>"><?php echo $value['name'];?></option>
                            <?php }
                        }
                    } else {
                        foreach ($listType as $key => $value) {
                            if(in_array($key, $arrKey) || Session::get('sid') == 0){
                    ?>
                                <option a="<?php echo Session::get('saccess_level');?>" attr="<?php echo $key;?>" value="<?php echo $value['id'];?>"><?php echo $value['name'];?></option>
                    <?php
                            }
                       }
                    }

                    ?>
                </select>
            </div>
            <div class="col-sm-7"></div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Chọn
                file: </label>
            <div class="col-sm-3" >
                <div class="bootstrap-filestyle input-group" >
                    <input type="text" id="uploadFile" placeholder="Chọn file excel" class="form-control margin_top_2" disabled="" >
                    <span class="group-span-filestyle input-group-btn btn-file glyphicon glyphicon-folder-open" tabindex="0"  >
                        <label for="filestyle-0" class="btn btn-default">
                            <span class="glyphicon glyphicon-folder-open " >
                                <input type="file" id="uploadInput" name ='uploadFile' accept="application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                            </span>
                        </label>
                    </span>
                </div>
            </div>
            <div class="col-sm-7"></div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Dòng bắt
                đầu: </label>

            <div class="col-sm-3">
                <input id="startRow" name="startRow" type="number" class="form-control col-xs-10 col-sm-5" value="4"/>
            </div>
            <div class="col-sm-7">

            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Loại
                sheet: </label>
            <input name="typeImport" id="typeImport" value="0" hidden/>
            <div class="col-sm-2">
                <label class="radio-inline">
                    <input type="radio" name="radio" checked="checked" id="one-sheet"
                           onchange="changeForRadioOneSheet();" class="marg-top-radio">Một sheet
                    <a href="public/excelTemplate/sampleImportGoal.xlsx" style="color: red;" id="linkGoal">Sample</a>
                    <a href="<?= URL::to('exportSamplePriorityCompany');?>" style="color: red;"
                       id="linkPriorityCompany">Sample</a>
                    <a href="<?= URL::to('exportSamplePriorityPosition');?>" style="color: red;"
                       id="linkPriorityposition">Sample</a>
                    <a href="public/excelTemplate/sampleImportUser.xlsx" style="color: red;" id="linkUser">Sample</a>
                    <a href="<?= URL::to('exportSampleGoalArea');?>" style="color: red;" id="linkGoalArea">Sample</a>
                    <a href="<?= URL::to('exportSampleGoalEmployee');?>" style="color: red;"
                       id="linkGoalEmployee">Sample</a>
                    <a href="<?= URL::to('exportSampleGoalPosition');?>" style="color: red;"
                       id="linkGoalPosition">Sample</a>
                    <a href="<?= URL::to('exportSamplePerformEmployee');?>" style="color: red;"
                       id="linkPerformForEmployee">Sample</a>
                    <a href="public/excelTemplate/sampleImportCTV.xlsx" style="color: red;"
                       id="linkPerformForPosition">Sample</a>
                    <a href="<?= URL::to('exportSamplePriorityArea');?>" style="color: red;"
                       id="linkPriorityArea">Sample</a>
                    <a href="<?= URL::to('exportSamplePriorityCorporation');?>" style="color: red;"
                       id="linkPriorityCorporation">Sample</a>
                </label>
            </div>
            <div class="col-sm-8 marg-bottom-radio" style="margin-left: -15px;">
                <label class="radio-inline">
                    <input type="radio" name="radio" id="one-sheets" onchange="changeForRadioManySheet();"
                           class="marg-top-radio">Nhiều sheet
                </label>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> STT: </label>

            <div class="col-sm-3">
                <input type="text" class="form-control txt-30" id="textbox-index-read" readonly="readonly">
                <input type="text" class="form-control txt-30" id="textbox-index" placeholder="1,2,3,..." name="arrSheetIndex">
            </div>
            <div class="col-sm-7">

            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15" for="form-field-1"> &nbsp; </label>

            <div class="col-sm-10">
                <button type="submit" data-loading-text="Đang tải..." class="btn btn-primary btn-save btnImport" id="btnImport"> &nbsp;<i
                            class="fa fa-floppy-o"></i> &nbsp;Tải lên&nbsp;&nbsp;</button>
                <a href="<?= URL::to('importGoal');?>">
                    <button type="button" class="btn btn-primary btn-cancel"><i class="fa fa-reply"></i> Bỏ qua</button>
                </a>
            </div>
        </div>
    </form>

    <!------------------------------------------------------------------------------------------------------->
    <div class="modal fade gray-darker " id="popupWarningImport" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" >
        <div class="modal-dialog" role="document">
            <div class="modal-content ">
                <div class="modal-body">
                    <a hidden type="button" data-dismiss="modal" style=" margin-bottom: 5%;margin-right: 1%; z-index: 20; cursor: pointer;">
                        <span class="glyphicon glyphicon-remove pull-right white"  aria-hidden="true" ></span>
                    </a>
                    <p/>
                    <div class="row" id="contentImport" ></div>
                    <div class="row" style="padding-top: 3px; padding-bottom: 6px;" id="content-warning-area">
                        <div class=" col-lg-11 font-size-base" style=" margin-left: 5%;padding-top: 7px;"><strong style="padding-left: 5px; ">Đồng ý ghi đè?</strong></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-primary" data-dismiss="modal" id="btnOverrideImport">Đồng ý</button>
                    <button type="button" class="btn btn-default btn-primary" data-dismiss="modal" id="btnExitImport">Thoát</button>
                </div>
            </div>
        </div>
    </div>
    <!------------------------------------------------------------------------------------------------------->
    <div id="progressbar" style="display: none;"></div>

    <script type="text/javascript">
        $( "#progressbar" ).progressbar({
            value: false
        });
        var isError = '{{Session::has('message-errors')}}';
        var isErrorImport = '{{Session::has('isset-codes-errors')}}';
        var isSuccess = '{{Session::has('message-success')}}';

        if(isError == '1' || isErrorImport == '1' || isSuccess == '1'){
            $( "#progressbar").attr('style', 'display: none');
        }

        $('#btnImport').click(function(){
            $( "#progressbar").attr('style', '');
        })

        $('#btnOverrideImport').click(function(){
            $( "#progressbar").attr('style', '');
        })



        function changeForRadioManySheet() {
            var isCheck = document.getElementById("one-sheets").checked;
            if (isCheck) {
                $('#textbox-index-read').hide();
                $('#textbox-index').show();

                /*
                 * Set type when choose import one or multi sheet
                 * */
                $('#typeImport').val("1");

            } else {
                $('#typeImport').val("0");
                $('#textbox-index-read').show();
                $('#textbox-index').hide();
            }
        }

        function changeForRadioOneSheet() {
            var isCheck = document.getElementById("one-sheet").checked;
            if (isCheck) {
                $('#typeImport').val("0");
                $('#textbox-index-read').show();
                $('#textbox-index').hide();
            } else {
                $('#typeImport').val("1");
                $('#textbox-index-read').hide();
                $('#textbox-index').show();
            }
        }

        function changeLink() {
            var type = $('#selTypeImport').val();
            //số thứ tự của các id trong mảng trùng với id của phần tử đó
            var arrId = new Array();
            arrId[1] = "#linkGoal";
            arrId[2] = "#linkPriorityCompany";
            arrId[3] = "#linkPriorityposition";
            arrId[4] = "#linkUser";
            arrId[5] = "#linkGoalArea";
            arrId[6] = "#linkGoalEmployee";
            arrId[7] = "#linkPriorityCorporation";
            arrId[8] = "#linkPerformForEmployee";
            arrId[9] = "#linkGoalPosition";
            arrId[10] = "#linkPriorityArea";
            arrId[11] = "#linkPerformForPosition";

            for (var i = 1; i <= arrId.length; i++) {
                $(arrId[i]).hide();
            }

            for (var i = 1; i <= arrId.length; i++) {
                if (i == type) {
                    $(arrId[i]).show();
                }
            }
        }

        var value = 0;
        // console.log(value);
        //
        $(document).ready(function () {
            changeForRadioManySheet();
            $('#linkGoal').hide();
            $('#linkPriorityCompany').hide();
            $('#linkPriorityposition').hide();
            $('#linkUser').hide();
            $('#linkGoalArea').hide();
            $('#linkGoalEmployee').hide();
            $('#linkPriorityCorporation').hide();
            $('#linkPerformForEmployee').hide();
            $('#linkGoalPosition').hide();
            $('#linkPriorityArea').hide();
            $('#linkPerformForPosition').hide();

            changeLink();

            /*
             * Open pop-up when exist data in database
             * */
            var existCompanyGoal = 0;
            var existAreaGoal = 0;
            var existPositionGoal = 0;

            var chooseImport = '';
            var html = '';
            var htmlWarning = '';
            var fDayCheck = '';
            var dayCheck = '';
            var pathFile = '';
            var startRow = '';
            var curType = -1;

            curType = "<?php echo Session::get('curType');?>";

            $('#btnExitImport').on('click', function(e){
                $('#popupWarningImport').modal('hide');
                var path = '<?php echo action('ImportExcelController@clearSession');?>';
                window.location.href = path;
            });

            /***********************************************************************************************************
             * Compare Current type import , exist data in database to show popup waring, and confirm Override data
             **********************************************************************************************************/
            var strIssetDataShow = "";
            switch (curType) {
                case '1':

                    break;
                case '2': /*Import Tỷ trọng Phòng/Đài/MBF HCM*/

                    strIssetDataShow = "<?php echo Session::get('strIssetDataShow');?>";
                    fDayCheck = "<?php echo Session::get('fDayCheck');?>";
                    dayCheck = "<?php echo Session::get('dayCheck');?>";
                    // Warning for function importCompanyGoal
                    if(strIssetDataShow != ""){
                        $('#popupWarningImport').modal('show');
                        $('#contentImport').html("");
                        html = '<div class=" col-lg-11 font-size-base" style=" margin-left: 5%;padding-top: 7px;"><span style="padding-left: 5px; ">'+strIssetDataShow+'</span></div>';
                        $('#contentImport').append(html);
                    }else {
                        $('#popupWarningImport').modal('hide');
                        var path = '<?php echo action('ImportExcelController@importPriorityCompany');?>';
                        window.location.href = path;
                    }

                    $('#btnExitImport').on('click', function(e){
                        $('#popupWarningImport').modal('hide');
                        var path = '<?php echo action('ImportExcelController@clearSession');?>';
                        window.location.href = path;
                    });

                    $('#btnOverrideImport').on('click', function(e){
                        $('#popupWarningImport').modal('hide');
                        var path = '<?php echo action('ImportExcelController@importPriorityCompany');?>';
                        window.location.href = path;
                    });

                    break;
                case '3':/*Import Tỷ trọng Chức danh*/


                    chooseImport = "<?php echo Session::get('chooseImport');?>";
                    if(chooseImport == '1'){
                        var strIssetDataShow = "<?php echo Session::get('strIssetDataShow');?>";

                        if(strIssetDataShow != ""){
                            $('#popupWarningImport').modal('show');
                            $('#contentImport').html("");
                            html = '<div class=" col-lg-11 font-size-base" style=" margin-left: 5%;padding-top: 7px;"><span style="padding-left: 5px; ">'+strIssetDataShow+'</span></div>';
                            $('#contentImport').append(html);
                        } else {
                            var path = '<?php echo action('ImportExcelController@importMultiPriorityPosition');?>';
                            window.location.href = path;
                        }

                        $('#btnExitImport').on('click', function(e){
                            $('#popupWarningImport').modal('hide');
                            var path = '<?php echo action('ImportExcelController@clearSession');?>';
                            window.location.href = path;
                        });

                        $('#btnOverrideImport').on('click', function(e){
                            var path = '<?php echo action('ImportExcelController@importMultiPriorityPosition');?>';
                            window.location.href = path;
                        });
                    }

                    break;
                case '4':/*Import Nhân viên*/

                    break;
                case '5':/*Import Kế hoạch cho Tổ/Quận/Huyện*/

                    strIssetDataShow = "<?php echo Session::get('strIssetDataShow');?>";
                    if(strIssetDataShow != ""){
                        $('#popupWarningImport').modal('show');
                        $('#contentImport').html("");
                        html = '<div class=" col-lg-11 font-size-base" style=" margin-left: 5%;padding-top: 7px;"><span style="padding-left: 5px; ">'+strIssetDataShow+'</span></div>';
                        $('#contentImport').append(html);
                    } else {
                        $('#popupWarningImport').modal('hide');
                        var path = '<?php echo action('ImportExcelController@importMultiGoalArea');?>';
                        window.location.href = path;
                    }
                    $('#btnExitImport').on('click', function(e){
                        $('#popupWarningImport').modal('hide');
                        var path = '<?php echo action('ImportExcelController@clearSession');?>';
                        window.location.href = path;
                    });
                    $('#btnOverrideImport').on('click', function(e){
                        $('#popupWarningImport').modal('hide');
                        var path = '<?php echo action('ImportExcelController@importMultiGoalArea');?>';
                        window.location.href = path;
                    });

                    break;
                case '6':/*Import Kế hoạch cho nhân viên*/

                    chooseImport = "<?php echo Session::get('chooseImport');?>";
                    if(chooseImport == '1'){
                        var strIssetDataShow = "<?php echo Session::get('strIssetDataShow');?>";

                        if(strIssetDataShow != ""){
                            $('#popupWarningImport').modal('show');
                            $('#contentImport').html("");
                            html = '<div class=" col-lg-11 font-size-base" style=" margin-left: 5%;padding-top: 7px;"><span style="padding-left: 5px; ">'+strIssetDataShow+'</span></div>';
                            $('#contentImport').append(html);
                        } else {
                            var path = '<?php echo action('ImportExcelController@importMultiGoalEmployee');?>';
                            window.location.href = path;
                        }

                        $('#btnExitImport').on('click', function(e){
                            $('#popupWarningImport').modal('hide');
                            var path = '<?php echo action('ImportExcelController@clearSession');?>';
                            window.location.href = path;
                        });

                        $('#btnOverrideImport').on('click', function(e){
                            var path = '<?php echo action('ImportExcelController@importMultiGoalEmployee');?>';
                            window.location.href = path;
                        });
                    }

                    break;
                case '7':/*Import Tỷ trọng Công ty*/

                    strIssetDataShow = "<?php echo Session::get('strIssetDataShow');?>";
                    // Warning for function importCompanyGoal
                    if(strIssetDataShow != ""){
                        $('#popupWarningImport').modal('show');
                        $('#contentImport').html("");
                        html = '<div class=" col-lg-11 font-size-base" style=" margin-left: 5%;padding-top: 7px;"><span style="padding-left: 5px; ">'+strIssetDataShow+'</span></div>';
                        $('#contentImport').append(html);
                    }else {
                        $('#popupWarningImport').modal('hide');
                        var path = '<?php echo action('ImportExcelController@importPriorityCorporation');?>';
                        window.location.href = path;
                    }

                    $('#btnExitImport').on('click', function(e){
                        $('#popupWarningImport').modal('hide');
                        var path = '<?php echo action('ImportExcelController@clearSession');?>';
                        window.location.href = path;
                    });

                    $('#btnOverrideImport').on('click', function(e){
                        $('#popupWarningImport').modal('hide');
                        var path = '<?php echo action('ImportExcelController@importPriorityCorporation');?>';
                        window.location.href = path;
                    });

                    break;
                case '8':/*Import Thực hiện cho nhân viên*/

                    chooseImport = "<?php echo Session::get('chooseImport');?>";
                    if(chooseImport == '1'){
                        var strIssetDataShow = "<?php echo Session::get('strIssetDataShow');?>";

                        if(strIssetDataShow != ""){
                            $('#popupWarningImport').modal('show');
                            $('#contentImport').html("");
                            html = '<div class=" col-lg-11 font-size-base" style=" margin-left: 5%;padding-top: 7px;"><span style="padding-left: 5px; ">'+strIssetDataShow+'</span></div>';
                            $('#contentImport').append(html);
                        } else {
                            var path = '<?php echo action('ImportExcelController@importMultiPerformEmployee');?>';
                            window.location.href = path;
                        }

                        $('#btnExitImport').on('click', function(e){
                            $('#popupWarningImport').modal('hide');
                            var path = '<?php echo action('ImportExcelController@clearSession');?>';
                            window.location.href = path;
                        });

                        $('#btnOverrideImport').on('click', function(e){
                            var path = '<?php echo action('ImportExcelController@importMultiPerformEmployee');?>';
                            window.location.href = path;
                        });
                    }

                    break;
                case '9':/*Import Kế hoạch cho chức danh*/

                    chooseImport = "<?php echo Session::get('chooseImport');?>";
                    if(chooseImport == '1'){
                        var strIssetDataShow = "<?php echo Session::get('strIssetDataShow');?>";

                        if(strIssetDataShow != ""){
                            $('#popupWarningImport').modal('show');
                            $('#contentImport').html("");
                            html = '<div class=" col-lg-11 font-size-base" style=" margin-left: 5%;padding-top: 7px;"><span style="padding-left: 5px; ">'+strIssetDataShow+'</span></div>';
                            $('#contentImport').append(html);
                        } else {
                            var path = '<?php echo action('ImportExcelController@importMultiGoalPosition');?>';
                            window.location.href = path;
                        }

                        $('#btnExitImport').on('click', function(e){
                            $('#popupWarningImport').modal('hide');
                            var path = '<?php echo action('ImportExcelController@clearSession');?>';
                            window.location.href = path;
                        });

                        $('#btnOverrideImport').on('click', function(e){
                            var path = '<?php echo action('ImportExcelController@importMultiGoalPosition');?>';
                            window.location.href = path;
                        });
                    }

                    break;
                case '10':/*Tỷ trọng cho Tổ/Quận/Huyện*/

                    chooseImport = "<?php echo Session::get('chooseImport');?>";
                        //console.log(chooseImport);
                    if(chooseImport == '1'){
                        var strIssetDataShow = "<?php echo Session::get('strIssetDataShow');?>";

                        if(strIssetDataShow != ""){
                            $('#popupWarningImport').modal('show');
                            $('#contentImport').html("");
                            html = '<div class=" col-lg-11 font-size-base" style=" margin-left: 5%;padding-top: 7px;"><span style="padding-left: 5px; ">'+strIssetDataShow+'</span></div>';
                            $('#contentImport').append(html);
                        }else{
                            var path = '<?php echo action('ImportExcelController@importMultiPriorityArea');?>';
                            window.location.href = path;
                        }
                        $('#btnOverrideImport').on('click', function(e){
                            var path = '<?php echo action('ImportExcelController@importMultiPriorityArea');?>';
                            window.location.href = path;
                        });
                    }

                    break;
                case '11':/*Thực hiện cho chức danh*/

                    chooseImport = "<?php echo Session::get('chooseImport');?>";
                    if(chooseImport == '1'){
                        var strIssetDataShow = "<?php echo Session::get('strIssetDataShow');?>";

                        if(strIssetDataShow != ""){
                            $('#popupWarningImport').modal('show');
                            $('#contentImport').html("");
                            html = '<div class=" col-lg-11 font-size-base" style=" margin-left: 5%;padding-top: 7px;"><span style="padding-left: 5px; ">'+strIssetDataShow+'</span></div>';
                            $('#contentImport').append(html);
                        } else {
                            var path = '<?php echo action('ImportExcelController@importMultiPerformPosition');?>';
                            window.location.href = path;
                        }

                        $('#btnExitImport').on('click', function(e){
                            $('#popupWarningImport').modal('hide');
                            var path = '<?php echo action('ImportExcelController@clearSession');?>';
                            window.location.href = path;
                        });

                        $('#btnOverrideImport').on('click', function(e){
                            var path = '<?php echo action('ImportExcelController@importMultiPerformPosition');?>';
                            window.location.href = path;
                        });
                    }

                    break;
            }
            /**********************************************************************************************************/

        });

        document.getElementById("uploadInput").onchange = function () {
            document.getElementById("uploadFile").value = this.value;
        };

        $(function() {
            $(".btnImport").click(function(){
                $(this).button('loading').delay(1000).queue(function() {
                    // $(this).button('reset');
                });

            });
        });

        $('#uploadInput').change( function()
        {
            $('#btnImport').prop("disabled", false);
        });
        if(value == 0){
            $('#btnImport').prop("disabled", true);
        }

        $(document).ready(function () {
            var path = $('#uploadInput').val();
            if(path == '' || path == null){
                $('#btnImport').prop("disabled", true);
            } else {
                $('#btnImport').prop("disabled", false);
            }
        });
    </script>
@stop