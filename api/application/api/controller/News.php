<?php
/**
 * Created by PhpStorm.
 * User: Xs___
 * Date: 2019/11/27
 * Time: 22:56:21
 * Desc:
 */

namespace app\api\controller;

use app\api\model\NewsType;
use app\api\model\UsersWallet;
use app\Utitls;
use function PHPSTORM_META\elementType;
use think\Cache;
use think\Db;
use think\facade\Config;
use think\facade\Request;

class News
{

//获取资讯类型
    public function type()
    {
        $params = Request::param();
        $res = \app\api\model\NewsType::where(['delete_at' => 0])->order('sort asc')->select();

        if ($res) {
            Utitls::sendJson(200,$res);
        }
        Utitls::sendJson(500);
    }

    //获取有新闻的类型
    public function haveNewstype()
    {
        $params = Request::param();

        $res = \app\api\model\NewsType::where(['delete_at' => 0])->order('sort asc')->select();
         $list=[];
        foreach ($res as $key =>$value){
            $count = \app\api\model\News::where(['status' => 1, 'tid' =>  $value['id']])->count();
            if($count>0){
                array_push($list,$value);
            }
        }
        if ($res) {
            Utitls::sendJson(200,$list);
        }
        Utitls::sendJson(500);
    }

//搜索资讯
    public function search()
    {
        $params = Request::param();
        if (isset($params['search']) && !empty($params['search'])) {
            $where[] = ['title|describe', 'like', '%' . $params['search']. '%'];
        }
        $where[] = ['delete_at','=', 0];
        $where[] = ['status','=', 1];
        $count = \app\api\model\News::where($where)->count();
        $info=db('news')->where($where)->order('id desc')->paginate($listRows = 8);

        $list=[];
        foreach ($info as $key =>$value){
            $list[$key]['title'] = $value['title'];
            $list[$key]['id'] = $value['id'];
            $list[$key]['tid'] = $value['tid'];
            $typeInfo= NewsType::where(['id'=>$value['tid']])->find();
            $list[$key]['typeName'] = $typeInfo['name'];
            $list[$key]['desc'] = $value['describe'];
            $list[$key]['time'] = date('Y-m-d H:i', $value['create_at']);
            $list[$key]['read_total'] =  $value['read_total'];
            //取收货地址的经纬度
            $distance = getDistance($params['lat'], $params['lng'], $value['lat'], $value['lng']);
            $list[$key]['distance'] = $distance;
        }
        array_multisort(array_column($list, 'distance'), SORT_ASC, $list);
        if ($list) {
            Utitls::sendJson(200,  ['list' => $list, 'page' => ceil($count / 8)]);
        }
        Utitls::sendJson(500);
    }

//    添加收藏
    public function addCollect()
    {
        $params = Request::param();
        $collect_news_ids = \app\api\model\User::where(['openid'=>$params['openid'],'delete_at'=>0])->value('collect_news_ids');
        if(empty($collect_news_ids)){
            $collect_news_ids =array();
            array_push($collect_news_ids,$params['id']);
        }else{
            $collect_news_ids =  json_decode($collect_news_ids,true);
            array_push($collect_news_ids,$params['id']);
        }

        $addData =json_encode($collect_news_ids,true);
        \app\api\model\User::where(['openid'=>$params['openid'],'delete_at'=>0])->update(
            [
                'collect_news_ids'=>$addData
            ]
        );

        $where = ['id'=>$params['id'],'delete_at'=>0];
        $collectCount = \app\api\model\News::where($where)->value('collectCount');
        $res = \app\api\model\News::where($where)->update(
            [
                'collectCount'=>$collectCount + 1
            ]
        );
        if ($res) {

            Utitls::sendJson(200);
        }
        Utitls::sendJson(500);
    }

//    添加点赞
    public function addZan()
    {
        $params = Request::param();


        $zan_news_ids = \app\api\model\User::where(['openid'=>$params['openid'],'delete_at'=>0])->value('zan_news_ids');
        if(empty($zan_news_ids)){
            $zan_news_ids =array();
            array_push($zan_news_ids,$params['id']);
        }else{
            $zan_news_ids =  json_decode($zan_news_ids,true);
            array_push($zan_news_ids,$params['id']);
        }

        $addData =json_encode($zan_news_ids,true);
        \app\api\model\User::where(['openid'=>$params['openid'],'delete_at'=>0])->update(
            [
                'zan_news_ids'=>$addData
            ]
        );

        $where = ['id'=>$params['id'],'delete_at'=>0];
        $zanCount = \app\api\model\News::where($where)->value('zanCount');
        $res = \app\api\model\News::where($where)->update(
            [
                'zanCount'=>$zanCount + 1
            ]
        );

        if ($res) {
            // 积分处理逻辑
            //根据id查文章主人，查主人的积分+，查积分log，添加记录
            $uuid = \app\api\model\News::where(['id'=>$params['id'],'delete_at'=>0])->value('uuid');
            if($uuid == \app\api\model\User::getUuid($params['openid'])){//点赞自己的文章不得积分

            }else{
                $userpoint = \app\api\model\UsersWallet::where(['uuid'=>$uuid,'delete_at'=>0])->value('point');
                \app\api\model\UsersWallet::where(['uuid'=>$uuid,'delete_at'=>0])->update(
                    [
                        'point'=>$userpoint+ \config('api.zanjifen')
                    ]
                );

                $data = ['uuid'=>$uuid,'type'=>'0','point'=>config('api.zanjifen'),'iorr'=>"1",'desc'=>'点赞获取积分','create_at'=>time()];
                \db('user_point_log')->insert($data, false, true, null);;

            }

            Utitls::sendJson(200);
        }
        Utitls::sendJson(500);
    }

    // 取消收藏资讯
    public function cancelCollect()
    {
        $params = Request::param();
        //取出自己的collect_news_ids，转数组
       $collect_news_ids = \app\api\model\User::where(['openid'=>$params['openid'],'delete_at'=>0])->value('collect_news_ids');
        //是否包含这个资讯id,包含就取消收藏 ，不包含就收藏数+1，collect_news_ids添加ID，更新表
        if($collect_news_ids){
           $arr =  json_decode($collect_news_ids,true);
//           删除数组某一个元素
           if(in_array($params['id'],$arr)){
               foreach ($arr as $k => $v) {
                   if ($v == $params['id']) {
                       unset($arr[$k]);
                   }
               }
               $addData =json_encode($arr,true);
               \app\api\model\User::where(['openid'=>$params['openid'],'delete_at'=>0])->update(
                   [
                       'collect_news_ids'=>$addData
                   ]
               );
               $where = ['id'=>$params['id'],'delete_at'=>0];
               $collectCount = \app\api\model\News::where($where)->value('collectCount');
               $res = \app\api\model\News::where($where)->update(
                   [
                       'collectCount'=>$collectCount - 1
                   ]
               );

               Utitls::sendJson(200);
           }else{
               Utitls::sendJson(201,['status'=>0]);
           }
        }else{
            Utitls::sendJson(500);
        }
    }

    // 添加
    public function add()
    {
        $params = Request::param();
        $res = \app\api\model\News::createNews($params);
        if ($res) {
            Utitls::sendJson(200);
        }
        Utitls::sendJson(500);
    }

    //取出个人的资讯
    public function myNews()
    {
        $params = Request::param();
        $uuid = \app\api\model\User::where(['openid'=>$params['openid']])->value('uuid');
        // status 0审核中，1审核通过，2拒绝
        $info = \app\api\model\News::where(['delete_at' => 0,'uuid'=>$uuid])->order('id desc')->select();
        $list=[];
        foreach ($info as $key =>$value){
            $comment_count = \app\api\model\NewsComments::where(['news_id'=>$value['id'],'delete_at' => 0])->count();
            $list[$key]['comment_count'] = $comment_count;
            $list[$key]['collectCount'] = $value['collectCount'];
            $list[$key]['title'] = $value['title'];
            $list[$key]['status'] = $value['status'];
            $list[$key]['statusTxt'] = $value['statusTxt'];
            $list[$key]['id'] = $value['id'];
            $list[$key]['refusetip'] = $value['refusetip'];
            $list[$key]['preimg'] = $value['preimg'];
            $list[$key]['tid'] = $value['tid'];
           $typeInfo= NewsType::where(['id'=>$value['tid']])->find();
            $list[$key]['typeName'] = $typeInfo['name'];

            $list[$key]['desc'] = $value['describe'];
            $list[$key]['time'] = date('Y-m-d', $value['create_at']);
            $list[$key]['upic'] = \app\api\model\User::where([ 'delete_at' => 0,'uuid'=>$value['uuid']])->value('avatar');
            $list[$key]['uname'] = \app\api\model\User::where([ 'delete_at' => 0,'uuid'=>$value['uuid']])->value('nickname');
            $list[$key]['content'] = json_decode($value['content']) ;
            $list[$key]['read_total'] =  $value['read_total'];
        }

        Utitls::sendJson(200, $list);
    }

    //删除资讯
    public function deleteMyNews()
    {
        $params = Request::param();
        $res = \app\api\model\News::where(['id' => $params['id']])->update(['delete_at' => 1]);
        if ($res) {
            Utitls::sendJson(200);
        }
        Utitls::sendJson(500);
    }

    //推荐的资讯
    public function tuijian()
    {
        $params = Request::param();
//      status 0审核中，1审核通过，2拒绝
        $info = \app\api\model\News::where(['delete_at' => 0,'status'=>1,'istuijian'=>1])->select();
        $list=[];

        foreach ($info as $key =>$value){
            $distance = getDistance($params['lat'], $params['lng'], $value['lat'], $value['lng']);
            if($distance<500){//范围小于200km
                $comment_count = \app\api\model\NewsComments::where(['news_id'=>$value['id'],'delete_at' => 0])->count();
                $list[$key]['comment_count'] = $comment_count;
                $list[$key]['collectCount'] = $value['collectCount'];
                $list[$key]['distance'] = $distance;
                $list[$key]['title'] = $value['title'];
                $list[$key]['id'] = $value['id'];
                $list[$key]['tid'] = $value['tid'];
                $typeInfo= NewsType::where(['id'=>$value['tid']])->find();
                $list[$key]['typeName'] = $typeInfo['name'];
                $list[$key]['preimg'] = $value['preimg'];

                $list[$key]['townName'] = db('town')->where(['delete_at' => 0,'id'=>$value['townid']])->value('name');
                $list[$key]['desc'] = $value['describe'];
                $list[$key]['time'] = date('Y-m-d H:i', $value['create_at']);
                $list[$key]['upic'] = \app\api\model\User::where([ 'delete_at' => 0,'uuid'=>$value['uuid']])->value('avatar');
                $list[$key]['uname'] = \app\api\model\User::where([ 'delete_at' => 0,'uuid'=>$value['uuid']])->value('nickname');
                $list[$key]['content'] = json_decode($value['content']) ;
                $list[$key]['read_total'] =  $value['read_total'];
            }else{

            }
        }
        array_multisort(array_column($list, 'read_total'), SORT_DESC, $list);
        $data = array_slice($list,0,3);
        Utitls::sendJson(200, $data);
    }

    //热门的资讯
    public function host()
    {
        $params = Request::param();
//      status 0审核中，1审核通过，2拒绝
        $info = \app\api\model\News::where(['delete_at' => 0,'status'=>1,'ishost'=>1])->select();
        $list=[];
        foreach ($info as $key =>$value){
            $comment_count = \app\api\model\NewsComments::where(['news_id'=>$value['id'],'delete_at' => 0])->count();
            $list[$key]['comment_count'] = $comment_count;

            $list[$key]['townName'] = db('town')->where(['delete_at' => 0,'id'=>$value['townid']])->value('name');

            $list[$key]['collectCount'] = $value['collectCount'];
            $list[$key]['title'] = $value['title'];
            $list[$key]['id'] = $value['id'];
            $list[$key]['tid'] = $value['tid'];
            $typeInfo= NewsType::where(['id'=>$value['tid']])->find();
            $list[$key]['typeName'] = $typeInfo['name'];
            $list[$key]['preimg'] = $value['preimg'];
            $list[$key]['desc'] = $value['describe'];
            $list[$key]['time'] = date('Y-m-d H:i', $value['create_at']);
            $list[$key]['upic'] = \app\api\model\User::where([ 'delete_at' => 0,'uuid'=>$value['uuid']])->value('avatar');
            $list[$key]['uname'] = \app\api\model\User::where([ 'delete_at' => 0,'uuid'=>$value['uuid']])->value('nickname');
            $list[$key]['content'] = json_decode($value['content']) ;
            $list[$key]['read_total'] =  $value['read_total'];
        }
        array_multisort(array_column($list, 'read_total'), SORT_DESC, $list);
        $data = array_slice($list,0,3);//0开始取，取3条
        Utitls::sendJson(200, $data);
    }

    //随机的资讯
    public function suijiNews()
    {
        $params = Request::param();
        $num = 5;    //需要抽取的默认条数
        $table = 'news';    //需要抽取的数据表
        $countcus = Db::name($table)->count();    //获取总记录数
        $min = Db::name($table)->min('id');    //统计某个字段最小数据
        if($countcus < $num){$num = $countcus;}
        $i = 1;
        $flag = 0;
        $ary = array();
        while($i<=$num){
            $rundnum = rand($min, $countcus);//抽取随机数
            if($flag != $rundnum){
                //过滤重复
                if(!in_array($rundnum,$ary)){
                    $ary[] = $rundnum;
                    $flag = $rundnum;
                }else{
                    $i--;
                }
                $i++;
            }
        }
        $info = Db::name($table)->where(['delete_at' => 0,'status'=>1])->select();
        $list=[];
        foreach ($info as $key =>$value){
            $comment_count = \app\api\model\NewsComments::where(['news_id'=>$value['id'],'delete_at' => 0])->count();
            $list[$key]['comment_count'] = $comment_count;
            $list[$key]['title'] = $value['title'];
            $list[$key]['collectCount'] = $value['collectCount'];
            $list[$key]['id'] = $value['id'];
            $list[$key]['tid'] = $value['tid'];
            $typeInfo= NewsType::where(['id'=>$value['tid']])->find();
            $list[$key]['typeName'] = $typeInfo['name'];
            $list[$key]['desc'] = $value['describe'];
            $list[$key]['preimg'] = $value['preimg'];
            $list[$key]['time'] = date('Y-m-d H:i', $value['create_at']);
            $list[$key]['upic'] = \app\api\model\User::where([ 'delete_at' => 0,'uuid'=>$value['uuid']])->value('avatar');
            $list[$key]['uname'] = \app\api\model\User::where([ 'delete_at' => 0,'uuid'=>$value['uuid']])->value('nickname');
            $list[$key]['content'] = json_decode($value['content']) ;
            $list[$key]['read_total'] =  $value['read_total'];
            $distance = getDistance($params['lat'], $params['lng'], $value['lat'], $value['lng']);
            $list[$key]['distance'] = $distance;
        }
        array_multisort(array_column($list, 'distance'), SORT_ASC, $list);
        Utitls::sendJson(200, $list);
    }

// 取出所有的资讯数据
    public function index()
    {
        $params = Request::param();
        $townid = $params['townid'];
        if ($townid==0){//全部乡镇
            $count = \app\api\model\News::where([ 'delete_at' => 0,'status'=>1])->count();
            $info = \app\api\model\News::where([ 'delete_at' => 0,'status'=>1])->order('top desc,id desc')->paginate(10);

        }else{
            $arr = [0,$townid];
//            $count = \app\api\model\Album::where([ 'delete_at' => 0,'townid'=>$townid ,'status'=>1])->whereIn('townid',$arr)->count();
            $count = \app\api\model\News::where([ 'delete_at' => 0,'status'=>1])->whereIn('townid',$arr)->count();
//            $info = \app\api\model\Album::where([ 'delete_at' => 0,'townid'=>$townid,'status'=>1])->order('top desc,id desc')->paginate(10);
            $info = \app\api\model\News::where([ 'delete_at' => 0,'status'=>1])->whereIn('townid',$arr)->order('top desc,id desc')->paginate(10);
        }

        $list=[];
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
            $list[$key]['tid'] = $value['tid'];
            $typeInfo= NewsType::where(['id'=>$value['tid']])->find();
            $list[$key]['typeName'] = $typeInfo['name'];

            if($value['top'] == 1 && time()>$value['startTopTimeStramp'] &&time()<$value['endToptimestamp'] ){ //置顶已经开始

                $list[$key]['top'] = 1;

            }else if($value['top'] == 1 && time()>$value['endToptimestamp']){ //置顶已经过期
                $count = \app\api\model\News::where([ 'delete_at' => 0,'id'=>$value['id']])->update(
                    [
                        'top'=>0,
                        'startTopTimeStramp'=>0,
                        'endToptimestamp'=>0,
                    ]);
                $list[$key]['top'] = 0;
            }else{//置顶未开始 ， 不置顶
                $list[$key]['top'] = 0;

            }
            $list[$key]['startTopTimeStramp'] = date('Y-m-d',$value['startTopTimeStramp']); ;
            $list[$key]['endToptimestamp'] = date('Y-m-d',$value['endToptimestamp']);

            $list[$key]['preimg'] = $value['preimg'];

            $list[$key]['releaseScr'] = $value['releaseScr'];

            $list[$key]['time'] = date('Y-m-d H:i', $value['create_at']);
            $list[$key]['read_total'] =  $value['read_total'];
            $list[$key]['content'] = json_decode($value['content']) ;
//            $distance = getDistance($params['lat'], $params['lng'], $value['lat'], $value['lng']);
//            $list[$key]['distance'] = $distance;
        }
        Utitls::sendJson(200,  ['list' => $list, 'page' => ceil($count / 10)]);
    }

//按类型取出的资讯
    public function getNewsByType(){
        $params = Request::param();
        $count = \app\api\model\News::where(['status' => 1, 'tid' =>  $params['tid']])->count();
        $info = \app\api\model\News::where(['status' => 1, 'tid' =>  $params['tid']])->order('id desc')->paginate(10);
        $list=[];
        foreach ($info as $key =>$value){
            $comment_count = \app\api\model\NewsComments::where(['news_id'=>$value['id'],'delete_at' => 0])->count();
            $list[$key]['comment_count'] = $comment_count;

            $list[$key]['townName'] = db('town')->where(['delete_at' => 0,'id'=>$value['townid']])->value('name');
            $list[$key]['preimg'] = $value['preimg'];
            $list[$key]['upic'] = \app\api\model\User::where([ 'delete_at' => 0,'uuid'=>$value['uuid']])->value('avatar');
            $list[$key]['uname'] = \app\api\model\User::where([ 'delete_at' => 0,'uuid'=>$value['uuid']])->value('nickname');
            $list[$key]['title'] = $value['title'];
            $list[$key]['collectCount'] = $value['collectCount'];
            $list[$key]['read_total'] =  $value['read_total'];
            $list[$key]['id'] = $value['id'];
            $list[$key]['content'] = json_decode($value['content']) ;
            $list[$key]['tid'] = $value['tid'];
            $typeInfo= NewsType::where(['id'=>$value['tid']])->find();
            $list[$key]['typeName'] = $typeInfo['name'];
            $list[$key]['desc'] = $value['describe'];
            $list[$key]['time'] = date('Y-m-d H:i', $value['create_at']);
            $distance = getDistance($params['lat'], $params['lng'], $value['lat'], $value['lng']);
            $list[$key]['distance'] = $distance;
        }
        Utitls::sendJson(200,  ['list' => $list, 'page' => ceil($count / 10)]);
    }

    //根据id获取详情
    public function detail()
    {
        $params = Request::param();
        $info = \app\api\model\News::where([ 'delete_at' => 0,'id'=>$params['id']])->find();

//是否已收藏
        $collect_news_ids = \app\api\model\User::where(['openid'=>$params['openid'],'delete_at'=>0])->value('collect_news_ids');
        $zan_news_ids = \app\api\model\User::where(['openid'=>$params['openid'],'delete_at'=>0])->value('zan_news_ids');
        //是否包含这个资讯id
        $arr =  json_decode($collect_news_ids,true);
        if($arr){
            $iscollect = in_array($params['id'],$arr);//是否包含
        }else{
            $iscollect = 0;
        }

        $zanArr =  json_decode($zan_news_ids,true);
        if($zanArr){
            $iszan = in_array($params['id'],$zanArr);//是否包含
        }else{
            $iszan = 0;
        }

//        \app\api\model\Album::where('id', $params['id'])->update(['read_total' => $readTotal]);
        if ($info){
            $comment_count = \app\api\model\NewsComments::where(['news_id'=>$info['id'],'delete_at' => 0])->count();
            $info['comment_count'] = $comment_count;
            $info['upic'] = \app\api\model\User::where([ 'delete_at' => 0,'uuid'=>$info['uuid']])->value('avatar');
            $info['uname'] = \app\api\model\User::where([ 'delete_at' => 0,'uuid'=>$info['uuid']])->value('nickname');


            $list['townName'] = db('town')->where(['delete_at' => 0,'id'=>$info['townid']])->value('name');

            $info['content'] = json_decode($info['content']) ;
            $info['time'] = date('Y-m-d H:i', $info['create_at']);
            //阅读数+1
            $info['read_total'] = $info['read_total'];
            $info['is_collect'] = $iscollect?1:0;
            $info['is_zan'] = $iszan?1:0;
            unset($info['istuijian'], $info['update_at'],$info['create_at'], $info['delete_at']);
            if($info['uuid']!= $params['openid']){//别人阅读才得积分和阅读量
                $this->increateClick();
            }
            Utitls::sendJson(200, $info);
        }else{
            Utitls::sendJson(500,null,'数据已被删除');
        }
    }

    /** 浏览量加一   三分钟内重复访问无效
     * @param $id  文章id
     */
    public function increateClick(){
        $id = Request::param('id');
        $openid = Request::param('openid');
        $uuid = \app\api\model\User::getUuid($openid);
        $point = \app\api\model\UsersWallet::where(['uuid' => $uuid, 'delete_at' => 0])->value('point');
        if($id){

            $sessonName = md5($id.$this->getip());
            if (!\think\facade\Cache::get($sessonName)) { //没有session进来 +1
                \think\facade\Cache::set($sessonName,time(),3600);
                $info = \app\api\model\News::where(['delete_at' => 0,'id'=>$id])->find();
                $readTotal = $info['read_total']+1;
                $info= Db('news')->where(['id'=>$id])->update(['read_total' => $readTotal]);//setInc()实现hits+1



                $res = \app\api\model\UsersWallet::where(['uuid' => $uuid, 'delete_at' => 0])->update(
                    [
                        'point' => $point + \config('api.readjifen'),
                    ]
                );

            } else if (time() - \think\facade\Cache::get($sessonName) < 1800) { //session创建了10秒内  不+1
                $de = time() - \think\facade\Cache::get($sessonName);

            }else{ //session创建了大于10秒  +1并清除这个session
                \think\facade\Cache::rm($sessonName);
                $info = \app\api\model\News::where(['delete_at' => 0,'id'=>$id])->find();
                $readTotal = $info['read_total']+1;
                $info= Db('news')->where(['id'=>$id])->update(['read_total' => $readTotal]);

                $res = \app\api\model\UsersWallet::where(['uuid' => $uuid, 'delete_at' => 0])->update(
                    [
                        'point' => $point + \config('api.readjifen'),
                    ]
                );
            }

        }
    }


//获取用户ip
    function getip() {
        static $realip;
        if (isset($_SERVER)) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else if (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $realip = $_SERVER['HTTP_CLIENT_IP'];
            } else {
                $realip = $_SERVER['REMOTE_ADDR'];
            }
        } else {
            if (getenv('HTTP_X_FORWARDED_FOR')) {
                $realip = getenv('HTTP_X_FORWARDED_FOR');
            } else if (getenv('HTTP_CLIENT_IP')) {
                $realip = getenv('HTTP_CLIENT_IP');
            } else {
                $realip = getenv('REMOTE_ADDR');
            }
        }
        return $realip;
    }

}
