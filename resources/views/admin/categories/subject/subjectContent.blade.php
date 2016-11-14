
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
                    <span class="caption-subject font-red sbold uppercase">DANH MỤC KHÁCH HÀNG / NHÀ CUNG CẤP</span>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-toolbar">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="btn-group">
                                <a  id="btnAddSubject" class="btn green btn-outline sbold uppercase" > Thêm mới
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
                <table class="table table-striped table-hover table-bordered" id="subject-table">
                    <thead>
                    <tr>
                        <th class="col-md-1"> STT </th>
                        <th class="col-md-2" sort_key="subject_code"> Mã <i class="fa pull-right unsort fa-sort"></i></th>
                        <th class="col-md-3" sort_key="subject_name"> Tên <i class="fa pull-right unsort fa-sort"></i></th>
                        <th class="col-md-2" sort_key="is_customer"> Khách hàng <i class="fa pull-right unsort fa-sort"></i></th>
                        <th class="col-md-2" sort_key="is_supplier"> Nhà cung cấp <i class="fa pull-right unsort fa-sort"></i></th>
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
                        <td id="td-code-{{$row->subject_id}}"> <?php echo $row->subject_code; ?> </td>
                        <td id="td-name-{{$row->subject_id}}"> <?php echo $row->subject_name; ?> </td>
                        <td id="td-customer-{{$row->subject_id}}" class="text-center">
                            <div class="checker">
                                <span class="<?php echo ($row->is_customer == 1) ? 'checked' : ''; ?>">
                                    <input type="checkbox" class="checkboxes" value="<?php echo $row->is_customer; ?>">
                                </span>
                            </div>

                        </td>
                        <td id="td-supplier-{{$row->subject_id}}" class="text-center">
                            <div class="checker">
                                <span class="<?php echo ($row->is_supplier == 1) ? 'checked' : ''; ?>">
                                    <input type="checkbox" class="checkboxes" value="<?php echo $row->is_supplier; ?>">
                                </span>
                            </div>
                        </td>
                        <td>
                            <a class="td-edit-subject" data-id="{{$row->subject_id}}"> Sửa </a>

                        </td>
                        @include('admin.categories.subject.updateSubject', array('subject_id' => $row->subject_id,
                                                                            'subject_code' => $row->subject_code,
                                                                            'subject_name' => $row->subject_name,
                                                                            'subject_address' => $row->subject_address,
                                                                            'subject_telephone' => $row->subject_telephone,
                                                                            'is_supplier' => $row->is_supplier,
                                                                            'is_customer' => $row->is_customer,

                        ))
                        @include('admin.categories.subject.deleteSubject', array('subject_id' => $row->subject_id,
                                                                            'subject_code' => $row->subject_code,
                                                                            'subject_name' => $row->subject_name,
                                                                            'subject_address' => $row->subject_address,
                                                                            'subject_telephone' => $row->subject_telephone,
                                                                            'is_supplier' => $row->is_supplier,
                                                                            'is_customer' => $row->is_customer,

                        ))
                        <td>
                            <a class='td-delete-subject' data-id="{{$row->subject_id}}"> Xóa </a>
                        </td>
                    </tr>
                    <?php
                    }
                    ?>
                    </tbody>
                    <div class="col-md-12">
                        <div class="pull-right">
                            <?php
                            echo $data->setPath(action('CategoriesController@subjectCategories'))->render();
                            ?>
                        </div>
                    </div>
                </table>
            </div>

        </div>
        <!-- END EXAMPLE TABLE PORTLET-->
    </div>
</div>

