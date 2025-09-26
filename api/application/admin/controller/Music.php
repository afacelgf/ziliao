<?php
/**
 * Created by PhpStorm.
 * User: Xs___
 * Date: 2019/11/27
 * Time: 22:56:21
 * Desc:
 */

namespace app\admin\controller;

use app\api\model\Type;
use app\api\model\UserChargeType;
use app\api\model\UsersAddress;
use app\api\model\UserVip;
use app\Utitls;
use think\Controller;
use think\facade\Env;
use think\facade\Request;
//活动
class Music extends Controller
{
    //列表
    public function getList()
    {
        $params = Request::param();
//        $where[]=[];
        if (isset($params['query']) && !empty($params['query'])) {
            $where[] = ['name', 'like', '%' . $params['query'] . '%'];
        }
        $where[] = ['delete_at', '=', 0];
        $count = db('music')->alias('m')
            ->join('singer s','s.id = m.author_id')
            ->join('album a', 'a.id = m.album_id')->where($where)->count();
        $res = db('music')->alias('m')
            ->join('singer s','s.id = m.author_id')
            ->join('album a', 'a.id = m.album_id')
            ->field('m.*,s.name s_name,a.name a_name')
            ->where($where)
            ->order('m.id desc')
            ->paginate($params['pagesize'], false, ['page' => $params['pagenum']]);
                if ($res) {
            $list = [];
            foreach ($res as $key => $value) {
                $list[$key]['id'] = $value['id'];
                $list[$key]['url'] = $value['url'];
                $list[$key]['name'] = $value['name'];
                $list[$key]['author'] = $value['s_name'];
                $list[$key]['albumName'] = $value['a_name'];
                $list[$key]['playNum'] = $value['playNum'];
                $list[$key]['gedanName'] = $value['gedan_ids'];
                $list[$key]['sort'] = $value['sort'];
                $list[$key]['status'] = $value['status'] ;
            }
            if ($list) {
                Utitls::sendJson(200, ['list' => $list, 'count' => $count, 'page' => ceil($count / 10)]);
            }
            Utitls::sendJson(500);
        }
        Utitls::sendJson(500);
    }

    //删除活动
    public function delete()
    {
        $params = Request::param();
        $res = db('music')->where(['id' => $params['id']])->find();
        if ($res) {
            db('music')->where(['id' => $params['id']])->delete();
            Utitls::sendJson(200, '');
        }
        Utitls::sendJson(500);
    }
    //更新状态
    public function update()
    {
        $params = Request::param();
        $res = db('music')->where(['id' => $params['id']])->find();
        if ($res) {
            db('music')->where(['id' => $params['id']])->update(
                ['status'=>$params['status']]
            );
            Utitls::sendJson(200, '');
        }
        Utitls::sendJson(500);
    }

    //编辑活动
    public function detail()
    {
        $params = Request::param();
        $res = db('music')->where(['id' => $params['id']])->find();
        if ($res) {
            Utitls::sendJson(200, $res);
        }
        Utitls::sendJson(500, '', '不存在');
    }
    //编辑活动
    public function edit()
    {
        $params = Request::param();
        $sId = \app\admin\model\Singer::where(['name'=>$params['author']])->field('id')->find();
        if (!$sId) {
            Utitls::sendJson(500, '', '歌手不存在');
        }
        $albumId = \app\admin\model\Album::where(['name'=>$params['albumName']])->field('id')->find();
        if (!$albumId) {
            Utitls::sendJson(500, '', '专辑名不存在');
        }
        $res = db('music')->where(['id' => $params['id']])->find();
        if ($res) {
            db('music')->where(['id' => $params['id']])->update([
                'name' => $params['name'],
                'url' => $params['url'],
                'playNum' => $params['playNum'],
                'author_id' => $sId["id"],
                'album_id' => $albumId['id'],
                'gedan_ids' => $params['gedanName'],
                'status' => $params['status'],
                'update_at' => time(),
            ]);
            Utitls::sendJson(200, '','000'.$albumId.$sId);
        }
        Utitls::sendJson(500, '', '不存在');
    }

    //add满赠活动
    public function add()
    {
        $params = Request::param();
        if(!$params["albumName"]){
            $params["albumName"] = $params["name"];
        }
        //判断是否有歌手文件夹，没有就创建
        $authorPath = Env::get('root_path').'public/mp3/'.$params["author"];
//        $path = ROOT_PATH.'public/mp3/周杰伦';
        if(!file_exists($authorPath)) {
            //创建歌手文件夹，
            mkdir($authorPath,0755);
//            创建专辑文件夹
            mkdir($authorPath.'/'.$params["albumName"],0755);
            //插入一条专辑信息
            $album = new Album();
           $albumId = $album->addByName($params["albumName"]);
            //插入歌手信息
            $singer = new Singer();
            $singerId = $singer->addByName($params["author"],$albumId);
            $res = \app\admin\model\Music::createMusic($params,$albumId,$singerId);
            if ($res) {
                Utitls::sendJson(200,$res);
            }
            Utitls::sendJson(500);
        }else{
            $singer = new Singer();
            $singerId = $singer->getIdByName($params["author"]);
            //是否有专辑文件夹，没有就创建
            if(!file_exists($authorPath.'/'.$params["albumName"])) {
                mkdir($authorPath.'/'.$params["albumName"],0755);
                //插入一条专辑信息
                $album = new Album();
                $albumId = $album->addByName($params["albumName"]);
            }else{
                $album = new Album();
                $albumId = $album->getIdByName($params["albumName"]);
            }
            //更新歌手的专辑ids
            $singer->updateIdsByAlbumId($params["author"],$albumId);
//            var_dump($albumId,$singerId);
//            exit();
            $res = \app\admin\model\Music::createMusic($params,$albumId,$singerId);
            if ($res) {
                Utitls::sendJson(200,$res);
            }
            Utitls::sendJson(500);
        }
    }

    //更新状态
    public function updateStatus()
    {
        $params = Request::param();
        $res = \app\api\model\Zitidian::where(['id' => $params['id']])->find();
        if ($res) {
            \app\api\model\Zitidian::where(['id' => $params['id']])->update([
                'status' => $params['status']
            ]);
            if ($params['status']==0){
                //下架所有地址
                UsersAddress::where(['zitidianId'=>$params['id'],'status'=>1])->update([
                    'status'=>0
                ]);
            }else{
                //上架所有地址
                UsersAddress::where(['zitidianId'=>$params['id'],'status'=>0])->update([
                    'status'=>1
                ]);
            }
            Utitls::sendJson(200, '');
        }
        Utitls::sendJson(500);
    }
}
