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

class Shop extends Model
{
    //自动过滤掉不存在的字段
    protected $field = true;

    public static function createShop($params)
    {
        $map['uuid'] = User::getUuid($params['openid']);
        $map['name'] = $params['name'];
        $map['tip'] = $params['tip'];
        $map['info'] = $params['info'];
        $map['lat'] = $params['lat'];
        $map['lng'] = $params['lng'];
        $map['type'] = $params['type'];
        $map['phone'] = $params['phone'];
        $map['address'] = $params['address'];
        $map['shop_time'] = $params['shop_time'];
        $map['zhizhao'] = $params['zhizhao'];
        $map['townid'] = $params['townid'];
        $map['fengmian'] = $params['fengmian'];
        $map['content'] = $params['content'];
        $map['create_at'] = time();
        return self::insertGetId($map);
    }

    public static function updateShop($params)
    {
        $params['content'] = $params['content'];
        return self::update($params);
    }

}

