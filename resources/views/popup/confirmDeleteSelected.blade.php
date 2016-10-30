<div id="popupDeleteSelected" aria-hidden="false" aria-labelledby="delete-confirmation" role="dialog" tabindex="-1" class="modal fade dialog-delete">
    <form accept-charset="utf-8" method="post" action="{{action($actionName)}}">
        <input type="hidden" name="_token" value="<?= csrf_token();?>"/>
        <input type="hidden" value="" name="arrId" id="arrId">
        <input type="hidden" value="" name="arrName" id="arrName">
        <div class="modal-dialog">
            <div class="modal-content text-left">
                <div class="modal-header">
                    <button data-dismiss="modal" class="close" type="button btn-sm">
                        <span tabindex="8" aria-hidden="true">×</span>
                        <span class="sr-only">Đóng</span>
                    </button>
                    <strong id="myModalLabel" class="modal-title">Xác nhận xóa</strong>
                </div>
                <div class="modal-body">
                    <p id="pMessage">Đồng ý xóa các mục đã chọn? </p>
                </div>
                <div class="modal-footer">
                    <button data-dismiss="modal" class="btn btn-default" type="button">Hủy Bỏ</button>
                    <button type="submit" class="btn btn-success">Đồng ý</button>
                </div>
            </div>
        </div>
    </form>
</div>