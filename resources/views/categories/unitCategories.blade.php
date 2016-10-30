@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.CATEGORIES') . ' ' . Config::get('constant.UNIT'))
@section('section')
@include('alerts.errors')
@include('alerts.success')

<?php
    use Utils\commonUtils;
    $curpage =  $data->currentPage();
    $accessLevel = Session::get('saccess_level');
    $isView = Session::get('sis_view');
?>
    <div id="wrapper" >
        <div class="row margin-form">
            <div class="col-sm-12">
                <span class="pull-right pading-right-22">
                    <?php if($accessLevel < 2 && $isView == 0) {?>
                        <a role="button" id="addGoal"  class="btn btn-primary change-color" href="<?= URL::to('addUnit');?>"><i class="fa fa-plus"></i> Thêm Mới</a>
                    <?php } ?>
                </span>
            </div>
            <div class="col-sm-12">
                <span class="pull-right pading-right-22">
                    <?php
                        echo $data->setPath(action('CategoriesController@unitCategories'))->render();
                    ?>
                </span>
            </div>
            <table class="table-common">
                <thead>
                <tr class="text-center">
                    <?php if($accessLevel < 2 && $isView == 0) {?>
                    <th class="col-sm-1">STT</th>
                    <th class="col-sm-1" sort_key="unit_code">Mã ĐVT<i class="fa pull-right unsort fa-sort"></i></th>
                    <th class="col-sm-2" sort_key="unit_name">Tên Đơn Vị Tính<i class="fa pull-right unsort fa-sort"></i></th>
                    <th class="col-sm-6" sort_key="unit_description">Mô Tả<i class="fa pull-right unsort fa-sort"></i></th>
                    <th class="col-sm-1"></th>
                    <th class="col-sm-1"></th>
                    <?php } else {?>
                    <th class="col-sm-1">STT</th>
                    <th class="col-sm-2" sort_key="unit_code">Mã ĐVT<i class="fa pull-right unsort fa-sort"></i></th>
                    <th class="col-sm-2" sort_key="unit_name">Tên Đơn Vị Tính<i class="fa pull-right unsort fa-sort"></i></th>
                    <th class="col-sm-7" sort_key="unit_description">Mô Tả<i class="fa pull-right unsort fa-sort"></i></th>
                    <?php } ?>
                </tr>
                </thead>
                <tbody>
                <?php
                    $i=0;
                    $stt = (($curpage-1) * CommonUtils::ITEM_PER_PAGE_DEFAULT) + 1;
                    foreach($data as $row){
                    $i++;
                ?>
                <?php if($i%2==0):?>
                    <tr class="background-color-smoke">
                <?php else:?>
                    <tr>
                <?php endif; ?>
                    <td class="text-center width-5"><?php  echo $stt; $stt++; ?></td>
                    <td class="text-center"><?php echo $row->unit_code;?></td>
                    <td class="text-left"><?php echo $row->unit_name;?></td>
                    <td class="text-left"><?php echo $row->unit_description;?></td>
                    <?php if($accessLevel < 2 && $isView == 0) {?>
                    <td class="table-icon text-center">
                        <a href="<?php echo 'updateUnit/'.$row->id;?>" role="button" title="Cập nhật">
                            <i class="fa fa-pencil-square-o"></i>
                        </a>
                    </td>
                    <td class="table-icon text-center" >
                        <a role="button"  href="#" data-target=".dialog-delete-goal-<?php echo $row->id; ?>" data-toggle="modal">
                            <i class="fa fa-trash" title="Xóa" ></i>
                        </a>
                        @include('popup.confirmDelete', array('rowId' => $row->id
                                                      , 'data' => $row->id
                                                      , 'strName' => $row->unit_name
                                                      , 'actionName'  => 'CategoriesController@deleteUnit'
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
                        echo $data->setPath(action('CategoriesController@unitCategories'))->render();
                    ?>
                </span>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function(){
        sortOnPageLoad();
    });
</script>
@stop