<?php

namespace App\Http\Controllers\Weixin;

use App\Model\WeixinUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Redis;
use GuzzleHttp;

class WeixinController extends Controller
{
    //

    protected $redis_weixin_access_token = 'str:weixin_access_token';     //微信 access_token

    public function test()
    {
        //echo __METHOD__;
        //$this->getWXAccessToken();
        $this->getUserInfo(1);
    }

    /**
     * 首次接入
     */
    public function validToken1()
    {
        //$get = json_encode($_GET);
        //$str = '>>>>>' . date('Y-m-d H:i:s') .' '. $get . "<<<<<\n";
        //file_put_contents('logs/weixin.log',$str,FILE_APPEND);
        echo $_GET['echostr'];
    }


    /**
     * 接收微信服务器事件推送
     */
    public function wxEvent()
    {
        $data = file_get_contents("php://input");//获取流的形式获取值(数据类型是一个xml字符串)
        //处理xml字符串
        $xml_str=simplexml_load_string($data);  //得到一个处理后的对象类型
        //获取事件类型
        $event= $xml_str->Event;    //subscribe关注   unsubscribe取消关注
        //判断事件类型----关注和取消关注
        if($event=='subscribe'){
            //获取openid
            $openid=$xml_str->FromUserName;
            //获取扫描时间
            $sub_time=$xml_str->CreateTime;
            //根据openid获取用户信息
            $userInfo=$this->getUserInfo($openid);
//            var_dump($userInfo);die;
            //保存用户信息
            $userData=WeixinUser::where(['openid'=>$openid])->first();
            if($userData){
                $upData=[
                    'status'=>1
                ];
                $res=WeixinUser::where(['openid'=>$openid])->update($upData);
                $str='老用户重新关注'.$res;
                var_dump($str);
            }else{
                $user_data = [
                    'openid'            => $userInfo['openid'],
                    'add_time'          => time(),
                    'nickname'          => $userInfo['nickname'],
                    'sex'               => $userInfo['sex'],
                    'headimgurl'        => $userInfo['headimgurl'],
                    'subscribe_time'    => $sub_time
                ];
                $id = WeixinUser::insertGetId($user_data);      //保存用户信息
                $str='新用户关注'.$id;
                var_dump($str);
            }
        }else if($event=='unsubscribe'){
            //用户取消关注    进行修改
            $openid=$xml_str->FromUserName;
            $where=[
              'openid'=>$openid
            ];
            $upData=[
              'status'=>2
            ];
            $res=WeixinUser::where($where)->update($upData);
            $str='用户取消关注'.$res;
            var_dump($str);
        }else if($event=='CLICK'){
            //判断事件类型----公众号事件(点击自动回复)
            if($xml_str->EventKey=='get_content'){
                $openid=$xml_str->FromUserName;
                $toUserName=$xml_str->ToUserName;
                $this->getContent($openid,$toUserName);
            }
        }
        $log_str = date('Y-m-d H:i:s') . "\n" . $data . "\n<<<<<<<";
        file_put_contents('logs/wx_event.log',$log_str,FILE_APPEND);
    }

    /**
     * 自动回复
     */
    public function getContent($openid,$toUserName){
        $time=time();
        $date=date("Y/m/d H:i:s");
        $content='你好，我是Tactshan！温馨提示您当前时间为'.$date;
        $xmlStrResopnse="<xml>
                    <ToUserName>< ![CDATA[".$openid."] ]></ToUserName>
                    <FromUserName>< ![CDATA[".$toUserName."] ]></FromUserName>
                    <CreateTime>".$time."</CreateTime>
                    <MsgType>< ![CDATA[text] ]></MsgType>
                    <Content>< ![CDATA[".$content."] ]></Content>
                    </xml>";
        echo $xmlStrResopnse;
    }

    /**
     * 接收事件推送
     */
    public function validToken()
    {
        $data = file_get_contents("php://input");
        var_dump($data);exit;
        $log_str = date('Y-m-d H:i:s') . "\n" . $data . "\n<<<<<<<";
        file_put_contents('logs/wx_event.log',$log_str,FILE_APPEND);
    }

    /**
     * 获取微信AccessToken
     */
    public function getWXAccessToken()
    {
        //获取缓存
        $token = Redis::get($this->redis_weixin_access_token);
        if(!$token){        // 无缓存 请求微信接口
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WEIXIN_APPID').'&secret='.env('WEIXIN_APPSECRET');
            $data = json_decode(file_get_contents($url),true);
            //记录缓存
            $token = $data['access_token'];
            Redis::set($this->redis_weixin_access_token,$token);
            Redis::setTimeout($this->redis_weixin_access_token,3600);
        }
        return $token;
    }

    /**
     * 获取用户信息
     * @param $openid
     */
    public function getUserInfo($openid)
    {
//        echo $openid;exit;
        $access_token = $this->getWXAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';

        $data = json_decode(file_get_contents($url),true);
        return $data;
//        echo '<pre>';print_r($data);echo '</pre>';die;
    }

    /**
     * 自定义菜单创建
     */
    public function createMenu(){
        //获取access_token
        $access_token=$this->getWXAccessToken();
        //拼接url
        $url='https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$access_token;
        //请求微信接口
        $client = new GuzzleHttp\Client(['base_uri' => $url]);

        //拼接菜单数据
        $data=[
          "button" => [
              [
                  "type"=>"click",
                  "name"=>"获取自动回复",
                  "key"=>"get_content"
              ],
              [
                 'name'=>"扫码",
                  'sub_button'=>[
                      [
                          "type"=>"scancode_waitmsg",
                          "name"=>"扫码",
                          "key"=>"rselfmenu_0_0",
                          "sub_button"=> [ ]
                      ],
                      [
                          'type'=>'pic_sysphoto',
                          'name'=>'拍照发图',
                          'key'=>"rselfmenu_1_0",
                          "sub_button"=> [ ]
                      ],
                      [
                          'type'=>'pic_photo_or_album',
                          'name'=>'拍照或者相册发图',
                          'key'=>"rselfmenu_1_1",
                          "sub_button"=> [ ]
                      ],
                      [
                          'type'=>'pic_weixin',
                          'name'=>'微信相册发图',
                          'key'=>"rselfmenu_1_2",
                          "sub_button"=> [ ]
                      ]
                  ]
              ],
              [
                 "name"=>"个人中心",
                  "sub_button"=>[
                      [
                          "type"=>"location_select",
                          "name"=>"发送位置",
                          "key"=> "rselfmenu_2_0"
                      ]
                  ]
              ]
          ]
        ];
        $res=$client->request('POST', $url, ['body' => json_encode($data,JSON_UNESCAPED_UNICODE)]);
        $res_arr=json_decode($res->getBody(),true);
        if($res_arr['errcode']==0){
            echo '菜单创建成功';
        }else{
            echo '菜单创建失败！错误码'.$res_arr['errmsg'];
        }
    }
}
