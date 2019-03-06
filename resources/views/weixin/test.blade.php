@extends('layouts.bootstrap')

@section('content')
    <h1><font>Web Socket测试</font></h1>
@endsection
@section('footer')
    @parent
    <script>
        var ws = new WebSocket('wss://example.com/socket');
        ws.onopen = function () {
            ws.send("Connection established . Hello server!")
        }
        ws.onmessage = function () {
            if (msg.data instanceof Blob){
                processBlob('msg.data')
            } else{
                processText('msg.data')
            }
        }
    </script>
@endsection