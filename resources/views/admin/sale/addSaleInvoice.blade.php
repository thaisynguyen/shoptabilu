<div class="modal fade" id="modalAddSaleInvoice" tabindex="-1" role="basic" aria-hidden="true" style="display: none;">
    <div class="modal-dialog" style="width: 1200px">
        {{Form::open()}}
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title text-normal text-bold"><?php echo 'Thêm Mới Hóa Đơn Bán Hàng';?></h4>
            </div>
            <div class="modal-body text-normal">
                <div class="form-group has-success">
                    <div class="row">
                        <label class="col-md-3 control-label"><?php echo 'Số hóa đơn';?> (<span class="input-require">*</span>)</label>
                        <div class="col-md-3">
                            <input type="text" class="form-control add-data-sale-invoice" id="sales-serial-number"  name="sales-serial-number" required>
                        </div>
                        <label class="col-md-2 control-label"><?php echo 'Ngày hóa đơn';?> (<span class="input-require">*</span>)</label>
                        <div class="col-md-3">
                            <div class="input-group date date-picker "  data-date-format="dd/mm/yyyy" required>
                                <input id="sales-invoice-date" type="text" class="form-control" readonly="" name="datepicker" >
                                        <span class="input-group-btn">
                                            <button class="btn default" type="button">
                                                <i class="fa fa-calendar"></i>
                                            </button>
                                        </span>

                            </div>
                        </div>

                    </div>
                </div>
                <div class="form-group has-success">
                    <div class="row">

                        <label class="col-md-3 control-label"><?php echo 'Khách Hàng';?> (<span class="input-require">*</span>)</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control add-data-sale-invoice" id="customer" required>
                        </div>
                    </div>
                </div>
                <div class="form-group has-success">
                    <div class="row">
                        <label class="col-md-3 control-label"><?php echo '% Giảm giá(trên tổng HĐ)';?> (<span class="input-require">*</span>)</label>
                        <div class="col-md-3 input-icon right">
                            <input type="text" class="form-control add-data-sale-invoice" id="discount-rate" required>
                        </div>
                        <label class="col-md-2 control-label"><?php echo 'Tiền giảm giá(trên tổng HĐ)';?> (<span class="input-require">*</span>)</label>
                        <div class="col-md-3 input-icon right">
                            <input type="text" class="form-control add-data-sale-invoice" id="discount-amount" required>
                        </div>
                    </div>
                </div>
                <div class="form-group has-success">
                    <div class="row">
                        <label class="col-md-3 control-label"><?php echo 'Tổng cộng';?> (<span class="input-require">*</span>)</label>
                        <div class="col-md-3 input-icon right">
                            <input type="text" class="form-control add-data-sale-invoice" id="total-invoice" required readonly>
                        </div>
                    </div>
                </div>
            </div>

            <table class="table table-striped table-hover table-bordered" id="added-product-table">
                <thead>
                <tr>
                    <th> STT </th>
                    <th> Mã sản phẩm <i class="fa pull-right unsort fa-sort"></i></th>
                    <th> Tên sản phẩm <i class="fa pull-right unsort fa-sort"></i></th>
                    <th> Đơn giá <i class="fa pull-right unsort fa-sort"></i></th>
                    <th> Số lượng <i class="fa pull-right unsort fa-sort"></i></th>
                    <th> Thành tiền <i class="fa pull-right unsort fa-sort"></i></th>
                    <th> % Giảm giá <i class="fa pull-right unsort fa-sort"></i></th>
                    <th> Giảm giá <i class="fa pull-right unsort fa-sort"></i></th>
                    <th> Tổng cộng <i class="fa pull-right unsort fa-sort"></i></th>
                    <th> Xóa </th>
                    <th> Thêm </th>
                </tr>
                </thead>
                <?php $stt = 0;?>
                <tbody id="added-product-list">
                    <tr id="row-product">
                        <td>
                            <?php echo $stt += 1;?>
                        </td>
                        <td>

                                <?php
                                    echo $optionProduct;
                                ?>

                        </td>
                        <td>
                            <input type="text" class="form-control" id="product_name" required>
                        </td>
                        <td>
                            <input type="number" class="form-control" id="unit_price" required>
                        </td>
                        <td>
                            <input type="number" class="form-control" id="quantity" required>
                        </td>
                        <td>
                            <input type="number" class="form-control" id="amount" required readonly>
                        </td>
                        <td>
                            <input type="number" class="form-control" id="discount" required>
                        </td>
                        <td>
                            <input type="number" class="form-control" id="discount_amount" required>
                        </td>
                        <td>
                            <input type="number" class="form-control" id="total" required readonly>
                        </td>
                        <td>
                            <a class='td-delete-row' > Xóa </a>
                        </td>
                        <td>
                            <a class='td-add-row' > Thêm </a>
                        </td>
                    </tr>


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