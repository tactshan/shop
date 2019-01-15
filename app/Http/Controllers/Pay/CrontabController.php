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
            echo ('还没有下单');exit;
        }
        $orderInfo=$orderInfo->toArray();
        var_dump($orderInfo);
        $res=false;
        foreach ($orderInfo as $k=>$v){
            echo '1'."\n";
            if($v['order_status']==1){
                echo '2'."\n";
                if(time()-$v['c_time'] > 300){
                    echo '3'."\n";
                    $Orderwhere=['order_num'=>$v['order_num']];
                    $data=[
                        'order_status'=>3
                    ];
                    $res=OrderModel::where($Orderwhere)->update($data);
                }
            }
        }
//        if($res!==false){
//            echo "Success"."\n";
//        }else{
//            echo 'Error';exit;
//        }

    }





}
