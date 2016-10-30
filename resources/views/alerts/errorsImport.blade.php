@if(Session::has('isset-codes-errors'))
    <div class="alert alert-danger alert-dismissible" role="alert">

        <button type="button" class="close" data-dismiss="alert" area-label="Đóng">
            <span aria-hidden="true">&times;</span>
        </button>
        {{Session::get('isset-codes-errors')}}
    </div>
@endif

