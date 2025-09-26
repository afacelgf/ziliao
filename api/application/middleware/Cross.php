<?php
/**
 * Created by PhpStorm.
 * User: Xs___
 * Date: 2019/11/25
 * Time: 21:19:26
 * Desc:
 */

namespace app\middleware;

use app\Utitls;

class Cross
{

    public function handle($request, \Closure $next)
    {
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers:Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-CSRF-TOKEN, X-Requested-With');

        $params = Request()->param();
        if (!isset($params['token']) || $params['token'] == '') {
                Utitls::sendJson(-2);
        }

        return $next($request);
    }
}