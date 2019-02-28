<?php

namespace App\Http\Middleware;

use Closure;

class CheckLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $uid=session()->get('uid');
//        echo $uid."<br>";
        $access_token=session()->get('u_token');
//        echo $access_token."<br>";
//        $cookie_token=$request->cookie('cookie_token');
//        echo $cookie_token."<br>";
        if(empty($uid)){
            echo "Place Login....001";
            header("refresh:2;url=/userlogin");
            exit;
        }
        if($_COOKIE['cookie_token'] != $access_token) {
            echo "Place Login....002";
            header("refresh:2;url=/userlogin");
            exit;
        }
            return $next($request);
    }
}
