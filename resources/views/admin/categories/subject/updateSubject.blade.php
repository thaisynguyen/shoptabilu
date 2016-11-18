<?php
?>

<div class="modal fade" id="edit-subject-<?php echo $subject_id;?>" tabindex="-1" role="basic" aria-hidden="true" style="display: none;">

    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title text-normal text-bold"><?php echo 'Sửa Khách Hàng/Nhà Cung Cấp';?></h4>


            </div>
            <div class="modal-body text-normal">
                <div class="form-group has-success">
                    <label class="control-label text-left"><?php echo 'Mã';?> (<span class="input-require">*</span>):</label>
                    <div class="input-icon right">
                        <input type="text" class="form-control" id="code-<?php echo $subject_id;?>" required value="<?php echo $subject_code;?>"> </div>
                </div>
                <div class="form-group has-success">
                    <label class="control-label text-left"><?php echo 'Tên';?> (<span class="input-require">*</span>):</label>
                    <div class="input-icon right">
                        <input type="text" class="form-control" id="name-<?php echo $subject_id;?>" required value="<?php echo $subject_name;?>"> </div>
                </div>
                <div class="form-group has-success">
                    <label class="control-label text-left"><?php echo 'Số điện thoại';?> (<span class="input-require">*</span>):</label>
                    <div class="input-icon right">
                        <input type="text" class="form-control" id="phone-<?php echo $subject_id;?>" required value="<?php echo $subject_telephone;?>"> </div>
                </div>
                <div class="form-group has-success">
                    <label class="control-label text-left"><?php echo 'Địa chỉ';?> (<span class="input-require">*</span>):</label>
                    <div class="input-icon right">
                        <input type="text" class="form-control" id="address-<?php echo $subject_id;?>" required value="<?php echo $subject_address;?>"> </div>
                </div>
                <?php
                $checkedSup = $is_supplier == 1 ? 'checked' : '';
                $checkedCus = $is_customer == 1 ? 'checked' : '';
                ?>
                <div class="has-success">
                    <label class="control-label"><?php echo 'Khách hàng/Nhà cung cấp';?> (<span class="input-require">*</span>)</label>

                    <div class="md-radio-inline">
                        <div class="md-radio">
                            <input type="radio" data-id="{{$subject_id}}" value="1" id="chkCustomer-{{$subject_id}}" name="nameSubject-{{$subject_id}}" class="md-radiobtn customer" <?php echo $checkedCus;?>>
                            <label for="chkCustomer-{{$subject_id}}">
                                <span class="inc"></span>
                                <span class="check"></span>
                                <span class="box"></span> Khách hàng </label>
                        </div>
                        <div class="md-radio">
                            <input type="radio" data-id="{{$subject_id}}" value="2" id="chkSupplier-{{$subject_id}}" name="nameSubject-{{$subject_id}}" class="md-radiobtn supplier" <?php echo $checkedSup;?>>
                            <label for="chkSupplier-{{$subject_id}}">
                                <span class="inc"></span>
                                <span class="check"></span>
                                <span class="box"></span> Nhà cung cấp </label>
                        </div>

                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" data-id="{{$subject_id}}"  class="btn blue btn-act btn-edit-subject btn-smooth"><?php echo 'Lưu';?></button>
                <button type="button" class="btn default btn-act btn-smooth" data-dismiss="modal"><?php echo 'Đóng';?></button>
            </div>
        </div>
        <input type="hidden" id="hidden-code-<?php echo $subject_id;?>" required value="<?php echo $subject_code;?>"> </div>
        <input type="hidden" id="hidden-select-<?php echo $subject_id;?>" required value=""> </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
