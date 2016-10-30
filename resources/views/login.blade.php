@extends ('layouts.plane')
@section ('body')
    @include('alerts.errors')
    <div class="container">
        <div class="row">
            <div class="col-md-4 col-md-offset-4">
                <br /><br /><br />
                @section ('login_panel_title', 'Đăng Nhập')
                @section ('login_panel_body')
                    <form role="form" action="{{action('LoginController@Log')}}" method="POST">
                        <input type="hidden" name="_token" value="<?= csrf_token();?>"/>
                        <fieldset>
                            <div class="form-group">
                                <label>Email:</label>
                                <input class="form-control" placeholder="example@email.com" name="email" type="text" autofocus >
                            </div>
                            <div class="form-group">
                                <label>Mật khẩu:</label>
                                <br/>
                                <input class="form-control" placeholder="Password" name="password" type="password" >
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input name="remember" type="checkbox">Ghi nhớ đăng nhập
                                </label>
                            </div>
                            <!-- Change this to a button or input when using this as a form -->
                            <button role="button" type="submit" class="btn btn-primary btn-block">Login</button>
                        </fieldset>
                    </form>

                @endsection
                @include('widgets.panel', array('as'=>'login', 'header'=>true))
            </div>
        </div>
    </div>
@stop