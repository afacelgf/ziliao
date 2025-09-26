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

class Zitidian extends Model
{


    public static function createZitidian($params)
    {
        $map['name'] = $params['name'];
        $map['phone'] = $params['phone'];
        $map['nickname'] = $params['name'];
        $map['shenfenNumber'] = $params['shenfenNumber'];
//        $map['fensi'] = $params['fensi'];

        $map['lng'] = $params['lng'];
        $map['lat'] = $params['lat'];
        $map['address'] = $params['address'];
        $map['shopPic'] =config('api.base_url'). $params['shopPic'];
        $map['shenfenPic'] =config('api.base_url').$params['shenfenPic'];
        $map['create_at'] = time();
        return self::insertGetId($map);
    }


}
