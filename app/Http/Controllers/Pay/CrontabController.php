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
            'uid'=>$uid
        ];
        $orderInfo=where($where)->get();
        if(empty($orderInfo)){
            exit('还没有下单');
        }
        foreach ($orderInfo as $k=>$v){
            if($v['order_status']==1){
                if(time()-$v['c_time'] > 300){
                    $Orderwhere=['order_num'=>$v['order_num']];
                    $data=[
                        'order_status'=>3
                    ];
                    $res=OrderModel::where($Orderwhere)->update($data);
                    $detailInfo=OrderDetailModel::where($Orderwhere)->get();
                    foreach ($detailInfo as $k=>$v) {
                        $info=[
                            'status'=>2
                        ];
                        $res2=OrderDetailModel::where($Orderwhere)->update($info);
                    }
                }
            }
        }
        echo "success"."\n";
    }





}
