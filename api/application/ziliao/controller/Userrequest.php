<?php

namespace app\ziliao\controller;
use app\admin\model\SongSheet;
use app\Utitls;
use think\facade\Request;
use function app\api\controller\db;

class Userrequest
{
    //根据用户提交的数据添加到表
    public function add()
    {
        $params = Request::param();
        
        // 参数验证
        if (empty($params["material_name"])) {
            return Utitls::sendJson(400, "资料名称不能为空");
        }
        if (empty($params["grade"])) {
            return Utitls::sendJson(400, "年级不能为空");
        }
        if (empty($params["subject"])) {
            return Utitls::sendJson(400, "科目不能为空");
        }
        // 准备插入数据
        $data = [
            'name' => $params["material_name"],
            'grade' => $params["grade"],
            'subject' => $params["subject"],
            'create_time' => date('Y-m-d H:i:s', time()), // 添加创建时间
            'status' => 0 // 默认状态为1
        ];

        // 插入数据
        try {
            $result = \app\ziliao\model\Userrequest::create($data);
            if ($result) {
                return Utitls::sendJson(200, "用户请求添加成功");
            } else {
                return Utitls::sendJson(500, "添加失败");
            }
        } catch (\Exception $e) {
            return Utitls::sendJson(500, "添加失败：" . $e->getMessage());
        }
    }

}