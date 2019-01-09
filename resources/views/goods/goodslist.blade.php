@extends('layouts.bootstrap')

@section('content')
    <h1><font>UID:{{$uid}} Welcome back!</font></h1>
    <table border="1" class="table table-bordered">
        <tr>
            <td>商品id</td>
            <td>商品名称</td>
            <td>价格</td>
            <td>库存</td>
            <td>添加时间</td>
        </tr>
        @foreach($info as $v)
            <tr>
                <td>{{$v->goods_id}}</td>
                <td>{{$v->goods_name}}</td>
                <td>{{$v->goods_price}}</td>
                <td>{{$v->goods_stock}}</td>
                <td>{{date('Y-m-d H:i:s',$v->reg_time)}}</td>
            </tr>
        @endforeach
    </table>
    <button class="btn btn-danger" ><a href="/userquit" style="text-decoration: none;color: white;">Quit</a></button>
@endsection