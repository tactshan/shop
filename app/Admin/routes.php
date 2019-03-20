<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    $router->resource('/goods',GoodsController::class);
    $router->resource('/weixin',WeixinController::class);
    $router->resource('/material',MaterialController::class);
    $router->resource('/auth/add_material',AddMaterialController::class);

    //微信群发
    $router->get('/auth/group_sending','AddMaterialController@groupSending');
    $router->post('/auth','AddMaterialController@group_content');

    //添加素材
    $router->post('/auth/add_material','AddMaterialController@getMaterial');

    //获取素材列表
    $router->get('/auth/getMaterialList','AddMaterialController@getMaterialList');

    //自定义菜单菜单管理
    $router->get('/auth/weixin/menu','WeixinController@upManu');

});
