<?php

namespace App\Http\Controllers\Goods;

use App\Model\GoodsModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GoodsController extends Controller
{
    //商品列表展示
    public function goodsList(){
        $info=GoodsModel::all();
        $uid=session()->get('uid');
        $data=[
            'info'=>$info,
            'uid'=>$uid
        ];
        return view('goods.goodslist',$data);
    }
}
