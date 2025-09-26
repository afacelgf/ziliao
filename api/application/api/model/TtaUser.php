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

class TtaUser extends Model
{

    public static function register($openid)
    {
        $map['uuid'] = $uuid = makeUuid();
        $map['openid'] = $openid;
        $map['nickname'] = '侠客'.makeCardPasswordByDigit(6);
        $addTime = strtotime('+3 day');
        $map['vipEndTime'] = date("Y-m-d H:i:s",$addTime);
        $map['isVip'] = 1;
        // 会员类型,0普通用户，1新用户送3天，2，正式会员，3终身会员
        $map['vipType'] = 1;
        $map['create_at'] = time();
        $map['status'] = 1;
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
