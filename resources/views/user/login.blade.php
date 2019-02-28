@extends('layouts.bootstrap')

<title>用户登录</title>

@section('content')

    <body>
        <form action="/userlogin" method="post">
            {{csrf_field()}}
            <table class="table table-bordered" style="width: 300px;">
                <h2>Login</h2>
                <tr>
                    <td width="100px">Email:</td>
                    <td><input type="text" name="u_email"></td>
                </tr>
                <tr>
                    <td>Password:</td>
                    <td><input type="password" name="u_pwd"></td>
                </tr>
            </table>
            <input class="btn btn-danger" type="submit" value="Login">
            <button class="btn btn-danger" ><a href="https://open.weixin.qq.com/connect/qrconnect?appid=wxe24f70961302b5a5&amp;redirect_uri=http%3A%2F%2Fmall.77sc.com.cn%2Fweixin.php?r1=http%3A%2F%2Fshop.tactshan.com%2Fweixin%2Fgetcode&amp;response_type=code&amp;scope=snsapi_login&amp;state=STATE#wechat_redirect" style="text-decoration: none;color: white;">微信登录</a></button>
            <button class="btn btn-danger" ><a href="/userreg" style="text-decoration: none;color: white;">Go register!</a></button>
        </form>
    </body>
@endsection
