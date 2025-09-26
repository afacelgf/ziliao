<?php

namespace app\ziliao\controller;
use app\admin\model\SongSheet;
use app\Utitls;
use think\facade\Request;
use function app\api\controller\db;

class Material
{

    //资料列表-根据年级id和科目id、type_id获取对应的资料分类列表【语文、数学】，每个分类下面都有资料列表
     public function list()
    {
        $params = Request::param();
        $res = \app\ziliao\model\Material::where([
            'grade_id' => $params['grade_id'],
            'subject_id'=>$params['subject_id'],
            'type_id'=>$params['type_id'],
                'is_visible'=>1
            ]
        )
            ->field(['id', 'name', 'download_url', 'file_size','description','click_count','download_count'])
            ->order('click_count desc')->select();
        if ($res) {
            Utitls::sendJson(200,$res);
        }
        Utitls::sendJson(500);
    }

    //类别列表-根据年级id和科目id获取对应的分类列表【试卷、练习册】
    public function getTypeList()
    {
        $params = Request::param();

        // 参数检查
        if (empty($params["grade_id"]) || empty($params["subject_id"])) {
            return Utitls::sendJson(400, "参数错误");
        }

        // 查询
        $res = \app\ziliao\model\MaterialCategory::field(['id', 'name', 'description'])
            ->whereRaw("FIND_IN_SET(:subject_id, subject_ids)", ['subject_id' => $params["subject_id"]])
            ->whereRaw("FIND_IN_SET(:grade_id, grade_ids)", ['grade_id' => $params["grade_id"]])
            ->where('is_visible', 1)
            ->order('sort_order desc')
            ->select()
            ->toArray();

        // 返回
        if (!empty($res)) {
            return Utitls::sendJson(200, $res);
        }

        return Utitls::sendJson(204, "暂无数据");
    }

}