
<?php
    use Utils\commonUtils;
    $curpage =  $data->currentPage();
?>

<div class="row" id="main-content">
    <div class="col-md-12">
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="portlet light portlet-fit bordered">
            <div class="portlet-title">
                <div class="caption">
                    <i class="icon-settings font-red"></i>
                    <span class="caption-subject font-red sbold uppercase">QUẢN LÝ NGƯỜI DÙNG</span>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-toolbar">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="btn-group">
                                <a  id="btnAddUser" class="btn green btn-outline sbold uppercase" > Thêm mới
                                    <i class="fa fa-plus"></i>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="btn-group pull-right">
                                <button class="btn green btn-outline dropdown-toggle" data-toggle="dropdown">Tools
                                    <i class="fa fa-angle-down"></i>
                                </button>
                                <ul class="dropdown-menu pull-right">
                                    <li>
                                        <a href="javascript:;"> Xuất to Excel </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <table class="table table-striped table-hover table-bordered" id="user-table">
                    <thead>
                    <tr>
                        <th> STT </th>
                        <th sort_key="user_code"> Email <i class="fa pull-right unsort fa-sort"></i></th>
                        <th sort_key="user_code"> Mã <i class="fa pull-right unsort fa-sort"></i></th>
                        <th sort_key="user_name"> Tên <i class="fa pull-right unsort fa-sort"></i></th>
                        <th sort_key="user_name"> Admin <i class="fa pull-right unsort fa-sort"></i></th>
                        <th sort_key="user_name"> Lần đăng nhập cuối <i class="fa pull-right unsort fa-sort"></i></th>
                        <th> Sửa </th>
                        <th> Xóa </th>
                    </tr>
                    </thead>
                    <tbody id="main-content">

                    <?php
                    $i = 0;
                    $stt = (($curpage-1) * CommonUtils::ITEM_PER_PAGE_DEFAULT) + 1;
                    foreach($data as $row){
                    $i++;
                    ?>



                    <tr>
                        <td class="text-center"> <?php  echo $stt; $stt++; ?>  </td>
                        <td id="td-code-{{$row->user_id}}"> <?php echo $row->email; ?> </td>
                        <td id="td-code-{{$row->user_id}}"> <?php echo $row->code; ?> </td>
                        <td id="td-name-{{$row->user_id}}"> <?php echo $row->name; ?> </td>
                        <td id="td-name-{{$row->user_id}}"> <?php echo $row->is_admin; ?> </td>
                        <td id="td-name-{{$row->user_id}}"> <?php echo $row->last_logon; ?> </td>
                        <td>
                            <a class="td-edit-user" data-id="{{$row->user_id}}"> Sửa </a>

                        </td>
                        @include('admin.categories.user.updateUser', array('user_id' => $row->user_id,
                                                                            'user_code' => $row->code,
                                                                            'user_name' => $row->name,

                        ))
                        @include('admin.categories.user.deleteUser', array('user_id' => $row->user_id,
                                                                            'user_code' => $row->code,
                                                                            'user_name' => $row->name,

                        ))
                        <td>
                            <a class='td-delete-user' data-id="{{$row->user_id}}"> Xóa </a>
                        </td>
                    </tr>
                    <?php
                    }
                    ?>
                    </tbody>
                    <div class="col-md-12">
                        <div class="pull-right">
                            <?php
                            echo $data->setPath(action('CategoriesController@userCategories'))->render();
                            ?>
                        </div>
                    </div>
                </table>
            </div>

        </div>
        <!-- END EXAMPLE TABLE PORTLET-->
    </div>
</div>

