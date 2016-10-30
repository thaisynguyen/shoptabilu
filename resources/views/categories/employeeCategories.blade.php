@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.CATEGORIES') . ' ' . Config::get('constant.EMPLOYEE'))
@section('section')
@include('alerts.success')  
@include('alerts.errors')
    <?php
        use Utils\commonUtils;
        $curpage =  $data->currentPage();
        $accessLevel = Session::get('saccess_level');
        $isView = Session::get('sis_view');
        $sDataUser = Session::get('sDataUser');
            $hidden = ($accessLevel > 3 || $isView == 1) ? "hidden" : "";
            $hiddenExportExcel = ($accessLevel > 3 || $isView == 1) ? "hidden" : "";
            $hiddenAll = ($accessLevel > 2) ? "hidden" : "";
    ?>
    <div id="wrapper" >

        <div class="col-md-12 col-xs-12 margin-form margin-bottom-20">
            <div class="col-md-3 col-xs-12">
                <b class="font-13">Tổ/Quận/Huyện</b><br>
                <div class="col-md-12 btnChoose marg-top-title" id="divSlCompany">
                    <select class="form-control margin-top-8"  id="cboArea" onchange="onChangeArea();" >
                        <?php if($hiddenAll == ""){?>
                            <option value="full">Tất cả</option>
                        <?php }?>

                        <?php foreach ($area as $a) {
                                if($selectedArea == $a->area_code){?>
                                     <option value="<?php echo $a->area_code;?>" selected><?php echo $a->area_name;?></option>
                                <?php } else { ?>
                                    <option value="<?php echo $a->area_code;?>"><?php echo $a->area_name;?></option>
                                <?php }
                         } ?>
                    </select>
                </div>
            </div>

            <div class="col-md-3 col-xs-12">
                <b class="font-13">Nhân viên</b><br>
                <div class="col-md-12 btnChoose marg-top-title" id="divSlCompany">
                    <select class="form-control margin-top-8" id="cboUser" onchange="onChangeUser();">
                        <option value="full">Tất cả</option>
                        <?php foreach ($user as $u) {
                            if($selectedEmp == $u->code){?>
                                 <option value="<?php echo $u->code;?>" selected><?php echo $u->name;?></option>
                            <?php } else { ?>
                                 <option value="<?php echo $u->code;?>"><?php echo $u->name;?></option>
                            <?php }
                            } ?>
                    </select>
                </div>
            </div>

            <div class="col-md-5 col-xs-12 margin-top-25">
                <form id='frmEmployee' action="{{action('CategoriesController@employeeCategories')}}" method="POST" class="form-horizontal ">
                    <input type="hidden" name="_token" value="<?= csrf_token();?>"/>
                    <div class="row">
                        <div class="col-sm-12">
                            <?php if(isset($key)){?>
                            <input type="text" class="col-xs-11 col-md-8" id="txtKeySearch" name="txtKeySearch" value="<?php echo $key?>">
                            <?php } else {?>
                            <input type="text" class="col-xs-11 col-md-8 magn-bottom-10" id="txtKeySearch" value="" name="txtKeySearch" placeholder="Tìm theo mã hoặc tên nhân viên.....">
                            <?php }?>
                            <input type="hidden" class="form-control" id="countEmployee" value="<?php echo count($data);?>"></a>
                            <button  type="submit" id="btnSearch" class="btn btn-primary btn_search_emp"><i class="glyphicon glyphicon-search"></i> Tìm</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>


        <div class="row margin-form">
            <div class="col-sm-12">
                <span class="pull-right pading-right-22">

                    <a role="button" id="exportEmployee" class="btn btn-primary pull-right marg-bottom-10 {{ $hiddenExportExcel }}"
                       onclick="reloadPageWithParam('{{action('ExportExcelController@exportEmployee')}}'
                               , 'exportEmployee'
                               , 'cboArea/cboUser/txtKeySearch')"><i
                                class="fa fa-sign-out"></i> Xuất Excel</a>

                    <a role="button" id="addGoal" class="btn btn-primary pull-right change-color margin-btnadd {{ $hidden }}"
                       href="<?= URL::to('addEmployee/0');?>" ><i class="fa fa-plus"></i> Thêm Mới</a>
                    <a role="button" id="deleteMultiEmployee" class="btn btn-primary pull-right change-color margin-btnadd"
                    ><i class="fa fa-times"></i> Xóa Nhiều</a>

                        <?php if($accessLevel < 2 && $isView == 0){?>

                        <?php } ?>
                </span>
            </div>
            <div class="col-sm-12 magn-bottom-10" id="paginationHeader">
                <span class="pull-right pading-right-22">
                    <?php
                    echo $data->setPath(action('CategoriesController@employeeCategories'))->render();
                    ?>
                </span>
            </div>
            <table class="table-common" id="tblEmployee">
                <thead>
                <tr style="text-align: center">

                    <th class="text-50 ">STT</th>
                    <th class="text-80 sortable" sort_key="code">Mã NV<i class="fa pull-right unsort fa-sort"></i></th>
                    <th class="text-100 sortable" sort_key="username">Tên đăng nhập<i class="fa pull-right unsort fa-sort"></i></th>
                    <th class="text-200 sortable" sort_key="name">Tên Nhân Viên<i class="fa pull-right unsort fa-sort"></i></th>
                    <th class="text-100 sortable" sort_key="company_name">Phòng/Đài/MBF HCM<i class="fa pull-right unsort fa-sort"></i></th>
                    <th class="text-130 sortable" sort_key="area_name">Tổ/Quận/Huyện<i class="fa pull-right unsort fa-sort"></i></th>
                    <th class="text-130 sortable" sort_key="group_name">Nhóm/Cửa hàng<i class="fa pull-right unsort fa-sort"></i></th>
                    <th class="text-120 sortable" sort_key="position_name">Chức Danh<i class="fa pull-right unsort fa-sort"></i></th>
                    <th class="text-80 sortable" sort_key="access_level_name">Mức Truy Cập<i class="fa pull-right unsort fa-sort"></i></th>
                    <th class="text-80 sortable" sort_key="terminate_date">Ngày Nghỉ Việc<i class="fa pull-right unsort fa-sort"></i></th>
                    <th class="text-50 sortable" sort_key="is_view">Chỉ Xem<i class="fa pull-right unsort fa-sort"></i></th>
                    <th class="text-50 sortable" sort_key="ldap">LDAP<i class="fa pull-right unsort fa-sort"></i></th>


                    <?php
                        $hidden = ($accessLevel < 2 && $isView == 0) ? '' : 'hidden';
                        if($accessLevel < 2 && $isView == 0) {
                    ?>
                    <th></th>
                    <th class="text-50"></th>
                    <th class="text-50"></th>
                    <?php } ?>
                </tr>
                </thead>
                <tbody>
                <?php
                    $i=0;
                    $stt = (($curpage-1) * CommonUtils::ITEM_PER_PAGE_DEFAULT) + 1;
                    foreach ($data as $row) {

                    $i++;
                    if ($i%2==0):?>
                <tr class="background-color-smoke">
                <?php else:?>
                <tr>
                    <?php endif; ?>
                    <td class="text-center"><?php  echo $stt; $stt++; ?></td>
                    <td class="text-center"><?php echo $row->code ;?></td>
                    <td><?php echo $row->username;?></td>
                    <td><?php echo $row->name;?></td>
                    <td><?php echo $row->company_name;?></td>
                    <td><?php echo $row->area_name;?></td>
                    <td><?php echo $row->group_name;?></td>
                    <td><?php echo $row->position_name;?></td>
                    <td><?php echo $row->access_level_name;?></td>
                    <td class="text-center">
                        <?php
                        $terminatedate =  ($row->terminate_date != "0000-00-00") ? $row->terminate_date : "";
                        if($terminatedate != ""){
                            echo commonUtils::formatDate($terminatedate);
                        }
                        ?>
                    </td>
                    <td class="table-icon text-center">
                        <?php if($row->is_view==0){ ?>
                        <i class="fa fa-square-o" title="Chỉ xem"></i>
                        <?php }else{ ?>
                        <i class="fa fa-check-square-o" title=""></i>
                        <?php } ?>
                    </td>
                    <td class="table-icon text-center">
                        <?php if($row->ldap==0){ ?>
                        <i class="fa fa-square-o" title="ldap"></i>
                        <?php }else{ ?>
                        <i class="fa fa-check-square-o" title=""></i>
                        <?php } ?>
                    </td>
                    <?php if($isView == 0) {
                        ?>
                    <td class="order-column text-center" {{$hidden}}><input id="chkSelectedRow" type="checkbox" rowId="{{$row->id;}}" rowCode="{{$row->code;}}" rowName="{{$row->name;}}" companyId="{{$row->company_id}}"> </td>
                    <td class="table-icon text-center" {{$hidden}}>
                        <a id="btnUpdateEmployee" href="<?php echo "updateEmployee/" . $row->id . '/' . $curpage . '/' . $row->company_id;?>" rowId="{{$row->id;}}" role="button" title="Cập nhật">
                            <i class="fa fa-pencil-square-o"></i>
                        </a>
                    </td>
                    <td class="table-icon text-center" {{$hidden}}>
                        <a role="button"  href="#" data-target=".dialog-delete-goal-<?php echo $row->id; ?>" data-toggle="modal">
                            <i class="fa fa-trash" title="Xóa" ></i>
                        </a>
                        @include('popup.confirmDelete', array('rowId' => $row->id
                                                      , 'data' => $row->id
                                                      , 'strName' => $row->name
                                                      , 'actionName'  => 'CategoriesController@deleteEmployee'
                                                      ))

                    </td>
                    <?php } ?>
                </tr>
                <?php } ?>
                </tbody>
            </table>
            <div id="result" style="color: red"></div>
            <div class="col-sm-12" id="paginationFooter">
                <span class="pull-right pading-right-22">
                    <?php
                        echo $data->setPath(action('CategoriesController@employeeCategories'))->render();
                    ?>
                </span>
            </div>
            @include('popup.confirmDeleteSelected', array('actionName' => 'CategoriesController@deleteSelectedEmployee'))
        </div>
    </div>
    </div>
    {{ HTML::style('public/assets/stylesheets/select2.min.css') }}
    {{ HTML::style('public/assets/stylesheets/employee.css') }}
    {{ HTML::script('public/assets/scripts/select2.min.js') }}
    <script>
        function linkToSearch(){
            var area = $('#cboArea').val();
            var emp = $('#cboUser').val()
            var pathname = window.location.pathname;
            window.location.href = pathname+"?area="+area+"&emp="+emp;
        }

        function onChangeArea(){
            linkToSearch();
        }

        function onChangeUser(){
             linkToSearch();
        }

        $('#cboUser').select2();
        $('#cboArea').select2();
        $(document).ready(function(){
            document.getElementById('addGoal').style.color="white";
            $("#addGoal").hover(function() {
                document.getElementById('addGoal').style.color="black";
            },function() {
                document.getElementById('addGoal').style.color="white";
            });
            //sort on column
            sortOnPageLoad();

            //delete multi row
            btnDeleteMultiRowClick($("#deleteMultiEmployee"), $("#tblEmployee"));

            //search
            if(getUrlParameter('search') != ''){
                $('#txtKeySearch').val(getUrlParameter('search'));
            }

            $('#btnSearch').click(function(){
                var pathname = window.location.pathname;
                var action = $('#frmEmployee').attr('action', pathname + '?search=' + $('#txtKeySearch').val());
            });

            $('#btnUpdateEmployee').click(function(){
                var pathname = window.location.pathname;
                $('#btnUpdateEmployee').attr('href', '{{url('/')}}' + '/updateEmployee/' + $('#btnUpdateEmployee').attr('rowId') +'/' + {{$curpage}} + '/' + $('#chkSelectedRow').attr('companyId'));
            });
        });
    </script>
@stop