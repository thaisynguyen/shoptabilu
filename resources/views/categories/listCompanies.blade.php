@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.CATEGORIES') . ' ' . Config::get('constant.COMPANY'))
@section('section')
@include('alerts.success')
@include('alerts.errors')

<?php
    use Utils\commonUtils;
    $curpage =  $data->currentPage();
    $accessLevel = Session::get('saccess_level');
    $isView = Session::get('sis_view');

        $hiddenExportExcel =  ($accessLevel <= 2 && $isView == 0) ? '' : 'hidden';
        $hiddenARCompany   =  ($accessLevel == 1 && $isView == 0) ? '' : 'hidden';
?>
    <div id="wrapper" >
        <div class="row margin-form">
            <div class="col-sm-12">
                <span class="pull-right pading-right-22">
                    <a role="button" id="exportCompany" class="btn btn-primary pull-right {{ $hiddenExportExcel }}"
                       href="<?= URL::to('exportCompany');?>" ><i class="fa fa-plus"></i> Xuất Excel</a>
                        <a role="button" id="addCompany" class="btn btn-primary pull-right change-color margin-btnadd {{ $hiddenARCompany }}"
                           href="<?= URL::to('addCompany');?>" ><i class="fa fa-plus"></i> Thêm Mới</a>
                        <a role="button" id="deleteCompany" class="btn btn-primary pull-right change-color margin-btnadd {{ $hiddenARCompany }}"
                        ><i class="fa fa-times"></i> Xóa Nhiều</a>
                </span>
            </div>
            <div class="col-sm-12">
                <span class="pull-right pading-right-22">
                    <?php
                    echo $data->setPath(action('CategoriesController@viewCompanies'))->render();
                    ?>
                </span>
            </div>
            <table id="tblCompany" class="table-common">
                <thead>
                <tr>
                    <?php if($accessLevel < 2 && $isView == 0) {?>
                    <th sort_key="stt" class="col-sm-1 text-center">
                        STT
                    </th>
                    <th sort_key="company_code" class="col-sm-2 sortable">
                        Mã<i class="fa pull-right unsort fa-sort"></i>
                    </th>
                    <th sort_key="company_name" class="col-sm-3 text-center sortable">
                        Tên<i class="fa pull-right unsort fa-sort"></i>
                    </th>
                    <th sort_key="manager" class="col-sm-2 text-center sortable">
                        Lãnh Đạo Đơn Vị<i class="fa pull-right unsort fa-sort"></i>
                    </th>
                    <th sort_key="phone" class="col-sm-1 text-center sortable">
                        Điện Thoại<i class="fa pull-right unsort fa-sort"></i>
                    </th>
                    <th></th>
                    <th class="col-sm-1 text-center"></th>
                    <th class="col-sm-1 text-center"></th>
                    <?php } else {?>
                    <th id="stt" class="col-sm-1 text-center">
                        STT
                    </th>
                    <th id="company_code" class="col-sm-2 sortable">
                        Mã<i class="fa pull-right unsort fa-sort"></i>
                    </th>
                    <th id="company_name" class="col-sm-4 text-center sortable">
                        Tên<i class="fa pull-right unsort fa-sort"></i>
                    </th>
                    <th id="manager" class="col-sm-2 text-center sortable">
                        Lãnh Đạo Đơn Vị<i class="fa pull-right unsort fa-sort"></i>
                    </th>
                    <th id="phone" class="col-sm-2 text-center sortable">Điện Thoại<i class="fa pull-right unsort fa-sort"></i>
                    </th>

                    <?php } ?>
                </tr>
                </thead>
                <tbody>
                <?php
                    $i = 0;
                    $stt = (($curpage-1) * CommonUtils::ITEM_PER_PAGE_DEFAULT) + 1;
                    foreach($data as $row){
                    $i++;
                ?>
                <?php if($i % 2 == 0):?>
                <tr class="background-color-smoke">
                <?php else:?>
                <tr>
                    <?php endif; ?>
                    <td class="order-column text-center"><?php  echo $stt; $stt++; ?></td>
                    <td ><?php echo $row->company_code;?></td>
                    <td class="text-left"><?php echo $row->company_name;?></td>
                    <td class="text-left"><?php echo $row->manager;?></td>
                    <td class="text-center"><?php echo $row->phone;?></td>
                    <?php if($accessLevel < 2 && $isView == 0) {?>
                    <td class="order-column text-center"><input id="chkSelectedRow" type="checkbox" rowId="{{$row->id;}}" rowCode="{{$row->company_code;}}" rowName="{{$row->company_name;}}"> </td>
                    <td class="table-icon text-center">
                        <a href="<?php echo 'updateCompany/'.$row->id;?>" role="button" title="Cập nhật">
                            <i class="fa fa-pencil-square-o"></i>
                        </a>
                    </td>

                    <td class="table-icon text-center" >
                        <a role="button" href="#" data-target=".dialog-delete-goal-<?php echo $row->id; ?>" data-toggle="modal">
                            <i class="fa fa-trash" title="Xóa" ></i>
                        </a>
                        @include('popup.confirmDelete', array('rowId' => $row->id
                                                      , 'data' => $row->id
                                                      , 'strName' => $row->company_name
                                                      , 'actionName'  => 'CategoriesController@deleteCompany'
                                                      ))

                    </td>
                    <?php } ?>
                </tr>
                <?php } ?>
                </tbody>
            </table>
            <div class="col-sm-12">
                <span class="pull-right pading-right-22">
                    <?php
                    echo $data->setPath(action('CategoriesController@viewCompanies'))->render();
                    ?>
                </span>
            </div>
        </div>
    </div>
    @include('popup.confirmDeleteSelected', array('actionName' => 'CategoriesController@deleteSelectedCompany'))
    <script>
        $(document).ready(function(){
            //sort on column
            sortOnPageLoad();

            //delete multi row
            btnDeleteMultiRowClick($("#deleteCompany"), $("#tblCompany"));

        });
    </script>
@stop