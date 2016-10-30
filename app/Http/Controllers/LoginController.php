<?php namespace App\Http\Controllers;
use DB;
use Session;

//use App\Http\Controllers\Adldap;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Utils\commonUtils;

class LoginController extends AppController {

    public function Log(Request $request){

        $post = $request->all();
        $username = $post['email'];
        $password = $post['password'];
        //$password = md5($post['password']);

        $remember = (isset($post['remember']) && $post['remember'] == 'on') ? 1 : 0 ;
        $response = new \Illuminate\Http\Response($remember);
        $response->withCookie(cookie('name', 'rememberme', commonUtils::TIME_KEEP_COOKIE));

        Session::put('checkin', 0);
        //$rule = "/^[a-zA-Z]{1}[a-zA-Z0-9]{3,50}\@[a-zA-Z0-9]{3,20}\.[a-zA-Z]{2,5}$/";



        if(
            $username != ""
            || $password != ""
        ){


            $username = (strpos($username, '@') == false) ? $username."@kimbich.com.vn" : $username;


            $keywordDenied = commonUtils::keywordDenied();
            foreach($keywordDenied as $kd){
                $pos = (strpos($username, $kd) !== false || strpos($password, $kd) !== false) ? 1 : -1;

                if ($pos == 1) {
                    Session::flush();
                    Session::flash('message-errors', "Tên đăng nhập hoặc mật khẩu không hợp lệ!");
                    return redirect('/');
                }

            }


            /* *****************************************************************************************************
             * Kiểm tra đăng nhập
             * ****************************************************************************************************/

            $sqlUser = "
                SELECT u.*
                FROM users AS u

                WHERE email = '".$username."'
                AND password = '".md5($password)."'
            ";

            $objUserDB = DB::select(DB::raw($sqlUser));

            if(count($objUserDB) == 1){

                $data = $objUserDB[0];
                if($data->inactive == 1){
                    Session::flush();
                    Session::flash('message-errors', "Tài khoản đang tạm khóa, Vui lòng liên hệ Quản trị viên để mở khóa tài khoản!");
                    return redirect('/');
                }

                Session::put('sid',                 $data->user_id);
                Session::put('scode',               $data->code);
                Session::put('sname',               $data->name);
                Session::put('susername',           $data->email);
                Session::put('spassword',           $data->password);
                Session::put('sconfirmed',          $data->confirmed);
                Session::put('sadmin',              $data->is_admin);
                Session::put('sinactive',           $data->inactive);
                Session::put('sDataUser',           $data);
                Session::put('checkin', 1);   # changing | Account Data being passed to view
                Session::put('key', 'value');

                Session::put('numRow', -1);

                Session::save();

                return redirect('adminhome');
            }else{

                Session::flush();
                Session::flash('message-errors', "Tên đăng nhập hoặc mật khẩu không chính xác!");
                return redirect('/');
            }



        }else{
            Session::flush();
            Session::flash('message-errors', "Tên đăng nhập và mật khẩu không được rỗng!");
            return redirect('/');
        }


         /**************************************************************************************************************/

    }

    public static function Logout(){
        # Clear all session before login
        Session::flush();
        return redirect('/');

    }

    public static function ChangePassword($username, $oldPass, $newPass, $confirmPass){
        if($oldPass != '0'){
            $data = DB::table('users')->where('username', $username)
                ->where('inactive', 0)
                ->first();

        }else{
            return response()->json(['data' => '0', 'success' => 'false', 'message' => 'Vui lòng nhập mật khẩu cũ.']);
        }

        if($data){

            if($data->password == $oldPass){
                // TODO: Old password is correct, check confirm password
                if($newPass == '0'){
                    return response()->json(['data' => '2', 'success' => 'false', 'message' => 'Vui lòng nhập mật khẩu mới.']);
                }

                if($confirmPass == '0'){
                    return response()->json(['data' => '2', 'success' => 'false', 'message' => 'Vui lòng nhập xác nhận mật khẩu.']);
                }

                if($newPass == $confirmPass){
                    DB::beginTransaction();
                    try{
                        $updatedUser = Session::get('sid');
                        $data = array(
                            'password' 		=> $newPass,
                            'updated_date'  => date("Y-m-d"),
                            'updated_user'  => $updatedUser);
                        $i = DB::table('users')->where('username', $username)->update($data);

                        if($i){
                            $functionName = 'Đổi mật khẩu (ChangePassword)';
                            $oldValue = 'Username: ' . $username . ', Mật khẩu cũ: ' . $oldPass;
                            $newValue = 'Username: ' . $username . ', Mật khẩu mới: ' . $newPass;

                            $dataLog = array('function_name' => $functionName,
                                            'action'         => commonUtils::ACTION_EDIT,
                                            'url'            => $_SERVER['REQUEST_URI'],
                                            'id_row'         => Session::get('sid'),
                                            'old_value'      => $oldValue,
                                            'new_value'      => $newValue,
                                            'created_user'   => $updatedUser,
                                            'created_date'   => date("Y-m-d"));

                            $log = DB::table('kpi_log')->insert($dataLog);
                            if($log){
                                DB::commit();
                                return response()->json(['data' => '5', 'success' => 'true', 'message' => 'Đổi mật khẩu thành công.']);
                            }
                        }
                    } catch(\Exception $e){
                        DB::rollback();
                        return response()->json(['data' => '4', 'success' => 'false', 'message' => 'Đổi mật khẩu không thành công.']);
                    }
                } else {
                    return response()->json(['data' => '3', 'success' => 'false', 'message' => 'Xác nhận mật khẩu không đúng.']);
                }
            } else {
                return response()->json(['data' => '1', 'success' => 'false', 'message' => 'Mật khẩu cũ không đúng.']);
            }
        }

        return redirect()->back()->withError('Incorrect old password');

    }



}