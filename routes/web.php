<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});



//路由参数
Route::get('/user/test','User\UserController@test');
Route::get('/user/{uid}','User\UserController@user');
Route::get('/month/{m}/date/{d}','Test\TestController@md');
Route::get('/name/{str?}','Test\TestController@showName');



// View视图路由
Route::view('/mvc','mvc');
Route::view('/error','error',['code'=>40300]);



//测试
Route::get('/test','User\UserController@testcookie');
Route::get('/test02','Test\Test@test')->middleware('check.cookie');


//用户注册
Route::get('/userreg','User\UserController@reg');
Route::post('/userreg','User\UserController@doReg');

//列表展示
Route::get('/userlist','User\UserController@usershow');
//登录
Route::get('/userlogin','User\UserController@loginview');
Route::post('/userlogin','User\UserController@userlogin');
//退出
Route::get('/userquit','User\UserController@quit');

//购物车列表
Route::get('/cartlist','Cart\CartController@cartList');
//购物车添加
Route::get('/cartadd/{goods_id}','Cart\CartController@cartAdd');
Route::post('/cartadd','Cart\CartController@cartAddDo');
//删除购物车数据
Route::get('/delcart/{goods_id}','Cart\CartController@delCartInfo');

//商品列表展示
Route::get('/goodslist','Goods\GoodsController@goodsList');

//生成订单
Route::get('/orderadd','Order\OrderController@createOrder');

//订单详情
Route::get('/orderdetail/{order_num}','Order\OrderController@orderDetail');
//我的订单
Route::get('/allorders','Order\OrderController@allOrders');
//订单支付
Route::get('/orderpay/{order_num}','Order\OrderController@orderPay');
//取消订单
Route::get('/orderdel/{order_num}/{order_status}','Order\OrderController@orderDel');

////订单测试
Route::get('/ordertest','Order\OrderController@orderTest');

//支付
Route::get('/alipay/{order_num}','Pay\AlipayController@test');         //调用支付宝接口



Route::get('/pay/o/{oid}','Pay\IndexController@order')->middleware('check.login.token');         //订单支付
Route::post('/pay/alipay/notify','Pay\AlipayController@notify');        //支付宝支付 通知回调
Route::get('/pay/alipay/sync','Pay\AlipayController@sync');        //支付宝支付 通知回调

//计划任务
Route::get('/pay/delete','Pay\CrontabController@deleteOrder');


Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
