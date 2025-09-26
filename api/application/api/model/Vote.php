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

class Vote extends Model
{
    //自动过滤掉不存在的字段
    protected $field = true;

    public static function createVote($params)
    {
        $map = $params;
        $map['uuid'] = User::getUuid($params['openid']);
        unset($map['openid']);
        unset($map['api_rand']);
        unset($map['api_sign']);
        unset($map['api_times']);
        $map['create_at'] = time();
        return self::insertGetId($map);
    }

    public static function updateVote($params)
    {
        return self::update($params);
    }

}

