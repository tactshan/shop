<?php

namespace App\Http\Controllers\Weixin;

use App\Model\UserModel;
use App\Model\WeixinChatRecord;
use App\Model\WeixinMaterial;
use App\Model\WeixinUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp;

class WeixinController extends Controller
{
    protected $redis_weixin_access_token = 'str:weixin_access_token';     //微信 access_token

    /**
     * 接收微信服务器事件推送
     */
    public function wxEvent()
    {
        $data = file_get_contents("php://input");//获取流的形式获取值(数据类型是一个xml字符串)
        //处理xml字符串
        $xml_str=simplexml_load_string($data);  //得到一个处理后的对象类型
        //获取事件类型
        $event= $xml_str->Event;    //subscribe关注   unsubscribe取消关注 click公众号点击事件

        //处理微信接受用户消息，自动回复
        if(isset($xml_str->MsgType)){
            //获取openid
            $openid=$xml_str->FromUserName;
            //获取用户微信信息
            $toUserName=$xml_str->ToUserName;

            //用户发送文字
            if($xml_str->MsgType=='text'){
                //保存到聊天记录表中
                $msg=$xml_str->Content;
                $Chat_record=[
                    'openid'=>$openid,
                    'content'=>$msg,
                    'send_people'=>$openid,
                    'send_time'=>time()
                ];
                $res=WeixinChatRecord::insertGetId($Chat_record);
        }

            //用户发送图片
            if($xml_str->MsgType=='image'){
                //获取media_id
                $media_id=$xml_str->MediaId;
                //保存图片到本地|服务器
                $res=$this->saveMaterialLocal($media_id,'image');
                //保存素材到数据库
                $res2=$this->saveMaterial($xml_str,$res);
                if($res&&$res2){
                    $hint='我们已经收到你的图片啦！点击'.'<a href="'.$res['file_path'].'">'.'查看'.'</a>';   //hint  提示
                }else{
                    $hint='很遗憾，您的图片我们没收到.....请稍后重试！';
                }
                $xmlStrResopnse='<xml>
                <ToUserName><![CDATA['.$openid.']]></ToUserName>
                <FromUserName><![CDATA['.$toUserName.']]></FromUserName>
                <CreateTime>'.time().'</CreateTime>
                <MsgType><![CDATA[text]]></MsgType>
                <Content><![CDATA['.$hint.']]></Content>
                </xml>';
                echo $xmlStrResopnse;
            }

            //用户发送语音
            if($xml_str->MsgType=='voice'){
                //获取media_id
                $media_id=$xml_str->MediaId;
                //保存语音到本地|服务器
                $res=$this->saveMaterialLocal($media_id,'voice');
                //类型
                //保存素材到数据库
                $res2=$this->saveMaterial($xml_str,$res);
                if($res&&$res2){
                    $hint=$res['file_path'];   //hint  提示
                }else{
                    $hint='很遗憾，您的语音我们没收到.....请稍后重试！';
                }
                $xmlStrResopnse='<xml>
                <ToUserName><![CDATA['.$openid.']]></ToUserName>
                <FromUserName><![CDATA['.$toUserName.']]></FromUserName>
                <CreateTime>'.time().'</CreateTime>
                <MsgType><![CDATA[text]]></MsgType>
                <Content><![CDATA['.$hint.']]></Content>
                </xml>';
                echo $xmlStrResopnse;
            }

            //用户发送视频
            if($xml_str->MsgType=='video'){
                //获取media_id
                $media_id=$xml_str->MediaId;
                //保存图片到本地|服务器
                $res=$this->saveMaterialLocal($media_id,'video');
                //保存素材到数据库
                $res2=$this->saveMaterial($xml_str,$res);
                if($res&&$res2){
                    $hint='我们已经收到你的视频啦！';   //hint  提示
                }else{
                    $hint='很遗憾，您的视频我们没收到.....请稍后重试！';
                }
                $xmlStrResopnse='<xml>
                <ToUserName><![CDATA['.$openid.']]></ToUserName>
                <FromUserName><![CDATA['.$toUserName.']]></FromUserName>
                <CreateTime>'.time().'</CreateTime>
                <MsgType><![CDATA[text]]></MsgType>
                <Content><![CDATA['.$hint.']]></Content>
                </xml>';
                echo $xmlStrResopnse;
            }
        }

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
            if(!empty($userData)){
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
        $xmlStrResopnse='<xml>
                <ToUserName><![CDATA['.$openid.']]></ToUserName>
                <FromUserName><![CDATA['.$toUserName.']]></FromUserName>
                <CreateTime>'.time().'</CreateTime>
                <MsgType><![CDATA[text]]></MsgType>
                <Content><![CDATA['.$content.']]></Content>
                </xml>';
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
     * 群发送消息
     */
    public function GroupSending(){
        //获取access_token
        $access_token=$this->getWXAccessToken();
        //拼接url
        $url='https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token='.$access_token;
        //请求微信接口
        $client = new GuzzleHttp\Client(['base_uri' => $url]);
        //拼接数据
        $userInfo=WeixinUser::all()->toArray();
        foreach ($userInfo as $k=>$v){
            $openid[]=$v['openid'];
        }
        $data=[
            'touser'=>$openid,
            "msgtype"=>"text",
            "text"=>["content"=>"来自Tactshan的祝福！"],
        ];
        $res=$client->request('POST', $url, ['body' => json_encode($data,JSON_UNESCAPED_UNICODE)]);
        $res_arr=json_decode($res->getBody(),true);
        if($res_arr['errcode']==0){
            echo '群发成功';
        }else{
            echo '群发失败！错误码'.$res_arr['errmsg'];
        }
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

    /**
     * 保存用户发送的素材到本地
     * @param $mediaId
     * @return bool
     */
    public function saveMaterialLocal($mediaId,$type){
        $client=new GuzzleHttp\Client();
        //获取access_token
        $access_token=$this->getWXAccessToken();
        //拼接下载图片的url  https://api.weixin.qq.com/cgi-bin/media/get?access_token=ACCESS_TOKEN&media_id=MEDIA_ID
        $url='https://api.weixin.qq.com/cgi-bin/media/get?access_token='.$access_token.'&media_id='.$mediaId;

        //使用GuzzleHttp下载文件
        $response=$client->get($url);

        //获取文件名称
        $file_info = $response->getHeader('Content-disposition');
        // string(91) "attachment; filename="naj5JLd6yeW1dLiIxlaNCv5AceOAyuCYt1EVcBWr8ky5FO48dIAarm_pDvbNDy25.jpg""
        $file_name=substr(rtrim($file_info[0],'"'),-20);
        //dIAarm_pDvbNDy25.jpg

        //判断文件类型，确定保存路径
        if($type=='image'){
            $localPath='wx/images/';
        }else if($type=='voice'){
            $localPath='wx/voice/';
        }else if($type=='video'){
            $localPath='wx/video/';
        }
        $WxImageSavePath=$localPath.$file_name;
        //保存路径/home/wwwroot/shop/storage/app/wx/images
        //保存图片
        $res = Storage::disk('local')->put($WxImageSavePath,$response->getBody());
        $saveInfo=[
          'file_name'=>$file_name,
          'file_path'=>$url
        ];
        if($res){     //保存成功
            return $saveInfo;
        }else{      //保存失败
            return false;
        }
    }

    /**
     * 刷新access_token
     */
    public function refreshToken()
    {
        Redis::del($this->redis_weixin_access_token);
        echo $this->getWXAccessToken();
    }

    /**
     * 保存素材到数据库
     * @param $xml_str
     * @param $saveInfo
     * @return bool
     */
    public function saveMaterial($xml_str,$saveInfo){
        //https://shop.tactshan.com/wx/images/_ia27aqZeswBWrWR.jpg
        //http://www.vmshop.com/wx/images/dIAarm_pDvbNDy25.jpg
        $materialData=[
            'openid'=>$xml_str->FromUserName,
            'add_time'=>$xml_str->CreateTime,
            'msg_type'=>$xml_str->MsgType,
            'media_id'=>$xml_str->MediaId,
            'format'=> $xml_str->Format,
            'msg_id'=>$xml_str->MsgId,
            'file_name'=>$saveInfo['file_name'],
            'file_path'=>$saveInfo['file_path']
        ];
        $res3=WeixinMaterial::insertGetId($materialData);
        if($res3){
            return true;
        }else{
            return false;
        }
    }

    //微信用户列表
    public function wxUserList(){
        $info=DB::table('p_wx_users')->paginate(5);
        $data=[
            'info'=>$info,
        ];
        return view('weixin.wx_users_list',$data);
    }
    //微信互动视图
    public function interactView($openid,$nickname){
        $data=[
            'openid'=>$openid,
            'nickname'=>$nickname
        ];
        return view('weixin.wx_interact_view',$data);
    }
    //发消息给指定用户
    public function wxInteract(){
        $openid=$_POST['openid'];
        $content=$_POST['content'];
        //获取access_token
        $access_token=$this->getWXAccessToken();
        $url='https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$access_token;
        //请求微信接口
        $client = new GuzzleHttp\Client(['base_uri' => $url]);
        $data=[
            "touser"=>$openid,
            'msgtype'=>'text',
            'text'=>[
                "content"=>$content
            ]
        ];
        $res=$client->request('POST', $url, ['body' => json_encode($data,JSON_UNESCAPED_UNICODE)]);
        $res_arr=json_decode($res->getBody(),true);
        if($res_arr['errcode']==0&&$res_arr['errmsg']=='ok'){
            //将聊天记录保存到数据库
            $data=[
              'openid'=>$openid,
              'content'=>$content,
              'send_people'=>'客服',
              'send_time'=>time()
            ];
            $res=WeixinChatRecord::insertGetId($data);
            var_dump($res);
        }
    }
    //实时获取聊天记录表中数据
    public function getWxChatRecord(){
        $openid=$_POST['openid'];
        $where=[
          'openid'=>$openid,
        ];
        $info=WeixinChatRecord::where($where)->OrderBy('send_time','asc')->get();
        echo json_encode($info);
    }

    /**
     * Weixin 登录
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function login()
    {
//        echo urlencode('http://shop.tactshan.com/weixin/getcode');
        return view('weixin.login');
    }

    /**
     * 获取code
     */
    public function getCode(Request $request)
    {
        //1获取code(微信登录成功，即获得code)
        $code=$_GET['code'];
        //2根据code获取access_token 和 openid
        $token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=wxe24f70961302b5a5&secret=0f121743ff20a3a454e4a12aeecef4be&code='.$code.'&grant_type=authorization_code';
        $token_json = file_get_contents($token_url);
        $token_arr = json_decode($token_json,true);

        $access_token = $token_arr['access_token'];
        $openid = $token_arr['openid'];

        // 3 携带token  获取用户信息
        $user_info_url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
        $user_json = file_get_contents($user_info_url);

        $user_arr = json_decode($user_json,true);
        //处理微信数据
        $where=[
            'openid'=>$user_arr['openid']
        ];
        $userInfo=WeixinUser::where($where)->first();
        $token = substr(md5(time().mt_rand(1,99999)),10,10);
        if(empty($userInfo)){
            //添加入库
            //添加users表
            $user_data=[
                'name'=>'wx_'.str_random(5)
            ];
            $uid=UserModel::insertGetId($user_data);
            //添加wx_user表
            $info=[
                'uid'=>$uid,
                'openid'=>$user_arr['openid'],
                'add_time'=>time(),
                'nickname'=>$user_arr['nickname'],
                'sex'=>$user_arr['sex'],
                'headimgurl'=>$user_arr['headimgurl'],
                'subscribe_time'=>time(),
                'add_time'=>time(),
            ];
            $id=WeixinUser::insertGetId($info);
            echo '111';exit;
//            if(!empty($id)){
//                $request->session()->put('uid',$uid);
//                setcookie('cookie_token',$token,time()+86400,'','',false,true);
//                $request->session()->put('u_token',$token);
//                header("refresh:2;url=/goodslist");exit;
//            }
        }else{
            $uid=$userInfo->uid;
            $request->session()->put('uid',$uid);
            setcookie('cookie_token',$token,time()+86400,'','',false,true);
            $request->session()->put('u_token',$token);
            header("refresh:2;url=/goodslist");exit;
            echo '222';exit;
        }
    }
}
