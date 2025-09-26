<?php
/**
 * Created by PhpStorm.
 * User: Xs___
 * Date: 2019/11/27
 * Time: 22:55:39
 * Desc:
 */

namespace app\ziliao\model;

use think\Model;

class Grade extends Model
{
    public static function createCarCard($params)
    {
        $map['uuid'] =  User::getUuid($params['openid']);
        $map['b_location'] = $params['b_location'];
        $map['e_location'] = $params['e_location'];
        $map['b_lat'] = $params['b_lat'];
        $map['b_lng'] = $params['b_lng'];
        $map['b_time'] = $params['b_time'];
        $map['car_num'] = $params['car_num'];
        $map['phone'] = $params['phone'];
        $map['money'] = $params['money'];
        $map['fenmian'] = $params['fenmian'];
        $map['by_phone'] = $params['by_phone'];
        $map['contact_phone'] = $params['contact_phone'];
        $map['tujingzhan'] = $params['tujingzhan'];
        $map['create_at'] = time();

        $map['status'] = 0;//0未审核
        return self::insertGetId($map);
    }

}
