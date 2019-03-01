<?php

namespace App\Http\Controllers\Weixin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WeixinJsSDK extends Controller
{
    public function wxJsSdk()
    {
        $jsconfig=[
            'openid'=>env('WEIXIN_JSSDK_APPID'),
            'timestamp'=>time(),
            'nonceStr'=>str_random(10),
            'sign'      => $this->wxJsConfigSign()
        ];
        $data=[
           'jsinfo'=>$jsconfig
        ];
        return view('weixin.wx_jssdk',$data);
    }

    /**
     * 计算签名
     * @return string
     */
    public function wxJsConfigSign()
    {
        $sign = str_random(15);
        return $sign;
    }
}
