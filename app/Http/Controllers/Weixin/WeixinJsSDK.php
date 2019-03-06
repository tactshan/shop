<?php

namespace App\Http\Controllers\Weixin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;

class WeixinJsSDK extends Controller
{
    protected $redis_weixin_access_token_jssdk = 'str:weixin_access_token_jssdk';     //微信 access_token
    protected $redis_weixin_jsapi_ticket = 'str:weixin_jsapi_ticket';     //微信 jsapi_ticket
    public function wxJsSdk()
    {
        $jsconfig=[
            'openid'=>env('WEIXIN_JSSDK_APPID'),
            'timestamp'=>time(),
            'noncestr'=>str_random(10),
//            'sign'      => $this->wxJsConfigSign()
        ];
        $sign=$this->wxJsConfigSign($jsconfig);
        $jsconfig['sign'] = $sign;
        $data=[
           'jsinfo'=>$jsconfig
        ];
//        var_dump($data);exit;
        return view('weixin.wx_jssdk',$data);
    }

    /**
     * 计算签名
     * @return string
     */
    public function wxJsConfigSign($param)
    {
        $current_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];     //当前调用 jsapi的 url
        $ticket=$this->getJsapiTicket();
        $str =  'jsapi_ticket='.$ticket.'&noncestr='.$param['noncestr']. '&timestamp='. $param['timestamp']. '&url='.$current_url;
        $signature=sha1($str);
        return $signature;
    }

    /**
     * 获取微信AccessToken
     */
    public function getWXAccessToken()
    {
        //获取缓存
        $token = Redis::get($this->redis_weixin_access_token_jssdk);
        if(empty($token)){        // 无缓存 请求微信接口
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WEIXIN_JSSDK_APPID').'&secret='.env('WEIXIN_JSSDK_APPSECRET');
            $data = json_decode(file_get_contents($url),true);
            //记录缓存
            $token = $data['access_token'];
            Redis::set($this->redis_weixin_access_token_jssdk,$token);
            Redis::setTimeout($this->redis_weixin_access_token_jssdk,3600);
        }
        return $token;
    }

    /**
     * 刷新access_token
     */
    public function refreshToken()
    {
        Redis::del($this->redis_weixin_access_token_jssdk);
        echo $this->getWXAccessToken();
    }

    /**
     * 获取jsapi-ticket
     * @return mixed
     */
    public function getJsapiTicket()
    {
        //获取缓存
        $ticket= Redis::get($this->redis_weixin_jsapi_ticket);
        if(empty($ticket)){
            $token=$this->getWXAccessToken();
            $url='https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$token.'&type=jsapi';
            $data = json_decode(file_get_contents($url),true);
            //记录缓存
            $ticket = $data['ticket'];
            Redis::set($this->redis_weixin_jsapi_ticket,$ticket);
            Redis::setTimeout($this->redis_weixin_jsapi_ticket,3600);
        }
        return $ticket;
    }

    public function test()
    {
        return view('weixin.test');
    }
}
