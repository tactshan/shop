<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Model\UserModel;

class UserController extends Controller
{
//    /**
//     * 用户列表展示
//     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
//     */
//    public function usershow(Request $request)
//    {
//        $this->middleware('auth');
//
//        $info=UserModel::all();
//        $uid=session()->get('uid');
//	        $data=[
//	          'info'=>$info,
//                'uid'=>$uid
//            ];
//	        return view('user.userlist',$data);
//    }
//
    /**
     * 用户注册
     * 2019年1月3日14:26:56
     * liwei
     */
    public function reg()
    {
        return view('user.reg');
    }
    public function doReg(Request $request)
    {
        $u_name=$request->input('u_name');
        //验证用户名，验证唯一性
        if(empty($u_name)){
            exit('User name Can\'t be empty!');
        }else{
            //唯一性验证
            $userInfo=UserModel::where(['name'=>$u_name])->first();
            if(!empty($userInfo)){
                header("refresh:3;url=/userreg");
                exit('This user name has already been registered.');
            }
        }
        $u_age=$request->input('u_age');
        if(empty($u_age)){
            exit('Please fill in your age!');
        }
        $pwd=$request->input('u_pwd');
        $qpwd=$request->input('u_qpwd');
        //处理密码，哈希加密
        if($pwd!==$qpwd){
            exit('Password and confirm password must be consistent!');
        }else{
            $pwd=password_hash($pwd,PASSWORD_BCRYPT,['cost'=>12]);
        }
        $data = [
            'name'  => $request->input('u_name'),
            'age'  => $request->input('u_age'),
            'pwd'  => $pwd,
            'email'  => $request->input('u_email'),
            'reg_time'  => time(),
        ];
        $uid = UserModel::insertGetId($data);
        if($uid){
            setcookie('uid',$uid,time()+86400,'','',false,true);
            echo 'Registered successfully';
            header("refresh:2;url=/userlogin");
        }else{
            echo 'Registered fail';
        }
    }

    /**
     * 用户登录
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function loginview(){
        return view('user.login');
    }
    public function userlogin(Request $request){
        $u_email=$request->input('u_email');
        if(empty($u_email)){
            exit('Email can\'t be empty');
        }
        $pwd=$request->input('u_pwd');
        if(empty($pwd)){
            exit('Password can\' be empty');
        }
       $where=[
         'email'=>$u_email,
       ];
       $data=UserModel::where($where)->first();
        $token = substr(md5(time().mt_rand(1,99999)),10,10);
       if(password_verify($pwd,$data->pwd) !== false){
           setcookie('uid',$data->uid,time()+86400,'','',false,true);
           echo 'Login successfully';
           header("refresh:2;url=/goodslist");
       }else{
           header("refresh:2;url=/userlogin");
           echo 'Email or Password is error';exit;
       }
    }

    /**
     * 退出
     *
     */
    public function quit(){
        header("refresh:0;url=/home");
    }





    /**
     * 手机登录测试
     */
    public function phoneLogin()
    {
        $email = $_POST['email'];
        $pwd = $_POST['pwd'];
        $where=[
          'email'=>$email,
          'pwd'=>$pwd
        ];
        $data=UserModel::where($where)->first();
        if(empty($data)){
            echo '账号或密码错误';
        }else{
            echo '登录成功';
        }
    }
    /**
     * 手机注册
     */
    public function phoneRegister()
    {
        $data=$_POST;
        $name = $data['nam'];
        $userInfo=UserModel::where(['name'=>$name])->first();
        if(!empty($userInfo)){
            die('用户名已存在！');
        }
        $uid = UserModel::insertGetId($data);
        echo $uid;
    }
}
