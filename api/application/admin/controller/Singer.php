<?php

namespace app\admin\controller;

use app\Utitls;
use Monolog\Utils;
use think\facade\Request;

class Singer
{
    //列表
    public function getList()
    {
        $params = Request::param();
        if (isset($params['query']) && !empty($params['query'])) {
            $where[] = ['name', 'like', '%' . $params['query'] . '%'];
        }
        $where[] = ['delete', '=', 0];
        $count = db('singer')->where($where)->count();
        $res = db('singer')
            ->where($where)
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
                Utitls::sendJson(200, ['list' => $res, 'count' => $count, 'page' => ceil($count / 10)]);
            }
            Utitls::sendJson(500);
        }
        Utitls::sendJson(500);
    }

    //删除活动
    public function delete()
    {
        $params = Request::param();
        $res = \app\admin\model\Singer::where(['id' => $params['id']])->find();
        if ($res) {
            \app\admin\model\Singer::where(['id' => $params['id']])->delete();
            Utitls::sendJson(200, '');
        }
        Utitls::sendJson(500);
    }

    // 添加
    public function add()
    {
        $params = Request::param();
        $res = \app\admin\model\Singer::createSinger($params);
        if ($res) {
            Utitls::sendJson(200);
        }
        Utitls::sendJson(500);
    }
    //编辑活动
    public function edit()
    {
        $params = Request::param();
        $sId = \app\admin\model\Singer::where(['id' => $params['id']])->find();
        if (!$sId) {
            Utitls::sendJson(500, '', '歌手不存在');
        }
       $res = \app\admin\model\Singer::where(['id' => $params['id']])->update(
            [
                'name'=>$params['name'],
                'fengmianUrl'=>$params['fengmianUrl'],
                'hot'=>$params['hot'],
                'albumIDs'=>$params['albumIDs'],
                'desc'=>$params['desc'],
                'songNum'=>$params['songNum'],
                'status'=>$params['status'],
            ]
        );
        if ($res) {
            Utitls::sendJson(200);
        }
        Utitls::sendJson(500);
    }

    // 添加歌曲的时候，没有歌手时添加一条
    public function addByName($name,$albumId)
    {
        $map['name'] = $name;
        $map['fengmianUrl']=$name.'.png';
        $map['albumIDs'] = $albumId;
        $map['songNum'] = 0;
        $map['hot'] = 1;
        $map['create_at'] = time();
        $map['status'] = 1;
        return \app\admin\model\Singer::insertGetId($map);
    }
    //获取歌手id
    public function getIdByName($name){
        $singerIdInfo =  \app\admin\model\Singer::where(['name'=>$name])->field('id')->find();
        return $singerIdInfo['id'];
    }

    //更新歌手专辑ids
    public function updateIdsByAlbumId($name,$albumId){
        $singerIdInfo =  \app\admin\model\Singer::where(['name'=>$name])->find();
        if ($singerIdInfo['albumIDs'] == null){
            \app\admin\model\Singer::update(['albumIDs'=>$albumId]);
        }else {
            $oringeAlbumIDs = Utitls::StrToArr($singerIdInfo['albumIDs']);
            if (in_array($albumId,$oringeAlbumIDs)){ //已包含
                return;
            }
             array_push($oringeAlbumIDs, $albumId);
            $resultStr = Utitls::ArrToStr($oringeAlbumIDs);
            \app\admin\model\Singer::where(['name'=>$name])->update(['albumIDs' => $resultStr]);
        }
    }

    // 更新
    public function update()
    {
        $params = Request::param();
        $res = \app\api\model\News::adminUpdateNews($params);
        if ($res) {
            Utitls::sendJson(200);
        }
        Utitls::sendJson(500);
    }


}
