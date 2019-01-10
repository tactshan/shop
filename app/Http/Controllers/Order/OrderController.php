<?php

namespace App\Http\Controllers\Order;

use App\Model\CartModel;
use App\Model\GoodsModel;
use App\Model\OrderModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    //生成订单
    public function createOrder(){
        //根据用户id查询该用户下的购物车信息
        $uid=session()->get('uid');
        $where=[
          'user_id'=>$uid
        ];
        $cartDate=CartModel::where($where)->get()->toArray();
        //求总价钱
        $order_amount=0;//单位 分
        foreach ($cartDate as $k=>$v){
            $goodsDate=GoodsModel::where(['goods_id'=>$v['goods_id']])->first()->toArray();
            $goodsDate['buy_number']=$v['buy_number'];
            $list[] = $goodsDate;
            $order_amount+=$goodsDate['goods_price']*$v['buy_number'];
        }
        print_r($list);exit;
        //生成订单
        $order_num=OrderModel::generateOrderSN();
        $data = [
            'order_num'      => $order_num,
            'user_id'           => session()->get('uid'),
            'c_time'      => time(),
            'order_amount'  => $order_amount
        ];
        $res = OrderModel::insertGetId($data);
        if($res){
            echo '下单成功,订单号：'.$order_num .' 跳转支付';
            //清空购物车
            CartModel::where(['user_id'=>session()->get('uid')])->delete();
        }else{
            exit('生成订单失败');
        }
    }
}
