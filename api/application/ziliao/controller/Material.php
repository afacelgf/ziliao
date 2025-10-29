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
            'grade_id'   => $params['grade_id'],
            'subject_id' => $params['subject_id'],
            'type_id'    => $params['type_id'],
            'is_visible' => 1
        ])
            ->field(['id', 'name', 'download_url', 'file_size', 'description', 'click_count', 'download_count'])
            ->order('click_count desc')
            ->select();

        if ($res) {
            // 转为数组，防止模型对象导致正则失效
            $res = $res->toArray();

            foreach ($res as &$item) {
                if (!empty($item['download_url'])) {
                    // 获取原始URL并进行清理
                    $url = $item['download_url'];
                    $url = trim($url);  // 标准trim
                    $url = preg_replace('/^[\s\x{00A0}\x{3000}\x{FEFF}]+|[\s\x{00A0}\x{3000}\x{FEFF}]+$/u', '', $url); // Unicode空白
                    $url = preg_replace('/^[\x00-\x20\x7F-\xFF]+|[\x00-\x20\x7F-\xFF]+$/u', '', $url); // 控制字符和扩展ASCII
                    
                    // 去除开头的多余斜杠，但保留协议部分
                    $url = preg_replace('/^\/+(https?:\/\/)/', '$1', $url);
                    
                    // 检查URL是否以协议开头
                    if (preg_match('/^https?:\/\//', $url)) {
                        // 已有完整协议，直接使用清理后的URL
                        $item['download_url'] = $url;

                    } else {
                        // 没有协议，添加域名前缀
                        $newUrl = 'https://jxpyq666.com' . (strpos($url, '/') === 0 ? '' : '/') . $url;
                        $item['download_url'] = $newUrl;
                    }
                }
            }

            Utitls::sendJson(200, $res);
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

        // 联表查询，只返回有Material内容的类型，并按Material数量排序
        $res = \app\ziliao\model\MaterialCategory::alias('mc')
            ->field(['mc.id', 'mc.name', 'mc.description', 'COUNT(m.id) as material_count'])
            ->join('material m', 'mc.id = m.type_id', 'INNER')
            ->whereRaw("FIND_IN_SET(:subject_id, mc.subject_ids)", ['subject_id' => $params["subject_id"]])
            ->whereRaw("FIND_IN_SET(:grade_id, mc.grade_ids)", ['grade_id' => $params["grade_id"]])
            ->where('mc.is_visible', 1)
            ->where('m.grade_id', $params["grade_id"])
            ->where('m.subject_id', $params["subject_id"])
            ->where('m.is_visible', 1)
            ->group('mc.id')
            ->order('material_count desc')
            ->select()
            ->toArray();


        // 返回
        if (!empty($res)) {
            return Utitls::sendJson(200, $res);
        }

        return Utitls::sendJson(204, "暂无数据");
    }


    //搜索资料-根据关键词搜索资料名称
    public function search()
    {
        $params = Request::param();

        // 参数检查
        if (empty($params["keyword"])) {
            return Utitls::sendJson(400, "请输入搜索关键词");
        }

        // 搜索查询
        $res = \app\ziliao\model\Material::where('name', 'like', '%' . $params["keyword"] . '%')
            ->where('is_visible', 1)
            ->field(['id', 'name', 'download_url', 'file_size', 'description', 'click_count', 'download_count', 'grade_id', 'subject_id', 'type_id'])
            ->order('click_count desc')
            ->select();

        // 返回
        if ($res && count($res) > 0) {
            return Utitls::sendJson(200, $res);
        }

        return Utitls::sendJson(204, "暂无相关资料");
    }

}