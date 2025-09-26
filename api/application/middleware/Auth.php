<?php
/**
 * Created by PhpStorm.
 * User: Xs___
 * Date: 2019/11/25
 * Time: 21:05:15
 * Desc: Api接口验签
 */

namespace app\middleware;

use app\Utitls;
use think\facade\Env;

class Auth
{

    public function handle($request, \Closure $next)
    {
        $params = Request()->param();
        unset($params['s']);

        //debug模式
        $isDebug = Env::get('APP_DEBUG');
//        dump($isDebug);
//        exit();
        if ($isDebug) {
            return $next($request);
        }

        //校验接口时效
        if (abs($params['api_times'] - time()) >= 10) {
            Utitls::sendJson(405, '', 'Params Expire');
        }

        //校验签名
        if (!Utitls::yzSign($params)) {
            Utitls::sendJson(403, '', 'Sign Error');
        }
        return $next($request);
    }
}
