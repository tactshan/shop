@extends('layouts.bootstrap')

@section('content')
    <h1><font>和{{$nickname}}的聊天界面</font></h1>
    <input type="hidden" name="openid" id="openid" value="{{$openid}}">
    <input type="hidden" name="nickname" id="nickname" value="{{$nickname}}">
    <div style="width: 500px;height: 500px;border: 1px red solid" id="show">
    </div>
    <input type="text" id="content" style="width: 500px;height: 50px;border: 1px blue solid">
    <button class="btn btn-danger" id="send">发送</button>
@endsection
@section('footer')
    @parent
    <script>
        $(function () {
            var _openid=$('#openid').val()
            var _nickname=$('#nickname').val()
            setInterval(function () {
                $('#show').empty()
                $.ajax({
                    headers:{
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url:'/get_wx_chat_record',
                    type:'post',
                    data:{openid:_openid},
                    success:function (reg) {
                        var data = JSON.parse(reg)
                        $.each(data,function(i,n){
                            // console.log(n)
                            if(n['send_people'] == '客服'){
                                var _text="<h5>"+"客服:"+n['content']+"</h5>"
                            }else{
                                var _text="<h5>"+_nickname+":"+n['content']+"</h5>"
                            }
                            $('#show').append(_text)
                        });
                    }
                })
            },1000)


            $('#send').click(function (e) {
                e.preventDefault()
                //点击发送获取文本信息
                var _content=$('#content').val()
                var _openid=$('#openid').val()
                $('#content').val('')
                var _text="<h5>"+"客服:"+_content+"</h5>"
                $('#show').append(_text)
               $.ajax({
                   headers:{
                       'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                   },
                   url:'/wx_interact',
                   type:'post',
                   data:{openid:_openid,content:_content},
                   success:function (reg) {
                       console.log(reg)
                   }
               })
            })
        })
    </script>
@endsection