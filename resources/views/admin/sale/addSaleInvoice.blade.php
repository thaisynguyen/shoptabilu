<div class="modal fade" id="modalAddSaleInvoice" tabindex="-1" role="basic" aria-hidden="true" style="display: none;">
    <div class="modal-dialog" style="width: 1000px">
        {{Form::open()}}
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title text-normal text-bold"><?php echo 'Thêm Mới Hóa Đơn Bán Hàng';?></h4>
            </div>
            <div class="modal-body text-normal">
                <div class="form-group has-success">
                    <label class="control-label"><?php echo 'Barcode';?> (<span class="input-require">*</span>)</label>
                    <div class="input-icon right">
                        <input type="text" class="form-control add-data-sale-invoice" id="barcodeid" required> </div>
                </div>

                <div class="form-group has-success">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="control-label"><?php echo 'Số hóa đơn';?> (<span class="input-require">*</span>)</label>
                            <div class="input-icon right">
                                <input type="text" class="form-control add-data-sale-invoice" id="sales-serial-number" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="control-label"><?php echo 'Ngày hóa đơn';?> (<span class="input-require">*</span>)</label>
                            <div class="input-icon right">
                                <div class="input-group date date-picker " id="sales-invoice-date" data-date-format="dd-mm-yyyy" required>
                                    <input type="text" class="form-control" readonly="" name="datepicker">
                                    <span class="input-group-btn">
                                        <button class="btn default" type="button">
                                            <i class="fa fa-calendar"></i>
                                        </button>
                                    </span>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="form-group has-success">
                    <label class="control-label"><?php echo 'Khách Hàng';?> (<span class="input-require">*</span>)</label>
                    <div class="input-icon right">
                        <input type="text" class="form-control add-data-sale-invoice" id="customer" required> </div>
                </div>
                <div class="form-group has-success">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="control-label"><?php echo '% Giảm giá(trên tổng HĐ)';?> (<span class="input-require">*</span>)</label>
                            <div class="input-icon right">
                                <input type="text" class="form-control add-data-sale-invoice" id="discount-rate" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="control-label"><?php echo 'Tiền giảm giá(trên tổng HĐ)';?> (<span class="input-require">*</span>)</label>
                            <div class="input-icon right">
                                <input type="text" class="form-control add-data-sale-invoice" id="discount-amount" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group has-success">
                    <label class="control-label"><?php echo 'Tổng cộng';?> (<span class="input-require">*</span>)</label>
                    <div class="input-icon right">
                        <input type="text" class="form-control add-data-sale-invoice" id="name" required>
                    </div>
                </div>
            </div>

            <table class="table table-striped table-hover table-bordered" id="added-product-table">
                <thead>
                <tr>
                    <th> STT </th>
                    <th> Loại sản phẩm <i class="fa pull-right unsort fa-sort"></i></th>
                    <th> Tên sản phẩm <i class="fa pull-right unsort fa-sort"></i></th>
                    <th> Đơn giá <i class="fa pull-right unsort fa-sort"></i></th>
                    <th> Số lượng <i class="fa pull-right unsort fa-sort"></i></th>
                    <th> Thành tiền <i class="fa pull-right unsort fa-sort"></i></th>
                    <th> % Giảm giá <i class="fa pull-right unsort fa-sort"></i></th>
                    <th> Giảm giá <i class="fa pull-right unsort fa-sort"></i></th>
                    <th> Tổng cộng <i class="fa pull-right unsort fa-sort"></i></th>
                    <th> Xóa </th>
                </tr>
                </thead>
                <tbody id="added-product-list">


                </tbody>

            </table>
            <div class="modal-footer">
                <button type="button" class="btn blue btn-act btn-smooth" id="btnSaveSaleInvoice"><?php echo 'Lưu';?></button>
                <button type="button" class="btn default btn-act btn-smooth" data-dismiss="modal"><?php echo 'Đóng';?></button>
            </div>
        </div>
        </div>
        <!-- /.modal-content -->
        {{Form::close()}}
    </div>
    <!-- /.modal-dialog -->
</div>