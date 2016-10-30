<div id="popupDelete" aria-hidden="false" aria-labelledby="delete-confirmation" role="dialog" tabindex="-1" class="modal fade dialog-delete-goal-{{$rowId}}">
    <form accept-charset="utf-8" method="post" action="{{action($actionName)}}" id="deleteDialog">
        <input type="hidden" name="_token" value="<?= csrf_token();?>"/>
        <input type="hidden" value="{{$data}}" name="id">
        <div class="modal-dialog">
            <div class="modal-content text-left">
                <div class="modal-header">
                    <button data-dismiss="modal" class="close" type="button btn-sm">
                        <span tabindex="8" aria-hidden="true" hidden>×</span>
                        <span class="sr-only">Đóng</span>
                    </button>
                    <strong id="myModalLabel" class="modal-title">Xác nhận xóa</strong>
                </div>
                <div class="modal-body">

                    <span class="hidden" id="loadingDelete-{{$rowId}}">

                    </span>
                    <p id="contentDelete-{{$rowId}}">Bạn có chắc muốn xóa các mục sau: <strong>{{$strName}}</strong>?</p>
                    <p id="waitingDelete-{{$rowId}}" class="hidden"><strong> &#8594; Dữ liệu đang được xóa và tính toán lại. Vui lòng đợi trong ít phút!..</strong></p>
                </div>
                <div class="modal-footer">
                    <button data-dismiss="modal" class="btn btn-default" type="button" id="btnCancel-{{$rowId}}">Hủy Bỏ</button>
                    <button type="submit" class="btn btn-success" id="btnOK-{{$rowId}}" onclick="showLoading({{$rowId}})">Đồng ý</button>
                </div>
            </div>
        </div>
    </form>
</div>
