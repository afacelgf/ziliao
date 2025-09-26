<?php

namespace app\api\controller;
use app\admin\model\SongSheet;
use app\Utitls;
use think\facade\Request;
//use WXBizDataCrypt;
include 'Tixian.php';

class music
{
    //歌单列表
     public function gedanList()
    {

        $params = Request::param();
        $res = SongSheet::where(['status' => 1])->order('id desc')->select();
        if ($res) {
            Utitls::sendJson(200,$res);
        }
        Utitls::sendJson(500);
    }
    //收藏列表123
      public function collectList()

    {
        $params = Request::param();
        $res = db('music')->where(['status' => 1,'collect'=>1])->order('id desc')->select();
        if ($res) {
            Utitls::sendJson(200,$res);
        }
        Utitls::sendJson(500);
    }
    //歌曲列表-根据歌单id
     public function songList()
    {
        $params = Request::param();
        $res = db('music')->where(['status' => 1,'gedan_id'=>$params['gedan_id']])->order('sort desc')->select();
        if ($res) {
            Utitls::sendJson(200,$res);
        }
        Utitls::sendJson(500);
    }


    //补全歌手名字
    public function addSingerName()
    {
       $res = \app\api\model\Music::where(['delete_at' => 0])->select();
       foreach ($res as $key => $item){
          $name = \app\api\model\Singer::where(['id'=>$item['author_id']])->field('name')->find();
          $result = \app\api\model\Music::where(['author_id'=>$item['author_id']])->update(['author_name'=>$name['name']]);
       }
    }

    //歌曲列表-根据歌手id
    public function songListBySingerId()
    {
        $params = Request::param();
        $pagesize = 20;
        $count = \app\api\model\Music::where(['status' => 1,'author_id'=>$params['author_id']])->count();
        $info = \app\api\model\Music::alias('m')
        ->join('singer s','s.id = m.author_id')
        ->join('album a', 'a.id = m.album_id')
        ->field('m.*,s.name s_name,s.fengmianUrl,a.name album_name')->where(['m.status' => 1,'m.author_id'=>$params['author_id']])->order('playNum desc')->paginate($pagesize);
        $list = [];
        if ($info) {
            foreach ($info as $key => $value) {
                $list[$key]['id'] = $value['id'];
                $list[$key]['name'] = $value['name'];
                $list[$key]['playNum'] = $value['playNum'];
                $list[$key]['hot'] = $value['hot'];
                $list[$key]['name'] = $value['name'];
                $list[$key]['author'] = $value['s_name'];
                $list[$key]['desc'] = $value['desc'];
                $list[$key]['fengmian'] ='mp3/fengmian/'. $value['fengmianUrl'];
               $list[$key]['collect'] = $value['collect'];
                $list[$key]['lrcUrl'] ='mp3/'.$value['s_name'].'/'. $value['album_name'].'/'.$value['name'].'.lrc';
                $list[$key]['url'] ='mp3/'.$value['s_name'].'/'. $value['album_name'].'/'.$value['name'].'.mp3';
                $list[$key]['time'] = date('Y-m-d H:i', $value['create_at']);

//                $map['lrcUrl'] = 'mp3/'.$info['s_name'].'/'. $info['album_name'].'/'.$info['name'].'.lrc';;
//                $map['url'] ='mp3/'.$info['s_name'].'/'. $info['album_name'].'/'.$info['name'].'.mp3';
//                $map['name'] = $info['name'];
//                $map['author'] = $info['s_name'];
//                $map['fengmian'] ='mp3/fengmian/'. $info['fengmianUrl'];
//                $map['collect'] = $info['collect'];
            }

            Utitls::sendJson(200, ['list' => $list, 'totalPage' => ceil($count / $pagesize), 'count' => $count,'cureentPage'=>$info->currentPage()]);
        } else {
            Utitls::sendJson(500, []);
        }
    }
    //add歌曲
    public function add()
    {
        $params = Request::param();
        $res = \app\api\model\Music::createMusic($params);
        if ($res) {
            Utitls::sendJson(200,$res);
        }
        Utitls::sendJson(500);
    }

    //歌曲信息
      public function getSongInfo()
    {
        $params = Request::param();
        $res = db('music')
            ->alias('m')
            ->join('singer s','s.id = m.author_id')
            ->join('album a', 'a.id = m.album_id')
            ->field('m.*,s.name s_name,s.fengmianUrl,a.name album_name')
            ->where(['m.status' => 1,'m.id'=>$params['m_id']])
            ->find();
        if ($res) {
            $map['id'] = $res['id'];
            $map['lrcUrl'] = 'mp3/'.$res['s_name'].'/'. $res['album_name'].'/'.$res['name'].'.lrc';;
            $map['url'] ='mp3/'.$res['s_name'].'/'. $res['album_name'].'/'.$res['name'].'.mp3';
            $map['name'] = $res['name'];
            $map['author'] = $res['s_name'];
            $map['fengmian'] ='mp3/fengmian/'. $res['fengmianUrl'];
            $map['collect'] = $res['collect'];
            Utitls::sendJson(200,$map);
        }
        Utitls::sendJson(500);
    }
    //收藏歌曲
        public function collectSong()
    {
        $params = Request::param();
        $info = db('music')->where(['status' => 1,'id'=>$params['m_id']])->find();
        if ($info) {
           $res = db('music')->where(['status' => 1,'id'=>$params['m_id']])->update(['collect'=>$params['collect']]);
            if ($res) {
                Utitls::sendJson(200,$res);
            }
            
        }
        Utitls::sendJson(500);
    }
}