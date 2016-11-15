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
                <div class="has-success">
                    <label class="control-label"><?php echo 'Khách hàng/Nhà cung cấp';?> (<span class="input-require">*</span>)</label>
                    <div class="md-radio-inline">
                        <div class="md-radio">
                            <input type="radio" id="chkCustomer-<?php echo $subject_id;?>" name="chkSubject" class="md-radiobtn" value="1">
                            <label for="chkCustomer" style="color: black">
                                <span></span>
                                <span class="check"></span>
                                <span class="box"></span> Khách hàng </label>
                        </div>
                        <div class="md-radio">
                            <input type="radio" id="chkSupplier-<?php echo $subject_id;?>" name="chkSubject" class="md-radiobtn" value="2">
                            <label for="chkSupplier" style="color: black">
                                <span></span>
                                <span class="check"></span>
                                <span class="box"></span> Nhà cung cấp </label>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" data-id="<?php echo $subject_id;?>" supplier-val="<?php echo $is_supplier;?>" customer-val="<?php echo $is_customer;?>" class="btn blue btn-act btn-edit-subject btn-smooth"><?php echo 'Lưu';?></button>
                <button type="button" class="btn default btn-act btn-smooth" data-dismiss="modal"><?php echo 'Đóng';?></button>
            </div>
        </div>
        <input type="hidden" id="hidden-code-<?php echo $subject_id;?>" required value="<?php echo $subject_code;?>"> </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>