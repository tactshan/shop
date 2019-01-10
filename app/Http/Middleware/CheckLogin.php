<?php

namespace App\Http\Middleware;

use Closure;

class CheckLogin
{
    /**
     * 验证登录
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(empty(session()->get('uid'))){
            header("refresh:2;url=/userlogin");
            exit('Please login ... ...');
        }else{
            if($_COOKIE['token']!=$request->session()->get('u_token')){
                header("refresh:2;url=/userlogin");
                exit('Please login ... ...');
            }
        }
        return $next($request);
    }
}
