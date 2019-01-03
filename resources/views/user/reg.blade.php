<!doctype html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>用户注册</title>
</head>
<body>
<form action="/userreg" method="post">
    {{csrf_field()}}
   用户名： <input type="text" name="u_name"><br>
    Email: <input type="text" name="u_email"><br>
    年龄： <input type="text" name="u_age"><br>
    <input type="submit" value="提交">
</form>
</body>
</html>