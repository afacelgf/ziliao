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
use function Sodium\add;
use think\facade\Request;

class CarCard
{

    // 所有信息
    public function index()
    {
        $params = Request::param();
        $where[] = ['delete_at','=', 0];
        $where[] = ['status','=', 1];//审核通过
        $where[] = ['pay','=', 1];//已支付
        $info = \app\api\model\CarCard::where($where)->order('id desc')->paginate(2);
        $count =\app\api\model\CarCard::where($where)->order('id desc')->count();
//        $includeData =[];
        $list=[];
        foreach ($info as $key =>$value){
//            $location = \app\api\model\UsersAddress::where(['id'=>$value['location_id']])->find();
            //取收货地址的经纬度
            $distance = getDistance($params['lat'],$params['lng'], $value['b_lat'], $value['b_lng']);
            if($distance<500) { //推荐100公里之内的名片
                $list[$key]['id'] = $value['id'];
                $list[$key]['b_location'] = $value['b_location'];
                $list[$key]['e_location'] = $value['e_location'];
                $list[$key]['b_lat'] = $value['b_lat'];
                $list[$key]['b_lng'] = $value['b_lng'];
                $list[$key]['b_time'] = $value['b_time'];
                $list[$key]['car_num'] = $value['car_num'];
                $list[$key]['fenmian'] = $value['fenmian'];
                $list[$key]['phone'] = $value['phone'];
                $list[$key]['by_phone'] = $value['by_phone'];
                $list[$key]['tujingzhan'] = $value['tujingzhan'];
                $list[$key]['distance'] = $distance;
            }
        }
//        print_r($list);
        array_multisort(array_column($list, 'distance'), SORT_ASC, $list);
//        $list =  ascArray($list,'distance');
//        print_r('ssss----');
//        print_r($list);
//        exit();
//        array_push($includeData,$list);
        if ($list) {
            Utitls::sendJson(200, ['list' => $list, 'page' => ceil($count / 2)]);
        }
        Utitls::sendJson(500);
    }

    public function add()
    {
        $params = Request::param();
        $res = \app\api\model\CarCard::createCarCard($params);

        if ($res) {
            Utitls::sendJson(200);
        }
        Utitls::sendJson(500);
    }
}
