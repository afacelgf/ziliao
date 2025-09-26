<?php
/**
 * Created by PhpStorm.
 * User: Xs___
 * Date: 2019/11/27
 * Time: 22:55:39
 * Desc:
 */

namespace app\api\model;

use think\Model;

class Kuaidi extends Model
{
    public static function createKuaidi($params)
    {
        $map['uuid'] =  User::getUuid($params['openid']);
        $map['location_id'] = $params['location_id'];
        $map['info_phone'] = $params['info_phone'];
        $map['info_name'] = $params['info_name'];
        $map['info_address'] = $params['info_address'];
        $map['kd_type'] = $params['kd_type'];
        $map['qujian_address'] = $params['qujian_address'];
        $map['song_date'] = $params['song_date'];
        $map['song_time'] = $params['song_time'];
        $date = $params['song_date'].' '.$params['song_time'];

        $map['songdao_time'] = strtotime($date);
        $map['money'] = $params['money'];
        $map['is_big'] = $params['is_big'];
        $map['other'] = $params['other'];
        $map['order_id'] = $params['order_id'];
        $map['create_at'] = time();
        $map['pay'] = 1;//支付成功才能生成
        $map['status'] = 0;
        return self::insertGetId($map);
    }


    public static function updateKuaidi($params)
    {

        $id = $params['id'];
       $res= self::where(['id'=>$id,'delete_at'=>0,'status'=>0])->find();
       if($res){
            $map['info_phone'] = $params['info_phone'];
            $map['info_name'] = $params['info_name'];
            $map['info_address'] = $params['info_address'];
            $map['kd_type'] = $params['kd_type'];
            $map['qujian_address'] = $params['qujian_address'];
            $map['song_date'] = $params['song_date'];
            $map['song_time'] = $params['song_time'];
            $map['money'] = $params['money'];
            $map['other'] = $params['other'];
           return self::where(['id'=>$id,'delete_at'=>0])->update($map);
        }else{
           return '';
       }


    }
}
