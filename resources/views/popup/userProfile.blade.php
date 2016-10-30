<div id="popupUserProfile" aria-hidden="false" aria-labelledby="userInfo" role="dialog" tabindex="-1" class="modal fade dialog-user-info">
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
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-3 col-lg-3 " align="center"> <img alt="User Pic" src="{{ asset('public/assets/images/user-icon.png') }}" class="img-circle img-responsive"> </div>

                        <div class=" col-md-9 col-lg-9 ">
                            <table class="table table-user-information">
                                <tbody>
                                    <tr>
                                        <td>Phòng/Đài/MBF HCM:</td>
                                        <td id="companyName"></td>
                                    </tr>
                                    <tr>
                                        <td>Tổ/Quận/Huyện:</td>
                                        <td id="areaName"></td>
                                    </tr>
                                    <tr>
                                        <td>Nhóm/Cửa hàng:</td>
                                        <td id="groupName"></td>
                                    </tr>
                                    <tr>
                                    <tr>
                                        <td>Chức danh:</td>
                                        <td id="positionName"></td>
                                    </tr>
                                    <tr>
                                        <td>Mức truy cập:</td>
                                        <td id="accessLevel"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-primary" data-dismiss="modal" id="btnPasswordChange">Đổi mật khẩu</button>
                    <button type="button" class="btn btn-default btn-primary" data-dismiss="modal" id="btnExit">Đóng</button>
                </div>
            </div>
        </div>
    </div>
</div>