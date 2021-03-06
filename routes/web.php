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
Route::get('/cartlist','Cart\CartController@cartList')->middleware('checklogin');
//购物车添加
Route::get('/cartadd/{goods_id}','Cart\CartController@cartAdd')->middleware('checklogin');
Route::post('/cartadd','Cart\CartController@cartAddDo');
//删除购物车数据
Route::get('/delcart/{goods_id}','Cart\CartController@delCartInfo');

//商品列表展示
Route::get('/goodslist/{keys?}','Goods\GoodsController@goodsList');


//生成订单
Route::get('/orderadd','Order\OrderController@createOrder');

//订单详情
Route::get('/orderdetail/{order_num}','Order\OrderController@orderDetail');
//我的订单
Route::get('/allorders','Order\OrderController@allOrders')->middleware('checklogin');
//订单支付
Route::get('/orderpay/{order_num}','Order\OrderController@orderPay')->middleware('checklogin');
//取消订单
Route::get('/orderdel/{order_num}/{order_status}','Order\OrderController@orderDel')->middleware('checklogin');

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

//文件上传
Route::get('/upload','Upload\UploadController@upload');
Route::post('/pdfadd','Upload\UploadController@pdfadd');


Route::get('/weixin/valid','Weixin\WeixinController@validToken');
Route::get('/weixin/valid1','Weixin\WeixinController@validToken1');
Route::post('/weixin/valid1','Weixin\WeixinController@wxEvent');        //接收微信服务器事件推送
Route::post('/weixin/valid','Weixin\WeixinController@validToken');
Route::get('/weixin/create_menu','Weixin\WeixinController@createMenu');      //自定义菜单创建
Route::get('/weixin/get_access_token','Weixin\WeixinController@getWXAccessToken');      //获取微信的access_token
Route::get('/weixin/group_sending','Weixin\WeixinController@GroupSending');      //群发送消息

Route::get('/weixin/refresh_token','Weixin\WeixinController@refreshToken');     //刷新token

//微信用户列表
Route::get('/weixin/wx_user_list','Weixin\WeixinController@wxUserList')->middleware('checklogin');
Route::post('/wx_interact','Weixin\WeixinController@wxInteract');
//聊天界面
Route::get('/wx_interact_view/{openid}/{nickname}','Weixin\WeixinController@interactView')->middleware('checklogin');
//实时获取连天记录表
Route::post('/get_wx_chat_record','Weixin\WeixinController@getWxChatRecord');


//微信支付
Route::get('/wx_pay/{order_num}','Weixin\PayController@wxPay')->middleware('checklogin');
//微信回调地址
Route::post('/weixin/pay/notice','Weixin\PayController@notice');
//检测是否支付成功
Route::post('/weixin/pay/find','Weixin\PayController@find');

//微信登录
Route::get('weixin/login','Weixin\WeixinController@login');
//接受code
Route::get('weixin/getcode','Weixin\WeixinController@getCode');

//微信JS-SDK
Route::get('weixin/jssdk/test','Weixin\WeixinJsSDK@wxJsSdk');
Route::get('weixin/jssdk/gettoken','Weixin\WeixinJsSDK@getWXAccessToken');
Route::get('weixin/jssdk/refresh_token','Weixin\WeixinJsSDK@refreshToken');     //刷新token
Route::get('weixin/jssdk/getticket','Weixin\WeixinJsSDK@getJsapiTicket');


//WebSocket测试
Route::get('weixin/jssdk/scoket','Weixin\WeixinJsSDK@test');


//手机登录测试
Route::post('phone/phone_login','User\UserController@phoneLogin');
Route::post('phone/phone_register','User\UserController@phoneRegister');