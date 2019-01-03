<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>用户登录</title>
</head>
<body>
    <form action="/userlogin" method="post">
        {{csrf_field()}}
        <h2>用户登录</h2>
        用户名：<input type="text" name="u_name">
        密码：<input type="password" name="u_pwd">
        <input type="submit" value="提交">
    </form>
</body>
</html>