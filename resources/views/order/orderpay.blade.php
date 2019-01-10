@extends('layouts.bootstrap')

@section('content')
    <h1>支付中心</h1>
    <form>
            <table border="1" class="table table-bordered" style="width: 600px;">
                <tr>
                    <td style="width: 150px;">订单号</td>
                    <td>{{$info->order_num}}</td>
                </tr>
                <tr>
                    <td>总金额</td>
                    <td>{{$info->order_amount/100}}</td>
                </tr>
                <tr>
                    <td>下单时间</td>
                    <td>{{date('Y-m-d H:i:s',$info->c_time)}}</td>
                </tr>
                <tr>
                    <td>支付方式</td>
                    <td>支付宝</td>
                </tr>
            </table>

    </form>
    <button class="btn btn-danger"><a href="#" style="text-decoration: none; color: #ffffff;">结算</a></button>
@endsection

@section('footer')
    @parent
@endsection