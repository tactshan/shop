<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Model\UserModel;

class UserController extends Controller
{
    //

	public function user($uid)
	{
		echo $uid;
	}

	public function test()
    {
        echo '<pre>';print_r($_GET);echo '</pre>';
    }

	public function add()
	{
		$data = [
			'name'      => str_random(5),
			'age'       => mt_rand(20,99),
			'email'     => str_random(6) . '@gmail.com',
			'reg_time'  => time()
		];

		$id = UserModel::insertGetId($data);
		var_dump($id);
	}
	public function data()
    {
	    echo date('Y-m-d H:i:s');
    }
    public function usershow()
    {
	        $info=UserModel::all();
	        $data=[
	          'info'=>$info
            ];
	        return view('user.userlist',$data);
    }
    public function viewTest1()
    {
        $data = [];
        return view('user.index',$data);
    }

    public function viewTest2()
    {
        $list = UserModel::all()->toArray();
        //echo '<pre>';print_r($list);echo '</pre>';

        $data = [
            'title'     => 'XXXX',
            'list'      => $list
        ];

        return view('user.child',$data);
    }
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
        echo __METHOD__;
        echo '<pre>';print_r($_POST);echo '</pre>';

        $data = [
            'name'  => $request->input('u_name'),
            'age'  => $request->input('u_age'),
            'email'  => $request->input('u_email'),
            'reg_time'  => time(),
        ];

        $uid = UserModel::insertGetId($data);
        var_dump($uid);

        if($uid){
            echo '注册成功';
        }else{
            echo '注册失败';
        }
    }
}
