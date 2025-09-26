<?php
/**
 * Created by PhpStorm.
 * User: Xs___
 * Date: 2019/11/27
 * Time: 22:55:00
 * Desc:
 */

namespace app\api\model;

use think\Model;

class User extends Model
{

    public static function reg($params)
    {
        $map['uid'] = $uuid = makeUuid();
        $map['name'] = $params['name'];
        $map['account'] = $params['account'];
        $map['password'] = md5($params['password']);
        $map['desc'] =$params['desc'];
        $map['fengmianIMG'] =$params['fengmianIMG'];
        $map['create_at'] = time();
        $map['status'] = 0;
        $map['identity'] = 0;
        $map['playNum'] = 0;
        self::insertGetId($map);
        return $uuid;
    }

    public static function getUuid($openid)
    {
        return self::where(['openid' => $openid, 'delete_at' => 0])->order('id desc')->value('uuid');
    }

    public static function getOpenid($uuid)
    {
        return self::where(['uuid' => $uuid, 'delete_at' => 0])->value('openid');
    }
}
