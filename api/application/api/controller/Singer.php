<?php

namespace app\api\controller;

use app\Utitls;
use think\facade\Request;

class Singer
{
    // 歌手列表
    public function index(){
        $params = Request::param();
        $pagesize = 10;
        $count = \app\api\model\Singer::where(['status' => 1])->count();
        $info = \app\api\model\Singer::where(['status' => 1])->order('hot desc')->paginate($pagesize);
        $list = [];
        if ($info) {
            foreach ($info as $key => $value) {
                $list[$key]['id'] = $value['id'];
                $list[$key]['name'] = $value['name'];
                $list[$key]['fengmianUrl'] = $value['fengmianUrl'];
                $list[$key]['hot'] = $value['hot'];
                $list[$key]['desc'] = $value['desc'];
                $list[$key]['songNum'] = $value['songNum'];
                $list[$key]['albumIDs'] = $value['albumIDs'];
                $list[$key]['time'] = date('Y-m-d H:i', $value['create_at']);
            }
//            exit(json_encode($list, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            Utitls::sendJson(200, ['list' => $list, 'page' => ceil($count / $pagesize), 'total' => $count]);
        } else {
            Utitls::sendJson(500, []);

        }
    }


    //根据id获取详情11
    public function detail()
    {
        $params = Request::param();
        $info = \app\api\model\News::where(['id' => $params['id']])->find();


//        \app\api\model\Album::where('id', $params['id'])->update(['read_total' => $readTotal]);
        if ($info) {

            $info['upic'] = \app\api\model\User::where(['delete_at' => 0, 'uuid' => $info['uuid']])->value('avatar');
            $info['uname'] = \app\api\model\User::where(['delete_at' => 0, 'uuid' => $info['uuid']])->value('nickname');

            $list['townName'] = db('town')->where(['delete_at' => 0, 'id' => $info['townid']])->value('name');
            $info['content'] = json_decode($info['content']);
            $info['time'] = date('Y-m-d H:i', $info['create_at']);
            //阅读数+1
            $info['read_total'] = $info['read_total'];
            $info['refusetip'] = $info['refusetip'];

            Utitls::sendJson(200, $info);
        } else {
            Utitls::sendJson(500, null, '数据已被删除');
        }
    }

    //设置是否通过  status=0审核中 1通过 2拒绝 3直接删除，refusetip:拒绝理由
    public function set()
    {
        $params = Request::param();
        $info = \app\api\model\News::where(['id' => $params['id']])->find();
        if ($info) {
            if ($params['status'] == 1) {
                $res = \app\api\model\News::where('id', $params['id'])->update(['status' => 1, 'delete_at' => 0, 'refusetip' => $params['refusetip']]);
                if ($res) {
                    Utitls::sendJson(200, $info, '设置成功');
                } else {
                    Utitls::sendJson(500, null, '设置失败');
                }
            } else if ($params['status'] == 2) {
                $res = \app\api\model\News::where('id', $params['id'])->update(['status' => 2, 'delete_at' => 0, 'refusetip' => $params['refusetip']]);
                if ($res) {
                    Utitls::sendJson(200, $info, '设置成功');
                } else {
                    Utitls::sendJson(500, null, '设置失败');
                }
            } else if ($params['status'] == 3) {
                $res = \app\api\model\News::where('id', $params['id'])->delete();
                if ($res) {
                    Utitls::sendJson(200, $info, '删除成功');
                } else {
                    Utitls::sendJson(500, null, '设置失败');
                }
            }

        } else {
            Utitls::sendJson(500, null, '数据已被删除');
        }
    }

    // 添加
    public function add()
    {
        $params = Request::param();
        $res = \app\admin\model\Album::adminCreateNews($params);
        if ($res) {
            Utitls::sendJson(200);
        }
        Utitls::sendJson(500);
    }

    // 添加
    public function addByName($name, $albumId)
    {
        $map['name'] = $name;
        $map['albumIDs'] = $albumId;
        $map['create_time'] = time();
        $map['status'] = 1;
        return \app\admin\model\Singer::insertGetId($map);
    }

    //获取歌手id
    public function getIdByName($name)
    {
        $singerIdInfo = \app\admin\model\Singer::where(['name' => $name])->field('id')->find();
        return $singerIdInfo['id'];
    }

    //更新歌手专辑ids
    public function updateIdsByAlbumId($name, $albumId)
    {
        $singerIdInfo = \app\admin\model\Singer::where(['name' => $name])->find();
        if ($singerIdInfo['albumIDs'] == null) {
            \app\admin\model\Singer::update(['albumIDs' => $albumId]);
        } else {
            $oringeAlbumIDs = Utitls::StrToArr($singerIdInfo['albumIDs']);
            array_push($oringeAlbumIDs, $albumId);

            $resultStr = Utitls::ArrToStr($oringeAlbumIDs);
            \app\admin\model\Singer::where(['name' => $name])->update(['albumIDs' => $resultStr]);
        }
    }

    //    添加置顶
    public function setNewsTop()
    {
        $params = Request::param();
        $res = \app\api\model\News::where(['id' => $params['id'], 'delete_at' => 0])->update(
            [
                'top' => 1,
                'startTopTimeStramp' => substr($params['startTopTimeStramp'], 0, 10),
                'endToptimestamp' => substr($params['endToptimestamp'], 0, 10),
            ]
        );

        if ($res) {
            Utitls::sendJson(200);
        }
        Utitls::sendJson(500);
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

    //通过请求
    public function agree()
    {

    }

}
