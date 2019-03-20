<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [

        '/test/*',
         '/pay/alipay/notify',
        '/weixin/valid',
        '/weixin/valid1',
        '/auth',
        '/wx_interact',
        '/get_wx_chat_record',
        '/weixin/pay/notice',
        '/weixin/pay/find',
        'phone/phone_login'
    ];
}
