@extends('layouts.bootstrap')

@section('content')
    <h1><font>UID:{{$uid}} Welcome back!</font></h1>
    <table border="1" class="table table-bordered">
        <tr>
            <td>商品id</td>
            <td>商品名称</td>
            <td>购买数量</td>
            <td>操作</td>
        </tr>
        @foreach($info as $v)
            <tr>
                <td>{{$v->goods_id}}</td>
                <td>{{$v->goods_name}}</td>
                <td>{{$v->buy_number}}</td>
                <td><a href="/delcart/<?php echo $v['goods_id']?>">删除</a></td>
            </tr>
        @endforeach
    </table>
    <button class="btn btn-danger" ><a href="/userquit" style="text-decoration: none;color: white;">Quit</a></button>
@endsection