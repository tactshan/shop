<?php

namespace App\Http\Controllers\Cart;

use App\Model\CartModel;
use App\Model\GoodsModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CartController extends Controller
{
    /**
     * 购物车添加视图
     * @param $goods_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
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

    /**
     * 购物车添加
     * @param Request $request
     */
    public function cartAddDo(Request $request){
        $goods_id=$request->input('goods_id');
        $buy_number=$request->input('buy_number');
        $uid=session()->get('uid');
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
        $cartData=CartModel::where($cartWhere)->first();
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

    /**
     * 购物车列表展示
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function cartList(Request $request)
    {
        $uid=session()->get('uid');
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

    /**
     * 删除购物车
     * @param $goods_id
     */
    public function delCartInfo($goods_id){
        $uid=session()->get('uid');
        $where=[
          'user_id'=>$uid,
          'goods_id'=>$goods_id
        ];
        $cartGoodsData=CartModel::where($where)->first()->toArray();
        $res=CartModel::where($where)->delete();
        if($res){
            //归还库存
            $buy_number=$cartGoodsData['buy_number'];
            $where=[
              'goods_id'=>$goods_id
            ];
            $goodsData=GoodsModel::where($where)->first()->toArray();
            $goodsData['goods_stock']=$goodsData['goods_stock']+$buy_number;
            $res=GoodsModel::where($where)->update($goodsData);
            if($res){
                echo ('删除成功');
                header("refresh:2;url=/cartlist");
            }
        }
    }
}
