@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.LOCK_DATA'))
@section('section')
    <?php
    use Utils\commonUtils;
    $curpageData =  $data->currentPage();
    $curpageDate =  $date->currentPage();
    $accessLevel = Session::get('saccess_level');
    $isView = Session::get('sis_view');
    $sDataUser = Session::get('sDataUser');

            $hiddenBlockLockByDay = ($accessLevel > 1 && $sDataUser->id != 0) ? 'hidden' : '';
    ?>

    <div id="wrapper" xmlns="http://www.w3.org/1999/html">
        <div class="row">
            <div class="col-md-12 col-xs-12">
                <ul id="myTab" class="nav nav-tabs ">
                    <li class="active"><a href="#client" data-toggle="tab">Khóa Theo Tháng</a></li>
                    <li class="{{ $hiddenBlockLockByDay }}"><a href="#lockByDay" data-toggle="tab">Khóa Theo Ngày</a></li>
                </ul>
                <div id="myTabContent" class="tab-content">
                    <div class="tab-pane fade in active" id="client">
                        <div class="row margin-form top-50" >
                            <form action="{{action('CategoriesController@searchLock')}}" method="POST">
                                <input type="hidden" name="_token" value="<?= csrf_token();?>"/>
                                <div class="col-sm-12 col-xs-12">
                                    <div class="col-sm-1 col-xs-12 padding-top-frm margin-popup-add">Tháng:</div>
                                    <div class="col-sm-2 col-xs-12 margin-checkbox">
                                        <select class="form-control" name="month" id="slAction">
                                              <?php if(isset($month)){
                                                  for($i = 0; $i < count($months); $i++) {
                                                      if($months[$i]->ofmonth == $month){?>
                                                          <option value="<?php echo $months[$i]->ofmonth; ?>" selected><?php echo $months[$i]->ofmonth; ?></option>
                                                      <?php } else {?>
                                                          <option value="<?php echo $months[$i]->ofmonth; ?>" ><?php echo $months[$i]->ofmonth; ?></option>
                                                      <?php }
                                                  }
                                              } else {
                                                  for($i = 0; $i < count($months); $i++){?>
                                                       <option value="<?php echo $months[$i]->ofmonth; ?>"><?php echo $months[$i]->ofmonth; ?></option>
                                                  <?php }
                                              }?>
                                        </select>
                                    </div>
                                    <div class="col-sm-1 col-xs-12 "></div>
                                    <div class="col-sm-1 col-xs-12 padding-top-frm margin-popup-add">Năm:</div>
                                    <div class="col-sm-2 col-xs-12 margin-checkbox">
                                        <select class="form-control " name="year" id="slAction">
                                             <?php if(isset($year)){
                                                  for($i = 0; $i < count($years); $i++) {
                                                      if($years[$i]->ofyear == $year){?>
                                                           <option value="<?php echo $years[$i]->ofyear; ?>" selected><?php echo $years[$i]->ofyear; ?></option>
                                                      <?php } else {?>
                                                            <option value="<?php echo $years[$i]->ofyear; ?>" ><?php echo $years[$i]->ofyear; ?></option>
                                                      <?php }
                                                  }
                                             } else {
                                                  for($i = 0; $i < count($years); $i++){?>
                                                      <option value="<?php echo $years[$i]->ofyear; ?>"><?php echo $years[$i]->ofyear; ?></option>
                                                   <?php }
                                             }?>
                                        </select>
                                    </div>
                                    <div class="col-sm-5 col-xs-12">
                                        <button type="submit"  class="btn btn-primary btn-save" style="margin-left: 55px;"> &nbsp;<i class="glyphicon glyphicon-search"></i>Tìm</button>
                                    </div>
                                </div>
                            </form>
                            <div class="col-md-12 col-xs-12">
                                <div class="col-sm-12 col-xs-12">
                                <span class="pull-right pading-right-22">
                                    <?php
                                    echo $data->setPath(action('CategoriesController@lockData'))->render();
                                    ?>
                                </span>
                                </div>

                                <div class="col-sm-12 col-xs-12">
                                    <div class="col-sm-12 col-xs-12" id="message" style="margin-top: 20px;margin-bottom: 20px;">
                                    </div>
                                    <table class="table-common">
                                        <thead>
                                        <tr>
                                            <th class="col-sm-1 col-xs-1 order-column">STT</th>
                                            <th class="col-sm-2 col-xs-2 order-column">Thời gian khóa</th>

                                            <?php if($accessLevel < 3 && $isView == 0) {?>
                                                <th class="col-sm-3 order-column" colspan="2">Ngày hết hạn tỷ trọng</th>
                                                <th class="col-sm-3 col-xs-3 order-column" colspan="2">Ngày hết hạn kế hoạch</th>
                                                <th class="col-sm-3 col-xs-3 order-column" colspan="2">Ngày hết hạn thực hiện</th>
                                            <?php } ?>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $stt = (($curpageData-1) * CommonUtils::ITEM_PER_PAGE_DEFAULT) + 1;
                                        foreach($data as $row){
                                        ?>
                                        <tr>
                                            <td class="order-column"><?php  echo $stt; $stt++; ?></td>

                                            <td class="order-column">
                                                {{$row->ofmonth.'/'.$row->ofyear}}
                                                <input hidden="hidden" type="text" id="{{'dateLock'.$row->id}}" value="{{$row->ofmonth.'/'.$row->ofyear}}">
                                            </td>

                                            <?php if($accessLevel < 3 && $isView == 0) {?>
                                            <td class="order-column">
                                                <div class="form-group margin-left-30">
                                                    <?php
                                                         if($row->lock == 0){?>
                                                             <div class='input-group date fdatepicker datepicker'>
                                                                  <input type='text' style="width: auto" id="{{'expireDateIL'.$row->id}}" class="form-control order-column" value="{{\Utils\commonUtils::formatDate($row->expire_date_il)}}"/>
                                                    <?php } else { ?>
                                                             <div class='input-group date fdatepicker'>
                                                                  <input type='text' disabled style="width: auto" id="{{'expireDateIL'.$row->id}}" class="form-control order-column" value="{{\Utils\commonUtils::formatDate($row->expire_date_il)}}"/>
                                                    <?php } ?>
                                                                 <span class="input-group-addon" hidden="hidden">
                                                                    <span class="glyphicon glyphicon-calendar"></span>
                                                                 </span>
                                                             </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="order-column">
                                                <?php
                                                    if($row->lock == 0){?>
                                                        <input id="{{'il'.$row->id}}" type="checkbox" status="{{$row->lock}}" rowId="{{$row->id}}" class="ckLockIL" name="is_ldap" >
                                                    <?php } else {?>
                                                        <input id="{{'il'.$row->id}}" type="checkbox" status="{{$row->lock}}" rowId="{{$row->id}}" class="ckLockIL" name="is_ldap" checked>
                                                    <?php }
                                                ?>
                                            </td>

                                            <td class="order-column">
                                                <div class="form-group margin-left-30">
                                                    <?php
                                                         if($row->target_lock == 0){?>
                                                             <div class='input-group date fdatepicker datepicker'>
                                                                  <input type='text' style="width: auto" id="{{'expireDateTarget'.$row->id}}" class="form-control order-column" value="{{\Utils\commonUtils::formatDate($row->expire_date_target)}}"/>
                                                         <?php } else { ?>
                                                             <div class='input-group date fdatepicker'>
                                                                  <input type='text' disabled style="width: auto" id="{{'expireDateTarget'.$row->id}}" class="form-control order-column" value="{{\Utils\commonUtils::formatDate($row->expire_date_target)}}"/>
                                                         <?php } ?>
                                                                 <span class="input-group-addon" hidden="hidden">
                                                                    <span class="glyphicon glyphicon-calendar"></span>
                                                                 </span>
                                                             </div>
                                                         </div>
                                                </div>
                                            </td>

                                            <td class="order-column">
                                                <?php
                                                    if($row->target_lock == 0){?>
                                                        <input id="{{'target'.$row->id}}" type="checkbox" status="{{$row->target_lock}}" rowId="{{$row->id}}" class="ckLockTarget" name="is_ldap" >
                                                    <?php } else {?>
                                                        <input id="{{'target'.$row->id}}" type="checkbox" status="{{$row->target_lock}}"rowId="{{$row->id}}" class="ckLockTarget" name="is_ldap" checked>
                                                    <?php }
                                                ?>
                                            </td>

                                            <td class="order-column">
                                                <div class="form-group margin-left-30">
                                                    <?php
                                                         if($row->perform_lock == 0){?>
                                                             <div class='input-group date fdatepicker datepicker'>
                                                                  <input type='text' style="width: auto" id="{{'expireDatePerform'.$row->id}}" class="form-control order-column" value="{{\Utils\commonUtils::formatDate($row->expire_date_perform)}}"/>
                                                         <?php } else { ?>
                                                             <div class='input-group date fdatepicker' id="{{'appliedDate'.$row->id}}" >
                                                                  <input type='text' disabled style="width: auto" id="{{'expireDatePerform'.$row->id}}" class="form-control order-column" value="{{\Utils\commonUtils::formatDate($row->expire_date_perform)}}"/>
                                                         <?php } ?>
                                                                  <span class="input-group-addon" hidden="hidden">
                                                                      <span class="glyphicon glyphicon-calendar"></span>
                                                                  </span>
                                                              </div>
                                                         </div>
                                                </div>
                                            </td>

                                            <td class="order-column">
                                                <?php
                                                    if($row->perform_lock == 0){?>
                                                        <input id="{{'perform'.$row->id}}" type="checkbox" status="{{$row->perform_lock}}" rowId="{{$row->id}}"class="ckLockPerform" name="is_ldap" >
                                                    <?php } else {?>
                                                        <input id="{{'perform'.$row->id}}" type="checkbox" status="{{$row->perform_lock}}" rowId="{{$row->id}}" class="ckLockPerform" name="is_ldap" checked>
                                                    <?php }
                                                ?>
                                             </td>

                                            <?php } ?>
                                        </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-sm-12 col-xs-12">
                            <span class="pull-right pading-right-22">
                                <?php
                                    echo $data->setPath(action('CategoriesController@lockData'))->render();
                                ?>
                            </span>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade"  id="lockByDay">
                        <div class="row margin-form top-50" >
                            <form action={{action('CategoriesController@searchLogByApplyDate')}} method="POST">
                                <input type="hidden" name="_token" value="<?= csrf_token();?>"/>
                                <div class="col-sm-12 col-xs-12">
                                    <div class="col-sm-2 col-xs-12 padding-top-frm margin-popup-add">Ngày áp dụng</div>
                                    <div class="col-sm-3 col-xs-12 form-group" style="margin-left: -90px;">
                                        <div class="form-group" >
                                            <div class='input-group date fdatepicker' id='appliedDate' >
                                                <?php if(isset($applyDate)){?>
                                                    <input type='text' name="applyDate" id="txtDateTo" class="form-control order-column" value="<?php echo $applyDate ?>" />
                                                <?php } else { ?>
                                                    <input type='text' name="applyDate" id="txtDateTo" class="form-control order-column" />
                                                <?php }?>
                                                    <span class="input-group-addon">
                                                        <span class="glyphicon glyphicon-calendar"></span>
                                                    </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xs-12">
                                        <button type="submit" class="btn btn-primary btn-save" style="margin-left: 55px;"> &nbsp;<i class="glyphicon glyphicon-search"></i>Tìm</button>
                                    </div>
                                </div>
                            </form>
                            <div class="col-md-12 col-xs-12">
                                <div class="col-sm-12 col-xs-12">
                                <span class="pull-right pading-right-22">
                                    <?php
                                        echo $date->setPath(action('CategoriesController@lockData'))->render();
                                    ?>
                                </span>
                                </div>
                                <div class="col-sm-12 col-xs-12">
                                    <table class="table-common">
                                        <thead>
                                        <tr>
                                            <th class="col-sm-1 col-xs-1 order-column">STT</th>
                                            <th class="col-sm-7 col-xs-7 order-column">Ngày Áp Dụng</th>
                                            <?php if($accessLevel < 3 && $isView == 0) {?>
                                            <th class="col-sm-4 col-xs-4 order-column">Khóa</th>
                                            <?php }?>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                            $stt = (($curpageDate-1) * CommonUtils::ITEM_PER_PAGE_DEFAULT) + 1;
                                            foreach($date as $row){
                                                $dayFormat = $row->apply_date;
                                                $day = substr($dayFormat, 0, 4);
                                                $month = substr($dayFormat, 5, 2);
                                                $year = substr($dayFormat, 8, 2);
                                                $dayFormat = $year.'-'.$month.'-'.$day;
                                        ?>
                                        <tr>
                                            <td class="order-column"><?php  echo $stt; $stt++?></td>
                                            <td class="order-column"><?php echo $dayFormat?></td>
                                            <?php if($accessLevel < 3 && $isView == 0) {?>
                                            <td class="order-column">
                                                <?php
                                                    if($row->lock == 0){?>
                                                    <input statusLock="{{0}}" dateLock="{{$row->apply_date}}" type="checkbox" class="ckLockApplyDate" >
                                                    <?php } else if($row->lock == 1){?>
                                                    <input statusLock="{{1}}" dateLock="{{$row->apply_date}}" type="checkbox" class="ckLockApplyDate" checked>
                                                    <?php }
                                                ?>
                                            </td>
                                            <?php }?>
                                        </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-sm-12">
                            <span class="pull-right pading-right-22">
                                <?php
                                echo $date->setPath(action('CategoriesController@lockData'))->render();
                                ?>
                            </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="application/javascript">

        // date định dạng $date: dd/mm/yyyy, $dateRoot: mm/yyyy
        function checkEqualMonthYear($dateRoot, $date){
            var m = parseInt($dateRoot.split('/')[0]);
            var y = parseInt($dateRoot.split('/')[1]);

            var month = parseInt($date.split('/')[1]);
            var year = parseInt($date.split('/')[2]);

            var result = 1;
            if(m == month && y == year){
                result = 0;
            }
            return result;
        }

        function checkMonthYearPerform($perform, $target){
            var d = parseInt($perform.split('/')[0]);
            var m = parseInt($perform.split('/')[1]);
            var y = parseInt($perform.split('/')[2]);

            var day = parseInt($target.split('/')[0]);
            var month = parseInt($target.split('/')[1]);
            var year = parseInt($target.split('/')[2]);

            var result = 0;
            if(((m == month && d > day) || m == (month+1)) && y == year){
                 result = 1;
            }
            return result;
        }

        $(".ckLockIL").click(function(){
            delChild('message');
            var rowId = $(this).attr('rowId');
            var status = $(this).attr('status');

            var isCheckLockIL = document.getElementById("il"+rowId).checked;
            var isCheckLockTV = document.getElementById("target"+rowId).checked;
            var isCheckLockP = document.getElementById("perform"+rowId).checked;

            var targetDate = $('#expireDateTarget'+rowId).val();
            var performDate = $('#expireDatePerform'+rowId).val();
            var ilDate = $('#expireDateIL'+rowId).val();

            var dateLock = $('#dateLock'+rowId).val();

            if(isCheckLockIL){
                // cùng năm, tháng với ngày khóa
                //nhỏ hơn ngày hết hạn thực hiện
                if(ilDate == '00/00/0000'){
                    var html = '<p style="color: red">Vui lòng chọn thời gian hết hạn tỷ trọng.</p>';
                    delChild('message');
                    $('#message').append(html);
                    document.getElementById("il"+rowId).checked = false;
                } else if(checkEqualMonthYear(dateLock, ilDate)){
                    var html = '<p style="color: red">Tháng và năm của thời gian hết hạn tỷ trọng phải bằng thời gian khóa. Vui lòng chọn lại.</p>';
                    delChild('message');
                    $('#message').append(html);
                    document.getElementById("il"+rowId).checked = false;
                } else {
                    $('#expireDateIL'+rowId).prop("disabled", true);
                    // đổi trường lock, expire_date, type_lock, perform_lock, target_lock trong db
                    $.get("changeLock",{rowId: rowId, status : status, columnDate: 'expire_date_il', columnLock: 'lock', targetDate: ilDate, logText: 'Ngày hết hạn tỷ trọng'},function(data){});
                }
            } else {
                if(isCheckLockP){
                    var html = '<p style="color: red">Vui lòng mở khóa kế hoạch và thực hiện trước khi mở khóa tỷ trọng.</p>';
                    delChild('message');
                    $('#message').append(html);
                    document.getElementById("il"+rowId).checked = true;
                } else if(isCheckLockTV){
                    var html = '<p style="color: red">Vui lòng mở khóa kế hoạch trước khi mở khóa tỷ trọng.</p>';
                    delChild('message');
                    $('#message').append(html);
                    document.getElementById("il"+rowId).checked = true;
                } else {
                    $('#expireDateIL'+rowId).prop("disabled", false);
                    // đổi trường lock trong db
                    $.get("changeLock",{rowId: rowId, status : status, columnDate: 'expire_date_il', columnLock: 'lock', targetDate: ilDate, logText: 'Ngày hết hạn tỷ trọng'},function(data){});
                }

            }
        });

        $(".ckLockTarget").click(function(){
            delChild('message');
            var rowId = $(this).attr('rowId');
            var status = $(this).attr('status');

            var isCheckLockTV = document.getElementById("target"+rowId).checked;
            var isCheckLockIL = document.getElementById("il"+rowId).checked;
            var isCheckLockP = document.getElementById("perform"+rowId).checked;

            var targetDate = $('#expireDateTarget'+rowId).val();
            var performDate = $('#expireDatePerform'+rowId).val();
            var ilDate = $('#expireDateIL'+rowId).val();

            var dateLock = $('#dateLock'+rowId).val();

            var dayTV = parseInt(targetDate.split('/')[0]);
            var dayIL = parseInt(ilDate.split('/')[0]);

            if(isCheckLockTV){
                // cùng năm, tháng với ngày khóa
                //nhỏ hơn ngày hết hạn thực hiện
                if(isCheckLockIL == 0){
                    var html = '<p style="color: red">Vui lòng khóa tỷ trọng trước khi khóa kế hoạch.</p>';
                    delChild('message');
                    $('#message').append(html);
                    document.getElementById("target"+rowId).checked = false;
                } else if(targetDate == '00/00/0000'){
                    var html = '<p style="color: red">Vui lòng chọn thời gian hết hạn kế hoạch.</p>';
                    delChild('message');
                    $('#message').append(html);
                    document.getElementById("target"+rowId).checked = false;
                } else if(checkEqualMonthYear(dateLock, targetDate)){
                    var html = '<p style="color: red">Tháng và năm của thời gian hết hạn kế hoạch phải bằng thời gian khóa. Vui lòng chọn lại.</p>';
                    delChild('message');
                    $('#message').append(html);
                    document.getElementById("target"+rowId).checked = false;
                } else if(dayIL > dayTV){
                    var html = '<p style="color: red">Ngày hết hạn kế hoạch phải lớn hơn hoặc bằng ngày hết hạn tỷ trọng. Vui lòng chọn lại.</p>';
                    delChild('message');
                    $('#message').append(html);
                    document.getElementById("target"+rowId).checked = false;
                }else {
                    $('#expireDateTarget'+rowId).prop("disabled", true);
                    // đổi trường lock, expire_date, type_lock, perform_lock, target_lock trong db
                    $.get("changeLock",{rowId: rowId, status : status, columnDate: 'expire_date_target', columnLock: 'target_lock', targetDate: targetDate, logText: 'Ngày hết hạn kế hoạch'},function(data){});
                }
            } else {
                if(isCheckLockP){
                    var html = '<p style="color: red">Vui lòng mở khóa thực hiện trước khi mở khóa kế hoạch.</p>';
                    delChild('message');
                    $('#message').append(html);
                    document.getElementById("target"+rowId).checked = true;
                } else {
                    $('#expireDateTarget'+rowId).prop("disabled", false);
                    // đổi trường lock trong db
                    $.get("changeLock",{rowId: rowId, status : status, columnDate: 'expire_date_target', columnLock: 'target_lock', targetDate: targetDate, logText: 'Ngày hết hạn kế hoạch'},function(data){});
                }

            }
        });

        $(".ckLockPerform").click(function(){
            delChild('message');
            var rowId = $(this).attr('rowId');
            var status = $(this).attr('status');

            var isCheckLockTV = document.getElementById("target"+rowId).checked;
            var isCheckLockP = document.getElementById("perform"+rowId).checked;

            var targetDate = $('#expireDateTarget'+rowId).val();
            var performDate = $('#expireDatePerform'+rowId).val();
            var dateLock = $('#dateLock'+rowId).val();

            var dayTV = parseInt(targetDate.split('/')[0]);
            var dayP = parseInt(performDate.split('/')[0]);

            if(isCheckLockP){
                // cùng năm, tháng với ngày khóa
                //nhỏ hơn ngày hết hạn thực hiện
                if(isCheckLockTV == 0){
                    var html = '<p style="color: red">Vui lòng khóa kế hoạch trước khi khóa thực hiện.</p>';
                    delChild('message');
                    $('#message').append(html);
                    document.getElementById("perform"+rowId).checked = false;
                } else if(performDate == '00/00/0000'){
                    var html = '<p style="color: red">Vui lòng chọn thời gian hết hạn thực hiện.</p>';
                    delChild('message');
                    $('#message').append(html);
                    document.getElementById("perform"+rowId).checked = false;
                } else if(!checkMonthYearPerform(performDate, targetDate)){
                    var html = '<p style="color: red">Ngày hết hạn thực hiện phải lớn hơn ngày hết hạn của kế hoạch hoặc không được lớn hơn ngày hết hạn kế hoạch quá 2 tháng. Vui lòng chọn lại.</p>';
                    delChild('message');
                    $('#message').append(html);
                    document.getElementById("perform"+rowId).checked = false;
                } else {
                    $('#expireDatePerform'+rowId).prop("disabled", true);
                    // đổi trường lock, expire_date, type_lock, perform_lock, target_lock trong db
                    $.get("changeLock",{rowId: rowId, status : status, columnDate: 'expire_date_perform', columnLock: 'perform_lock', targetDate: performDate, logText: 'Ngày hết hạn thực hiện'},function(data){});
                }
            } else {
                $('#expireDatePerform'+rowId).prop("disabled", false);
                // đổi trường lock trong db
                $.get("changeLock",{rowId: rowId, status : status, columnDate: 'expire_date_perform', columnLock: 'perform_lock', targetDate: performDate, logText: 'Ngày hết hạn thực hiện'},function(data){});
            }
        });

        $(".ckLockApplyDate").click(function(){
            var dateLock = $(this).attr('dateLock');
            var statusLock = $(this).attr('statusLock');
            $.get("changeLockApplyDate",{dateLock: dateLock, statusLock:statusLock},function(data){});
        });

        $(function() {
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                // save the latest tab; use cookies if you like 'em better:
                localStorage.setItem('lastTab', $(this).attr('href'));
            });
            // go to the latest tab, if it exists:
            var lastTab = localStorage.getItem('lastTab');
            if (lastTab) {
                $('[href="' + lastTab + '"]').tab('show');
            }
        });

    </script>
@stop