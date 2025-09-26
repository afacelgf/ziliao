<?php
/**
 * Created by PhpStorm.
 * User: Xs___
 * Date: 2019/11/27
 * Time: 22:56:21
 * Desc:
 */

namespace app\api\controller;

use app\api\model\KuaidiType;
use app\Utitls;
use think\facade\Request;

class SfCar
{
    //添加
    public function add()
    {
        $params = Request::param();
        $res = \app\api\model\SfCar::createSfCar($params);
        if ($res) {
            Utitls::sendJson(200);
        }
        Utitls::sendJson(500);
    }

//删除
    public function delete()
    {
        $params = Request::param();

        $res = \app\api\model\SfCar::where(['id'=>$params['id'],'delete_at'=>0])->find();
        if($res){
           $info= \app\api\model\SfCar::where(['id'=>$params['id'],'delete_at'=>0])->update(
                ['delete_at'=>1]
            );
           if($info){
               Utitls::sendJson(200);
           }else{
               Utitls::sendJson(500);
           }
        }else {
            Utitls::sendJson(501);
        }

    }

    // 所有信息
    public function index()
    {
//        status：0没人接单 1已接单
        $params = Request::param();
        $where[] = ['delete_at', '=', 0];
        $where[] = ['status', '=', 0];
        $count = \app\api\model\SfCar::where($where)->count();
        $info = \app\api\model\SfCar::where($where)->order('cf_time asc')->paginate($listRows = 1);
        $list = [];
        foreach ($info as $key => $value) {
            if ($value['cf_time'] - time() > 0) {//出发时间小于现在时间
                $list[$key]['id'] = $value['id'];
                $list[$key]['cf_location'] = $value['cf_location'];
                $list[$key]['md_location'] = $value['md_location'];
                $list[$key]['cf_time'] = date('Y-m-d H:i:s', $value['cf_time']);
                $list[$key]['type'] = $value['type'];
                $list[$key]['money'] = $value['money'];
                $list[$key]['number'] = $value['number'];
                $distance = getDistance($params['lat'], $params['lng'], $value['cf_lat'], $value['cf_lng']);
                $list[$key]['distance'] = $distance;
                $list[$key]['seat'] = $value['seat'];
                $list[$key]['read_total'] = $value['read_total'];
            } else {//过期删除
                $res = \app\api\model\SfCar::where(['id' => $value['id']])->update(
                    ['delete_at' => 1]);
            }
        }
        array_multisort(array_column($list, 'distance'), SORT_ASC, $list);

        if ($list) {
            Utitls::sendJson(200, ['list' => $list, 'page' => ceil($count / 1)]);
        }
        Utitls::sendJson(500);
    }

    // 我的订单-顺风车
    public function myRecord()
    {
        $params = Request::param();
        $uuid =  \app\api\model\User::getUuid($params['openid']);

        $info = \app\api\model\SfCar::where(['uuid'=>$uuid,'delete_at'=>0])->order('cf_time desc')->select();
        $list=[];
        foreach ($info as $key => $value) {
            if ($value['cf_time'] - time() > 0) {//出发时间小于现在时间
                $list[$key]['id'] = $value['id'];
                $list[$key]['cf_location'] = $value['cf_location'];
                $list[$key]['md_location'] = $value['md_location'];
                $list[$key]['cf_time'] = date('Y-m-d H:i:s', $value['cf_time']);
                $list[$key]['type'] = $value['type'];
                $list[$key]['money'] = $value['money'];
                $list[$key]['number'] = $value['number'];
                $distance = getDistance($params['lat'], $params['lng'], $value['cf_lat'], $value['cf_lng']);
                $list[$key]['distance'] = $distance;
                $list[$key]['seat'] = $value['seat'];
                $list[$key]['read_total'] = $value['read_total'];
            } else {//过期删除
                $res = \app\api\model\SfCar::where(['id' => $value['id']])->update(
                    ['delete_at' => 1]);
            }
        }
        if ($list) {
            Utitls::sendJson(200,$list);
        }
        Utitls::sendJson(500);
    }


//搜索资讯
    public function search()
    {
        $params = Request::param();
        if (isset($params['search']) && !empty($params['search'])) {
            $where[] = ['cf_location|md_location', 'like', '%' . $params['search'] . '%'];
        }
        $where[] = ['delete_at', '=', 0];
        $where[] = ['status', '=', 0];
        $count = \app\api\model\SfCar::where($where)->count();
        $info = \app\api\model\SfCar::where($where)->order('cf_time asc')->paginate($listRows = 8);
        $list = [];
        foreach ($info as $key => $value) {
            if ($value['cf_time'] - time() > 0) {//出发时间小于现在时间
                $list[$key]['id'] = $value['id'];
                $list[$key]['cf_location'] = $value['cf_location'];
                $list[$key]['md_location'] = $value['md_location'];
                $list[$key]['cf_time'] = date('Y-m-d H:i:s', $value['cf_time']);
                $list[$key]['type'] = $value['type'];
                $list[$key]['money'] = $value['money'];
                $list[$key]['number'] = $value['number'];
                $list[$key]['seat'] = $value['seat'];
                //取收货地址的经纬度
                $distance = getDistance($params['lat'], $params['lng'], $value['cf_lat'], $value['cf_lng']);
                $list[$key]['distance'] = $distance;
                $list[$key]['read_total'] = $value['read_total'];
            } else {//过期删除
                $res = \app\api\model\SfCar::where(['id' => $value['id']])->update(
                    ['delete_at' => 1]);
            }
        }
        array_multisort(array_column($list, 'distance'), SORT_ASC, $list);
        if ($list) {
            Utitls::sendJson(200, ['list' => $list, 'page' => ceil($count / 8)]);
        }
        Utitls::sendJson(500);
    }

    // 挑选3条热门的在首页显示
    public function hostList()
    {
        $params = Request::param();
        $where[] = ['delete_at', '=', 0];
        $where[] = ['status', '=', 0];
        $info = \app\api\model\SfCar::where($where)->order('id desc')->limit(3)->select();
        $list = [];
        foreach ($info as $key => $value) {
            if ($value['cf_time'] - time() > 0) {//出发时间小于现在时间
                $list[$key]['id'] = $value['id'];
                $list[$key]['cf_location'] = $value['cf_location'];
                $list[$key]['md_location'] = $value['md_location'];
                $list[$key]['cf_time'] = date('Y-m-d H:i:s', $value['cf_time']);
                $list[$key]['type'] = $value['type'];
                $list[$key]['money'] = $value['money'];
                $list[$key]['number'] = $value['number'];
                $list[$key]['seat'] = $value['seat'];
                $list[$key]['read_total'] = $value['read_total'];
            } else {//过期删除
                $res = \app\api\model\SfCar::where(['id' => $value['id']])->update(
                    ['delete_at' => 1]);
            }
        }
        if ($list) {
            Utitls::sendJson(200, $list);
        }
        Utitls::sendJson(500);
    }


    // 根据id获取
    public function infoById()
    {
        $params = Request::param();
        $info = \app\api\model\SfCar::where(['id' => $params['id'], 'delete_at' => 0])->find();
        if ($info) {
            $readTotal = $info['read_total'] + 1;
            $this->increateClick();

            $map['id'] = $info['id'];
            $map['cf_location'] = $info['cf_location'];
            $map['cf_lat'] = $info['cf_lat'];
            $map['cf_lng'] = $info['cf_lng'];

            $map['md_location'] = $info['md_location'];
            $map['md_lat'] = $info['md_lat'];
            $map['md_lng'] = $info['md_lng'];
            $map['cf_time'] = date('Y-m-d H:i:s', $info['cf_time']);;
            $map['type'] = $info['type'];
            $map['money'] = $info['money'];
            $map['number'] = $info['number'];
            $map['seat'] = $info['seat'];
            $map['read_total'] = $readTotal;
            $map['status'] = $info['status'];
            $map['contact'] = $info['contact'];
            $map['phone'] = $info['phone'];
            $map['beizhu'] = $info['beizhu'];
//    $location = \app\api\model\UsersAddress::where(['id'=>$info['location_id']])->find();
            //取收货地址的经纬度
//    $distance = getDistance($params['lat'],$params['lng'], $info['cf_lat'], $info['cf_lng']);
//    $map['distance'] = $distance;

        }

        if ($map) {
            Utitls::sendJson(200, $map);
        }
        Utitls::sendJson(500);
    }
//================================================================


    /** 浏览量加一   三分钟内重复访问无效
     * @param $id  快递id
     */
    public function increateClick()
    {
        $id = Request::param('id');
        if ($id) {
            $sessonName = md5($id . getip());
            if (!\think\facade\Session::has($sessonName)) { //没有session进来 +1
                \think\facade\Session::set($sessonName, time());
                $info = \app\api\model\SfCar::where(['delete_at' => 0, 'id' => $id])->find();
                $readTotal = $info['read_total'] + 1;
                $info = Db('sf_car')->where(['id' => $id])->update(['read_total' => $readTotal]);//setInc()实现hits+1
            } else if (time() - \think\facade\Session::get($sessonName) < 180) { //session创建了10秒内  不+1

            } else { //session创建了大于10秒  +1并清除这个session
                \think\facade\Session::delete($sessonName);
                $info = \app\api\model\SfCar::where(['delete_at' => 0, 'id' => $id])->find();
                $readTotal = $info['read_total'] + 1;
                $info = Db('sf_car')->where(['id' => $id])->update(['read_total' => $readTotal]);
            }
        }
    }
}
