<?php

namespace App\Http\Controllers\Pay;

use Illuminate\Http\Request;
use App\Model\OrderModel;
use App\Model\OrderDetailModel;
use App\Http\Controllers\Controller;

class CrontabController extends Controller
{
    /**
     * 删除订单
     */
    public function deleteOrder(Request $request){
        $uid=$request->session()->get('uid');
        $where=[
            'user_id'=>$uid
        ];
        $orderInfo=OrderModel::where($where)->get();
        if(empty($orderInfo)){
            echo 'NULL';exit;
        }
        var_dump($orderInfo);exit;
//        $res=false;
//        foreach ($orderInfo as $k=>$v){
//            if($v['order_status']==1){
//                echo '1';
//                if(time()-$v['c_time'] > 300){
//                    echo '2';
//                    $Orderwhere=['order_num'=>$v['order_num']];
//                    $data=[
//                        'order_status'=>3
//                    ];
//                    $res=OrderModel::where($Orderwhere)->update($data);
//                    var_dump($res);
//                }
//            }
//        }
//        echo '3';
//        var_dump($res);
//        if($res!==false){
//            echo "Success"."\n";
//        }else{
//            echo 'Error';exit;
//        }

    }





}
