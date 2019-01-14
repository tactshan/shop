<?php

namespace App\Http\Controllers\Pay;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use GuzzleHttp\Client;

class AlipayController extends Controller
{
    public $app_id = '2016092200571842';
    public $gate_way = 'https://openapi.alipaydev.com/gateway.do';
    public $notify_url = 'http://shop.comcto.com/pay/alipay/notify';
    public $rsaPrivateKeyFilePath = './key/priv.key';

    /**
     * 请求订单服务 处理订单逻辑
     *
     */
    public function test0()
    {
        //
        $url = 'http://vm.order.lening.com';
       // $client = new Client();
        $client = new Client([
            'base_uri' => $url,
            'timeout'  => 2.0,
        ]);

        $response = $client->request('GET', '/order.php');
        echo $response->getBody();
    }


    public function test($order_num)
    {
        $orderWhere=[
            'order_num'=>$order_num
        ];
        $orderData=OrderModel::where($orderWhere)->first()->toArray();
//业务请求参数
        $bizcont = [
            'subject'           => 'ancsd'. mt_rand(1111,9999).str_random(6), //订单信息
            'out_trade_no'      => $orderData['order_num'],  //订单号
            'total_amount'      => $orderData['order_amount']/100,                 //金额
            'product_code'      => 'QUICK_WAP_WAY',  //销售产品码，商家和支付宝签约的产品码，为固定值QUICK_MSECURITY_PAY
        ];
//$data 公共参数
        $data = [
            'app_id'   => $this->app_id,                     //支付宝分配给开发者的应用ID
            'method'   => 'alipay.trade.wap.pay',           //接口名称
            'format'   => 'JSON',
            'charset'   => 'utf-8',
            'sign_type'   => 'RSA2',
            'timestamp'   => date('Y-m-d H:i:s'), //发送请求时间
            'version'   => '1.0',                           //	调用的接口版本，固定为：1.0
            'notify_url'   => $this->notify_url,            //支付宝服务器主动通知商户服务器里指定的页面http/https路径。建议商户使用https
            'biz_content'   => json_encode($bizcont),       //业务请求参数的集合
        ];

        $sign = $this->rsaSign($data);
        $data['sign'] = $sign;
        $param_str = '?';
        foreach($data as $k=>$v){
            $param_str .= $k.'='.urlencode($v) . '&';
        }
        $url = rtrim($param_str,'&');
        $url = $this->gate_way . $url;
        header("Location:".$url);
    }


    public function rsaSign($params) {
        return $this->sign($this->getSignContent($params));
    }

    protected function sign($data) {

        $priKey = file_get_contents($this->rsaPrivateKeyFilePath);
        $res = openssl_get_privatekey($priKey);

        ($res) or die('您使用的私钥格式错误，请检查RSA私钥配置');

        openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);

        if(!$this->checkEmpty($this->rsaPrivateKeyFilePath)){
            openssl_free_key($res);
        }
        $sign = base64_encode($sign);
        return $sign;
    }


    public function getSignContent($params) {
        ksort($params);
        $stringToBeSigned = "";
        $i = 0;
        foreach ($params as $k => $v) {
            if (false === $this->checkEmpty($v) && "@" != substr($v, 0, 1)) {

                // 转换成目标字符集
                $v = $this->characet($v, 'UTF-8');
                if ($i == 0) {
                    $stringToBeSigned .= "$k" . "=" . "$v";
                } else {
                    $stringToBeSigned .= "&" . "$k" . "=" . "$v";
                }
                $i++;
            }
        }

        unset ($k, $v);
        return $stringToBeSigned;
    }

    protected function checkEmpty($value) {
        if (!isset($value))
            return true;
        if ($value === null)
            return true;
        if (trim($value) === "")
            return true;

        return false;
    }


    /**
     * 转换字符集编码
     * @param $data
     * @param $targetCharset
     * @return string
     */
    function characet($data, $targetCharset) {
        if (!empty($data)) {
            $fileType = 'UTF-8';
            if (strcasecmp($fileType, $targetCharset) != 0) {
                $data = mb_convert_encoding($data, $targetCharset, $fileType);
            }
        }


        return $data;
    }
}
