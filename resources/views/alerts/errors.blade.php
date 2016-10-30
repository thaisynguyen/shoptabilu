@if(Session::has('message-errors'))
    <div class="alert alert-danger alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" area-label="Đóng">
            <span aria-hidden="true">&times;</span>
        </button>
        {{Session::get('message-errors')}}
    </div>
@endif
