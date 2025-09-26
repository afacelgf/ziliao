<?php

namespace app\ziliao\controller;
use app\admin\model\SongSheet;
use app\Utitls;
use think\facade\Request;
use function app\api\controller\db;

class Grade
{
    //年级列表
     public function list()
    {
        $params = Request::param();
        $res = \app\ziliao\model\Grade::where(['status' => 1])
            ->field(['id', 'grade_name', 'grade_level', 'description'])
            ->order('id asc')
            ->select();
        if ($res) {
            Utitls::sendJson(200,$res);
        }
        Utitls::sendJson(500);
    }


}