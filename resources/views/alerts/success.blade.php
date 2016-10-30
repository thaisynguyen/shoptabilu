@if(Session::has('message-success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" area-label="Đóng">
            <span aria-hidden="true">&times;</span>
        </button>
        {{Session::get('message-success')}}
    </div>
@endif
