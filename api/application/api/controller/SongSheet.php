<?php

namespace app\api\controller;

use app\Utitls;
use Monolog\Utils;
use think\facade\Request;

class SongSheet
{
    //热门
    public static function getHotList()
    {
        $params = Request::param();
        $count = \app\api\model\SongSheet::where(['status' => 1])->count();
        $res = \app\api\model\SongSheet::alias('m')
            ->join('user u','u.uid = m.uid_c')
            ->field('m.*,u.name u_name,u.fengmianIMG u_fengmian ')->
            where(['m.status' => 1])
            ->order('hot desc')
            ->select();
        if ($res) {
//            var_dump($res);
//            exit();
            $list = self::dealResultData($res);
            if ($list) {
                Utitls::sendJson(200, ['list' => $list, 'count' => $count, 'page' => ceil($count / 10)]);
            }
            Utitls::sendJson(500);
        }
        Utitls::sendJson(500);
    }

    //首页推荐
    public static function getTuiJianList()
    {
        $params = Request::param();

        $res = \app\api\model\SongSheet::alias('m')
            ->join('user u','u.id = m.uid_c')
            ->field('m.*,u.name u_name,u.fengmianIMG u_fengmian ')->
            where(['status' => 1,'tuijian'=>1])
            ->order('hot desc')
            ->limit(10);
        if ($res) {

            $list = self::dealResultData($res);
            if ($list) {
                Utitls::sendJson(200, ['list' => $list]);
            }
            Utitls::sendJson(500);
        }
        Utitls::sendJson(500);
    }
    //我的歌单
    public static function getMyList()
    {
        $params = Request::param();
        $where = ['uid_c' => $params['uid'],'status'=>1];
        $count = \app\api\model\SongSheet::where($where)->count();
        $res = \app\api\model\SongSheet::where($where)
            ->order('hot desc')
            ->select();
        if ($res) {
            $list = self::dealResultData($res,1);
            if ($list) {
                Utitls::sendJson(200, ['list' => $list, 'count' => $count, 'page' => ceil($count / 10)]);
            }
            Utitls::sendJson(500);
        }
        Utitls::sendJson(500);
    }

    public static function dealResultData($res,$isMyList=0){
        if ($res) {
            $list = [];
            foreach ($res as $key => $value) {
                $list[$key]['id'] = $value['id'];
                $list[$key]['name'] = $value['name'];
                if ($isMyList==0){
                    $list[$key]['u_name'] = $value['u_name']?$value['u_name']:'';
                    $list[$key]['u_fengmian'] = $value['u_fengmian'];
                }
                $list[$key]['desc'] = $value['desc']==null ? '':$value['desc'];
                $list[$key]['fengmianUrl'] = $value['fengmianUrl']==null ? '':$value['fengmianUrl'];
                $list[$key]['songIds'] = $value['songIds'];
                $list[$key]['uid_collect'] = $value['uid_collect'];
                $list[$key]['hot'] = $value['hot'] ;
            }
            return $list;
        }
    }


    //详情
    public function detail()
    {
        $params = Request::param();
        $res = \app\api\model\SongSheet::where(['id' => $params['id']])->find();
        if ($res) {
            Utitls::sendJson(200, $res);
        }
        Utitls::sendJson(500, '', '不存在');
    }

    //歌单歌曲列表
    public function getListById()
    {
        $params = Request::param();
        $res = \app\api\model\SongSheet::where(['id' => $params['id']])->find();
        if ($res) {
            if ($res['songIds']){
               $ids = explode(',',$res['songIds']);

//                $songData = \app\api\model\Music::alias('m')
//                    ->join('singer s',' m.author_id =s.id')
//                    ->field('m.*,s.name s_name')
////                    ->where(['m.id', 'in' ,$ids])
//                    ->where('m.id', 100)
//                    ->order('m.playNum desc')->select();

               $songData = \app\api\model\Music::where('id', 'in' ,$ids)->field('id,name, author_id,author_name')->select();
//              var_dump($songData);
//               exit();
                Utitls::sendJson(200, $songData);
            }
            Utitls::sendJson(500, '', '暂无歌曲');
        }
        Utitls::sendJson(500, '', '不存在');
    }

    //删除活动
    public function delete()
    {
        $params = Request::param();
        $res = \app\api\model\SongSheet::where(['id' => $params['id'],'uid_c' => $params['uid']])->find();
        if ($res) {
            \app\api\model\SongSheet::where(['id' => $params['id'],'uid_c' => $params['uid']])->update(
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
        $res = \app\api\model\SongSheet::createSongSheet($params);
        if ($res) {
            Utitls::sendJson(200);
        }
        Utitls::sendJson(500);
    }
    //编辑活动
    public function edit()
    {
        $params = Request::param();
        $sId = \app\api\model\SongSheet::where(['id' => $params['id'],'uid_c' => $params['uid']])->find();
        if (!$sId) {
            Utitls::sendJson(500, '', '歌单不存在');
        }
       $res = \app\api\model\SongSheet::where(['id' => $params['id'],'uid_c' => $params['uid']])->update(
            [
                'name'=>$params['name'],
//                'fengmianUrl'=>$params['fengmianUrl'],
//                'hot'=>$params['hot'],
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
