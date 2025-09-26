<?php

namespace app\admin\controller;

use app\Utitls;
use Monolog\Utils;
use think\facade\Request;

class SongSheet
{
    //列表
    public function getList()
    {
        $params = Request::param();
        if (isset($params['query']) && !empty($params['query'])) {
            $where[] = ['name', 'like', '%' . $params['query'] . '%'];
        }
        $where[] = ['delete_at', '=', 0];
        $count = \app\admin\model\SongSheet::where($where)->count();
        $res = \app\admin\model\SongSheet::
            where($where)
            ->order('id desc')
            ->paginate($params['pagesize'], false, ['page' => $params['pagenum']]);
        if ($res) {
//            $list = [];
//            foreach ($res as $key => $value) {
//                $list[$key]['id'] = $value['id'];
//                $list[$key]['url'] = $value['url'];
//                $list[$key]['name'] = $value['name'];
//                $list[$key]['author'] = $value['s_name'];
//                $list[$key]['albumName'] = $value['a_name'];
//                $list[$key]['playNum'] = $value['playNum'];
//                $list[$key]['gedanName'] = '123';
//                $list[$key]['sort'] = $value['sort'];
//                $list[$key]['status'] = $value['status'] ;
//            }
            if ($res) {
//                Utitls::sendJson(200, ['list' => $res, 'count' => $count, 'page' => ceil($count / 10)]);
                Utitls::sendJson(200, $res);
            }
            Utitls::sendJson(500);
        }
        Utitls::sendJson(500);
    }
    //详情
    public function detail()
    {
        $params = Request::param();
        $res = \app\admin\model\SongSheet::where(['id' => $params['id']])->find();
        if ($res) {
            Utitls::sendJson(200, $res);
        }
        Utitls::sendJson(500, '', '不存在');
    }

    //删除活动
    public function delete()
    {
        $params = Request::param();
        $res = \app\admin\model\SongSheet::where(['id' => $params['id']])->find();
        if ($res) {
            \app\admin\model\SongSheet::where(['id' => $params['id']])->update(
                [
                    'delete_at' => 1,
                    'status' => 0
                ]
            );;
            Utitls::sendJson(200, '');
        }
        Utitls::sendJson(500);
    }

    // 添加
    public function add()
    {
        $params = Request::param();
        $res = \app\admin\model\SongSheet::createSongSheet($params);
        if ($res) {
            Utitls::sendJson(200);
        }
        Utitls::sendJson(500);
    }
    //编辑活动
    public function edit()
    {
        $params = Request::param();
        $sId = \app\admin\model\SongSheet::where(['id' => $params['id']])->find();
        if (!$sId) {
            Utitls::sendJson(500, '', '歌单不存在');
        }
       $res = \app\admin\model\SongSheet::where(['id' => $params['id']])->update(
            [
                'name'=>$params['name'],
                'fengmianUrl'=>$params['fengmianUrl'],
                'hot'=>$params['hot'],
                'uid_c'=>$params['uid_c'],
                'desc'=>$params['desc'],
                'songIds'=>$params['songIds'],
                'status'=>$params['status'],
                'uid_collect'=>$params['uid_collect'],
                'update_at' => time()
            ]
        );
        if ($res) {
            Utitls::sendJson(200);
        }
        Utitls::sendJson(500);
    }
}
