<?php

namespace App\Http\Controllers\Weixin;

use App\Model\OrderDetailModel;
use App\Model\OrderModel;
use App\Model\UserModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PayController extends Controller
{
    public $weixin_unifiedorder_url = 'https://api.mch.weixin.qq.com/pay/unifiedorder'; //微信支付接口
    public $weixin_notify_url = 'https://shop.tactshan.com/weixin/pay/notice';     //支付通知回调

    public function wxPay($order_num)
    {
        $total_fee= 1;
        $order_info = [
            'appid'         =>  env('WEIXIN_APPID_0'),      //微信支付绑定的服务号的APPID
            'mch_id'        =>  env('WEIXIN_MCH_ID'),       // 商户ID
            'nonce_str'     => str_random(16),             // 随机字符串
            'sign_type'     => 'MD5',
            'body'          => 'Tactshan的订单测试',
            'out_trade_no'  => $order_num,                       //本地订单号
            'total_fee'     => $total_fee,
            'spbill_create_ip'  => $_SERVER['REMOTE_ADDR'],     //客户端IP
            'notify_url'    => $this->weixin_notify_url,        //通知回调地址
            'trade_type'    => 'NATIVE'                         // 交易类型
        ];
        $this->values = [];
        $this->values = $order_info;
        //将签名添加到数组中
        $this->SetSing();
        $xml = $this->ToXml();      //将数组转换为XML
        $res = $this->postXmlCurl($xml, $this->weixin_unifiedorder_url, $useCert = false, $second = 30);
        $data =  simplexml_load_string($res);
        $code_url=$data->code_url;
        $info=[
          'code_url'=>$code_url,
            'order_num'=>$order_num
        ];
        return view('pay.wx_pay_code',$info);
    }
    public function SetSing()
    {
        $sign=$this->MakeSing();
        $this->values['sign'] = $sign;
        return $sign;
    }
    public function MakeSing()
    {
        //签名步骤一：按字典序排序参数
        ksort($this->values);
        $string = $this->ToUrlParams();
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".env('WEIXIN_MCH_KEY');
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }
    /**
     * 格式化参数格式化成url参数
     */
    public function ToUrlParams()
    {
        $buff = "";
        foreach ($this->values as $k => $v)
        {
            if($k != "sign" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }
    //转化数据
    public function ToXml()
    {
        if(!is_array($this->values)||count($this->values)<= 0){
            die("数组数据异常！");
        }
        $xml = "<xml>";
        foreach ($this->values as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }
    public function postXmlCurl($xml, $url, $useCert = false, $second = 30)
    {
        $ch =curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            die("curl出错，错误码:$error");
        }
    }

    /**
     * 微信支付回调
     */
    public function notice()
    {
        $data = file_get_contents("php://input");
        //记录日志
        $log_str = date('Y-m-d H:i:s') . "\n" . $data . "\n<<<<<<<";
        file_put_contents('logs/wx_pay_notice.log',$log_str,FILE_APPEND);

        $xml = (array)simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
        if($xml['result_code']=='SUCCESS' && $xml['return_code']=='SUCCESS'){      //微信支付成功回调
            //验证签名
            $sign = $this->check_sign($xml);
            if($sign){       //签名验证成功
                //记录日志
                $log_str = '>>>> ' . date('Y-m-d H:i:s');
                if($sign === false){
                    //记录日志 验签失败
                    $log_str .= " Sign Failed!<<<<< \n\n";
                    file_put_contents('logs/wx_pay.log',$log_str,FILE_APPEND);
                }else{
                    $log_str .= " Sign OK!<<<<< \n\n";
                    file_put_contents('logs/wx_pay.log',$log_str,FILE_APPEND);
                }
                //逻辑处理  订单状态更新
                $res=$this->dealOrder($xml);
                if($res){
                    $order_num=$xml['out_trade_no'];
                    header("refresh:1;url='/orderdetail/$order_num");
                }else{
                    die('Error');
                }
            }else{
                //TODO 验签失败
                echo '验签失败，IP: '.$_SERVER['REMOTE_ADDR'];
                // TODO 记录日志
            }
        }
        $response = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
        echo $response;
    }
    //验签
    public function check_sign($xml)
    {
        $this->values = [];
        $this->values = $xml;
        $sign=$this->SetSing();
        if($sign!==$xml['sign']){
            return false;
        }else{
            return true;
        }
    }
    //处理订单逻辑
    function dealOrder($data){
        $order_num=$data['out_trade_no'];
        $orderWhere=[
            'order_num'=>$order_num
        ];
        $orderData=OrderModel::where($orderWhere)->first()->toArray();
        if(empty($orderData)){
            die("订单 ".$order_num. "不存在！");
        }
        $order_status=$orderData['order_status'];
        if($order_status!=1){
            die("此订单已被支付或订单异常。");
        }

        $order_amount=$orderData['order_amount'];
        //更改订单状态
        $where=[
            'order_num'=>$order_num
        ];
        $data=[
            'order_status'=>2
        ];
        $res=OrderModel::where($where)->update($data);
        //根据订单号获查询订单表获取到用户id
        $uid=$orderData['user_id'];
        //赠送积分
        $userWhere=[
            'uid'=>$uid
        ];
        $userData=UserModel::where($userWhere)->first()->toArray();
        $userData['integral']=$userData['integral']+$order_amount;
        $res2=UserModel::where($userWhere)->update($userData);
        if($res&&$res2){
            return true;
        }
    }
    //ajax实时检测订单状态
    public function find()
    {
        $order_num=$_POST['order_num'];
        $where=[
          'order_num'=>$order_num
        ];
        $data=OrderModel::where($where)->first()->toArray();
        if($data['order_status']==2){
            echo 1;
        }
    }
}
