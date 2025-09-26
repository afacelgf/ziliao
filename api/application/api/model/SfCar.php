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

class SfCar extends Model
{
    public static function createSfCar($params)
    {
        $map['uuid'] =  User::getUuid($params['openid']);
        $map['cf_lat'] = $params['cf_lat'];
        $map['cf_lng'] = $params['cf_lng'];
        $map['cf_location'] = $params['cf_location'];
        $map['cf_time'] = strtotime($params['cf_time']);
        $map['md_lat'] = $params['md_lat'];
        $map['md_lng'] = $params['md_lng'];
        $map['md_location'] = $params['md_location'];
        $map['type'] = $params['type'];
        $map['seat'] = $params['seat'];
        $map['number'] = $params['number'];
        $map['phone'] = $params['phone'];
        $map['money'] = $params['money'];
        $map['contact'] = $params['contact'];
        $map['beizhu'] = $params['beizhu'];
        $map['create_at'] = time();
        $map['status'] = 0;
        return self::insertGetId($map);
    }

    public static function updateSfCar($params)
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
