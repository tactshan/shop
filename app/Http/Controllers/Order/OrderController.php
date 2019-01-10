<?php

namespace App\Http\Controllers\Order;

use App\Model\CartModel;
use App\Model\GoodsModel;
use App\Model\OrderDetailModel;
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
        $order_num=OrderModel::generateOrderSN();
        foreach ($cartDate as $k=>$v){
            $goodsDate=GoodsModel::where(['goods_id'=>$v['goods_id']])->first()->toArray();
            //减少库存
            $goodsDate['goods_stock']=$goodsDate['goods_stock']-$v['buy_number'];
            $upWhere=[
              'goods_id'=>$v['goods_id']
            ];
            $res=GoodsModel::where($upWhere)->update($goodsDate);
            if($res){
                //补全订单详情
                $goodsDate['buy_number']=$v['buy_number'];
                $goodsDate['order_num']=$order_num;
                $goodsDate['user_id']=$uid;
                unset($goodsDate['goods_stock']);
                $list[] = $goodsDate;
            }
            //求总价钱
            $order_amount+=$goodsDate['goods_price']*$v['buy_number'];
        }
        //生成订单
        $data = [
            'order_num'      => $order_num,
            'user_id'           => session()->get('uid'),
            'c_time'      => time(),
            'order_amount'  => $order_amount
        ];
        $res = OrderModel::insertGetId($data);
        //生成订单详情
        unset($list['goods_stock']);
        $res2=OrderDetailModel::insert($list);
        if($res&&$res2){
            //减少库存
            if($res){
                //清空购物车
                CartModel::where(['user_id'=>session()->get('uid')])->delete();
                header("refresh:0;url=/orderdetail/$order_num");
            }else{
                exit('生成订单失败');
            }
        }
    }
    public function orderDetail($order_num){
        $uid=session()->get('uid');
        $showWhere=[
            'user_id'=>$uid,
            'order_num'=>$order_num
        ];
        $order_detail=OrderDetailModel::where($showWhere)->get();
        $data=[
            'order_num'=>$order_num,
            'info'=>$order_detail
        ];
        return view('order.orderdetail',$data);
    }
    public function allOrders(){
        $uid=session()->get('uid');
        $where=[
          'user_id'=>$uid
        ];
        $orderDate=OrderModel::where($where)->get();
        $data=[
            'uid'=>$uid,
          'data'=>$orderDate
        ];
        return view('order.allorders',$data);
    }
}
