<div id="popupUpdateUserProfile"  backdrop="static" data-keyboard="false" aria-hidden="false" aria-labelledby="updateUserInfo" role="dialog" tabindex="-1" class="modal fade in dialog-update-password">
    <div class="row">
        <div class="col-md-5 toppad pull-right col-md-offset-3">
            <a href="{{url('/Logout')}}" >Đăng xuất</a>
            <br>
            <p class="text-info">{{date('d-m-Y')}}</p>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xs-offset-0 col-sm-offset-0 col-md-offset-3 col-lg-offset-3 toppad">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">{{$name}}</h3>
                </div>

                <input type="hidden" id="username" class="form-control txt-200">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-3 col-lg-3" align="center"> <img alt="User Pic" src="{{ asset('public/assets/images/user-icon.png') }}" class="img-circle img-responsive"> </div>
                        <input type="hidden" id="username" class="form-control txt-200">
                        <div class=" col-md-9 col-lg-9">
                            <table>
                                <tbody>
                                    <tr>
                                        <td>Mật khẩu cũ (*):</td>
                                        <td><input type="password" id="oldPassword" class="form-control txt-200"></td>
                                    </tr>
                                    <tr>
                                        <td>Mật khẩu mới (*):</td>
                                        <td><input type="password" id="newPassword" class="form-control txt-200"></td>
                                    </tr>
                                    <tr>
                                        <td>Xác nhận mật khẩu (*):</td>
                                        <td><input type="password" id="confirmPassword" class="form-control txt-200"></td>
                                    </tr>
                                    <tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div id="msgError" class="alert alert-danger alert-dismissible" role="alert" style="display: none">
                    <label id="lblError"></label>
                </div>
                <div id="msgSuccess" class="alert alert-success alert-dismissible" role="alert" style="display: none">
                    <label id="lblSuccess"></label>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-default btn-primary" id="btnSavePassword">Lưu</button>
                    <button type="button" class="btn btn-default btn-primary" data-dismiss="modal" id="btnExitChangePass">Đóng</button>
                </div>
            </div>
        </div>
    </div>
</div>