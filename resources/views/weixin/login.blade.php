@extends('layouts.bootstrap')

@section('content')
    <h1><font>微信登录</font></h1>
    <h5><a href="https://open.weixin.qq.com/connect/qrconnect?appid=wxe24f70961302b5a5&amp;r1=http%3A%2F%2Fshop.tactshan.com%2Fweixin%2Fgetcode;redirect_uri=http%3A%2F%2Fmall.77sc.com.cn%2Fweixin.php&amp;response_type=code&amp;scope=snsapi_login&amp;state=STATE#wechat_redirect">微信登录</a></h5>
@endsection
@section('footer')
    @parent
    <script>
    </script>
@endsection