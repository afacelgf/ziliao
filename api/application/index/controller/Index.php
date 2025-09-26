<?php

namespace app\index\controller;

use app\Utitls;

class Index
{
    public function index()
    {
        return '首页12';
    }
    public function test()
    {
        return '首页1122';
    }

    public function miss()

    {
//        Utitls::sendJson(404, '', 'Route Not Found');
        return '404-路由找不到1';
    }
}
