@extends('layouts.bootstrap')

@section('content')
    <h1><font>UID: Welcome back!</font></h1>
    <button class="btn btn-danger" ><a href="/userquit" style="text-decoration: none;color: white;">Quit</a></button>
    <table border="1" class="table table-bordered">
        <tr>
            <td>用户ID</td>
            <td>微信号(openid)</td>
            <td>微信昵称</td>
            <td>性别</td>
            <td>头像</td>
            <td>状态</td>
            <td>添加时间</td>
            <td>操作</td>
        </tr>
        @foreach($info as $v)
            <tr>
                <td>{{$v->id}}</td>
                <td>{{$v->openid}}</td>
                <td>{{$v->nickname}}</td>
                @if($v->sex== 1)
                    <td>男</td>
                @elseif($v->sex == 2)
                    <td>女</td>
                @else
                    <td>暂无填写</td>
                @endif
                <td><img src="{{$v->headimgurl}}"></td>
                @if($v->status == 1)
                    <td>已关注</td>
                @elseif($v->status == 2)
                    <td>取消关注</td>
                @endif
                <td>{{date('Y/m/d H:i:s',$v->add_time)}}</td>
                <td><a href="/wx_interact_view/{{$v->openid}}/{{$v->nickname}}">互动</a></td>
            </tr>
        @endforeach
    </table>
    {{$info->links()}}
@endsection
@section('footer')
    @parent
    <script>
    </script>
@endsection