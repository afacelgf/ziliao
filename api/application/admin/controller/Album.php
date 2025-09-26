<?php

namespace app\admin\controller;

use app\Utitls;
use function PHPSTORM_META\elementType;
use think\facade\Request;

class Album
{
    // 取出所有的资讯数据
    public function index()
    {
        $params = Request::param();
        $type = $params['type'];
        $pagesize = 10;
//        0审核中 1通过 2拒绝 3直接删除
        if ($type==0){//未审核
            $count = \app\api\model\News::where([ 'delete_at' => 0,'status'=>0])->count();
            $info = \app\api\model\News::where([ 'delete_at' => 0,'status'=>0])->order('id desc')->paginate($pagesize);

        }else if ($type==1){//已审核
            $count = \app\api\model\News::where([ 'delete_at' => 0,'status'=>1])->count();
            $info = \app\api\model\News::where([ 'delete_at' => 0,'status'=>1])->order('id desc')->paginate($pagesize);

        }else{//已拉黑
            $count = \app\api\model\News::where([ 'delete_at' => 1])->count();
            $info = \app\api\model\News::where([ 'delete_at' => 1])->order('id desc')->paginate($pagesize);
        }

        $list=[];
        if ($info){
        foreach ($info as $key =>$value){
            $comment_count = \app\api\model\NewsComments::where(['news_id'=>$value['id'],'delete_at' => 0])->count();
            $list[$key]['comment_count'] = $comment_count;

            $list[$key]['townName'] = db('town')->where(['delete_at' => 0,'id'=>$value['townid']])->value('name');
            $list[$key]['upic'] = \app\api\model\User::where([ 'delete_at' => 0,'uuid'=>$value['uuid']])->value('avatar');
            $list[$key]['uname'] = \app\api\model\User::where([ 'delete_at' => 0,'uuid'=>$value['uuid']])->value('nickname');
            $list[$key]['title'] = $value['title'];
            $list[$key]['collectCount'] = $value['collectCount'];
            $list[$key]['id'] = $value['id'];
            $list[$key]['desc'] = $value['describe'];
            $list[$key]['top'] = $value['top'];
            $list[$key]['tid'] = $value['tid'];
            $list[$key]['preimg'] = $value['preimg'];
            $list[$key]['time'] = date('Y-m-d H:i', $value['create_at']);
            $list[$key]['read_total'] =  $value['read_total'];
            $list[$key]['content'] = json_decode($value['content']) ;

        }
            Utitls::sendJson(200,  ['list' => $list, 'page' => ceil($count / $pagesize),'total'=>$count]);
        }else{
            Utitls::sendJson(500,  []);
        }


    }

    //根据id获取详情
    public function detail()
    {
        $params = Request::param();
        $info = \app\api\model\News::where(['id'=>$params['id']])->find();


//        \app\api\model\Album::where('id', $params['id'])->update(['read_total' => $readTotal]);
        if ($info){

            $info['upic'] = \app\api\model\User::where([ 'delete_at' => 0,'uuid'=>$info['uuid']])->value('avatar');
            $info['uname'] = \app\api\model\User::where([ 'delete_at' => 0,'uuid'=>$info['uuid']])->value('nickname');

            $list['townName'] = db('town')->where(['delete_at' => 0,'id'=>$info['townid']])->value('name');
            $info['content'] = json_decode($info['content']) ;
            $info['time'] = date('Y-m-d H:i', $info['create_at']);
            //阅读数+1
            $info['read_total'] = $info['read_total'];
            $info['refusetip'] = $info['refusetip'];

            Utitls::sendJson(200, $info);
        }else{
            Utitls::sendJson(500,null,'数据已被删除');
        }
    }

    //设置是否通过  status=0审核中 1通过 2拒绝 3直接删除，refusetip:拒绝理由
    public function set()
    {
        $params = Request::param();
        $info = \app\api\model\News::where(['id'=>$params['id']])->find();
        if ($info){
            if($params['status'] == 1){
                $res= \app\api\model\News::where('id', $params['id'])->update(['status' => 1,'delete_at' => 0,'refusetip' => $params['refusetip']]);
                if($res){
                    Utitls::sendJson(200, $info,'设置成功');
                }else{
                    Utitls::sendJson(500,null,'设置失败');
                }
            }else if($params['status'] == 2){
                $res= \app\api\model\News::where('id', $params['id'])->update(['status' => 2,'delete_at' => 0,'refusetip' => $params['refusetip']]);
                if($res){
                    Utitls::sendJson(200, $info,'设置成功');
                }else{
                    Utitls::sendJson(500,null,'设置失败');
                }
            }else if($params['status'] == 3){
                $res= \app\api\model\News::where('id', $params['id'])->delete();
                if($res){
                    Utitls::sendJson(200, $info,'删除成功');
                }else{
                    Utitls::sendJson(500,null,'设置失败');
                }
            }

        }else{
            Utitls::sendJson(500,null,'数据已被删除');
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
    // 添加歌曲时，修改albumids字符
    public function addByName($name)
    {
        $map['name'] = $name;
        $map['create_at'] = time();
        $map['status'] = 1;
        return \app\admin\model\Album::insertGetId($map);
    }

    public function getIdByName($name){
        $res = \app\admin\model\Album::where(['name'=>$name])->field('id')->find();
        return $res['id'];
    }
    //    添加置顶
    public function setNewsTop()
    {
        $params = Request::param();
        $res = \app\api\model\News::where(['id'=>$params['id'],'delete_at'=>0])->update(
            [
                'top'=>1,
                'startTopTimeStramp'=>substr($params['startTopTimeStramp'], 0, 10) ,
                'endToptimestamp'=>substr($params['endToptimestamp'], 0, 10),
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
    public function agree(){

    }

}
