<?php

namespace App\Http\Controllers\Cart;

use App\Model\CartModel;
use App\Model\GoodsModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CartController extends Controller
{
    //购物车添加
    public function cartAdd($goods_id)
    {
        $where=[
          'goods_id'=>$goods_id
        ];
        $goodsInfo=GoodsModel::where($where)->first();
        $data=[
          'goodsInfo'=>$goodsInfo
        ];
        return view('cart.cartadd',$data);

    }
    public function cartAddDo(Request $request){
        $goods_id=$request->input('goods_id');
        $buy_number=$request->input('buy_number');
        $uid=$_COOKIE['uid'];
        $session_token=$request->session()->get('u_token');
        $where=[
          'goods_id'=>$goods_id
        ];
        $goodsData=GoodsModel::where($where)->first()->toArray();
        if($goodsData['goods_stock']<$buy_number){
            exit('库存不足');
        }
        $cartWhere=[
          'goods_id'=>$goods_id,
          'user_id'=>$uid
        ];
        $cartData=CartModel::where($cartWhere)->first()->toArray();
        if(!empty($cartData)){
            //该商品已存在该用户的购物车中--做累加
            $cart_id=$cartData['cart_id'];
            $upWhere=[
              'cart_id'=>$cart_id
            ];
            $data=[
                'goods_id'=>$goods_id,
                'buy_number'=>$cartData['buy_number']+$buy_number,
                'c_time'=>time(),
                'user_id'=>$uid,
                'session_token'=>$session_token
            ];
            $res=CartModel::where($upWhere)->update($data);
        }else{
            //添加购物车
            $data=[
                'goods_id'=>$goods_id,
                'buy_number'=>$buy_number,
                'c_time'=>time(),
                'user_id'=>$uid,
                'session_token'=>$session_token
            ];
            $res=CartModel::insertGetId($data);
        }
        if($res){
            //减少库存
                $goodsData['goods_stock']=$goodsData['goods_stock']-$buy_number;
                $res=GoodsModel::where($where)->update($goodsData);
            //添加成功
                if($res){
                    echo '添加成功';exit;
                }
        }
    }
    //购物车列表展示
    public function cartList(Request $request)
    {
        $uid=$_COOKIE['uid'];
        $where=[
          'user_id'=>$uid
        ];
        $cart_goods=CartModel::where($where)->get();
        $cart_goods=[
          'info'=>$cart_goods,
            'uid'=>$uid
        ];
        return view('cart.cartlist',$cart_goods);
    }
    //删除购物车
    public function delCartInfo($goods_id){
        //根据商品id查看是否存在购物车中
        $cart_goods=session()->get('cart_goods');

        if(!empty($cart_goods)){
//            if(!in_array($goods_id,$cart_goods)){
//                exit('删除失败！');
//            }else{
                foreach ($cart_goods as $k=>$v){
                    if($goods_id == $v['goods_id'] ){
                        session()->pull('cart_goods.'.$k);
                        echo '删除成功';
                        header("refresh:3;url=/cartlist");
                    }
                }
//            }
        }
    }
}
