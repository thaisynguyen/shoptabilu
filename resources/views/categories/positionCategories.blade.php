@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.CATEGORIES') . ' ' . Config::get('constant.POSITION'))
@section('section')
@include('alerts.errors')
@include('alerts.success')
<?php
    use Utils\commonUtils;
    $curpage =  $data->currentPage();
    $isView = Session::get('sis_view');
    $accessLevel = Session::get('saccess_level');
    $sDataUser = Session::get('sDataUser');
?>

    <div id="wrapper" >
        <div class="row margin-form">
            <div class="col-sm-12">
                <span class="pull-right pading-right-22">
                    <?php if($accessLevel < 2 && $isView == 0) {?>
                        <a role="button" id="addGoal"  class="btn btn-primary change-color" href="<?= URL::to('addPosition');?>"><i class="fa fa-plus"></i> Thêm Mới</a>
                    <?php } ?>
                </span>
            </div>
            <div class="col-sm-12">
                <span class="pull-right pading-right-22">
                    <?php
                        echo $data->setPath(action('CategoriesController@positionCategories'))->render();
                    ?>
                </span>
            </div>
            <table class="table-common">
                <thead>
                <tr class="text-center">
                    <?php if($accessLevel < 2 && $isView == 0) {?>
                    <th class="col-sm-1">STT</th>
                    <th class="col-sm-2" sort_key="position_code">Mã Chức Danh<i class="fa pull-right unsort fa-sort"></i></th>
                    <th class="col-sm-7" sort_key="position_name">Tên Chức Danh<i class="fa pull-right unsort fa-sort"></i></th>
                    <th class="col-sm-1"></th>
                        <?php if($sDataUser->id == 0){?>
                    <th class="col-sm-1"></th>
                        <?php }?>
                    <?php } else {?>
                    <th class="col-sm-1">STT</th>
                    <th class="col-sm-3" sort_key="position_code">Mã Chức Danh<i class="fa pull-right unsort fa-sort"></i></th>
                    <th class="col-sm-8" sort_key="position_name">Tên Chức Danh<i class="fa pull-right unsort fa-sort"></i></th>
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
                    <td class="order-column width-5" ><?php  echo $stt; $stt++; ?></td>
                    <td><?php echo $row->position_code ;?></td>
                    <td><?php echo $row->position_name ;?></td>
                    <?php if($accessLevel < 2 && $isView == 0) {?>
                    <td class="table-icon text-center">
                        <a href="<?php echo 'updatePosition/'.$row->id ?>" role="button" title="Cập nhật">
                            <i class="fa fa-pencil-square-o"></i>
                        </a>
                    </td>
                        <?php if($sDataUser->id == 0){?>
                            <td class="table-icon text-center" >
                                <a role="button" href="#" data-target=".dialog-delete-goal-<?php echo $row->id; ?>" data-toggle="modal">
                                    <i class="fa fa-trash" title="Xóa" ></i>
                                </a>
                                @include('popup.confirmDelete', array('rowId' => $row->id
                                                             , 'data' => $row->id
                                                              , 'strName' => $row->position_name
                                                              , 'actionName'  => 'CategoriesController@deletePosition'
                                                              ))
                            </td>
                        <?php }?>
                    <?php } ?>
                </tr>
                <?php } ?>
                </tbody>
            </table>
            <div class="col-sm-12">
                <span class="pull-right pading-right-22">
                    <?php
                        echo $data->setPath(action('CategoriesController@positionCategories'))->render();
                    ?>
                </span>
            </div>
        </div>
        <script>
            $(document).ready(function(){
                sortOnPageLoad();
            });
        </script>
    </div>
@stop
