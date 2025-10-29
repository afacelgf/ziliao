<?php

namespace app\ziliao\controller;
use app\admin\model\SongSheet;
use app\Utitls;
use think\facade\Request;
use function app\api\controller\db;

class Subject
{
    //根据年级获取对应的科目列表
    public function getSubjectList()
    {
        $params = Request::param();
        $res = \app\ziliao\model\Subject::field(['id','name','description'])
            ->whereRaw("FIND_IN_SET(:grade_id, grade_ids) AND is_visible = 1", ['grade_id' => $params["grade_id"]])
            ->order('id asc')
            ->select();
        if ($res) {
            Utitls::sendJson(200,$res);
        }
        Utitls::sendJson(500);
    }

}