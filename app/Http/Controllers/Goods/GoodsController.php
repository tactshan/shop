<?php

namespace App\Http\Controllers\Goods;

use App\Model\GoodsModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class GoodsController extends Controller
{
    //商品列表展示
    public function goodsList(){
        if(!empty($_GET['key'])){
            $key=$_GET['key'];
        }else{
            $key='';
        }

        $cacheKey='info';

        if(Redis::exists($cacheKey)){
            $res = Redis::get($cacheKey);
            $info = unserialize($res);
        }else{
            $info=DB::table('shop_goods')->where('goods_name','like',"%$key%")->paginate(2);
        }

        //存redis
        Redis::setex($cacheKey, 600, serialize($info));

        $uid=session()->get('uid');
        $data=[
            'info'=>$info,
            'uid'=>$uid,
            'key'=>$key
        ];
        return view('goods.goodslist',$data);
    }
    //商品列表展示搜索分页
}
