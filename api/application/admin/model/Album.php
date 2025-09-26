<?php

namespace app\admin\model;

use app\api\model\Music;
use app\Utitls;
use think\facade\Request;
use think\Model;

class Album extends Model
{
    //歌单列表
     public function gedanList()
    {
        $params = Request::param();
        $res = db('music_gedan')->where(['status' => 1])->order('id desc')->select();
        if ($res) {
            Utitls::sendJson(200,$res);
        }
        Utitls::sendJson(500);
    }
    //收藏列表123
      public function collectList()

    {
        $params = Request::param();
        $res = db('music_list')->where(['status' => 1,'collect'=>1])->order('id desc')->select();
        if ($res) {
            Utitls::sendJson(200,$res);
        }
        Utitls::sendJson(500);
    }
    //歌曲列表-根据歌单id
     public function songList()
    {
        $params = Request::param();
        $res = db('music_list')->where(['status' => 1,'gedan_id'=>$params['gedan_id']])->order('sort desc')->select();
        if ($res) {
            Utitls::sendJson(200,$res);
        }
        Utitls::sendJson(500);
    }
    //add歌曲
    public function add()
    {
        $params = Request::param();
        $res = Music::createMusic($params);
        if ($res) {
            Utitls::sendJson(200,$res);
        }
        Utitls::sendJson(500);
    }

    //歌曲信息
      public function getSongInfo()
    {
        $params = Request::param();
        $res = db('music_list')->where(['status' => 1,'id'=>$params['m_id']])->find();
        if ($res) {
            Utitls::sendJson(200,$res);
        }
        Utitls::sendJson(500);
    }
    //收藏歌曲
        public function collectSong()
    {
        $params = Request::param();
        $info = db('music_list')->where(['status' => 1,'id'=>$params['m_id']])->find();
        if ($info) {
           $res = db('music_list')->where(['status' => 1,'id'=>$params['m_id']])->update(['collect'=>$params['collect']]);
            if ($res) {
                Utitls::sendJson(200,$res);
            }
            
        }
        Utitls::sendJson(500);
    }
}