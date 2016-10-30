<div id="popupWaiting" aria-hidden="false" aria-labelledby="calculate-waiting" role="dialog" tabindex="-1" class="modal fade dialog-waiting-cal-kpi-{{$calId}}">
    <div class="modal-dialog">
        <div class="modal-content text-left">
            <div class="modal-header">
                <button data-dismiss="modal" class="close" type="button btn-sm">
                    <span tabindex="8" aria-hidden="true" hidden>×</span>
                    <span class="sr-only">Đóng</span>
                </button>
                <span id="loadingWating-{{$calId}}" class="loading-delete"></span>
                <strong id="myModalLabel" class="modal-title">Đang tính...</strong>
            </div>
            <div class="modal-body">
                <p id="waitingCalculator-{{$calId}}" ><strong> &#8594; Dữ liệu đang được tính toán lại. Vui lòng đợi trong ít phút!..</strong></p>
            </div>
        </div>
    </div>
</div>
