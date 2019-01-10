@extends('layouts.bootstrap')

@section('content')
    <h1><font>UID:{{$uid}} Welcome back!</font></h1>
    <table border="1" class="table table-bordered">
        <tr>
            <td>商品id</td>
            <td>商品名称</td>
            <td>价格</td>
            <td>库存</td>
            <td>操作</td>
        </tr>
        @foreach($info as $v)
            <tr>
                <td>{{$v->goods_id}}</td>
                <td>{{$v->goods_name}}</td>
                <td>{{$v->goods_price/100}}</td>
                <td>{{$v->goods_stock}}</td>
                <td><a href="/cartadd/{{$v->goods_id}}">商品信息</a></td>
            </tr>
        @endforeach
    </table>
    <button class="btn btn-danger" ><a href="/userquit" style="text-decoration: none;color: white;">Quit</a></button>
    <button class="btn btn-danger"><a href="/allorders" style="text-decoration: none; color: #ffffff;">我的全部订单</a></button>
@endsection