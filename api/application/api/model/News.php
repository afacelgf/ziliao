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

class News extends Model
{
    public static function createNews($params)
    {
//        $map['hotel_id'] = HotelsRooms::where(['id' => $params['id']])->value('hotel_id');
        $map['uuid'] = User::getUuid($params['openid']);
        $map['townid'] = $params['townid'];
        $map['tid'] = $params['tid'];
        $map['lat'] = $params['lat']>0?$params['lat']:0;
        $map['lng'] = $params['lng']>0?$params['lng']:0;
        $map['title'] = $params['title'];
        $map['content'] = $params['content'];
        $map['describe'] = $params['describe'];
        $map['preimg'] = $params['preimg'];
        $map['releaseScr'] = $params['releaseScr'];
        $map['create_at'] = time();
        return self::insertGetId($map);
    }

    public static function adminCreateNews($params)
    {

//        $map['hotel_id'] = HotelsRooms::where(['id' => $params['id']])->value('hotel_id');
        $map['uuid'] = User::getUuid($params['openid']);
        $map['townid'] = $params['townid'];
        $map['tid'] = $params['tid'];
        $map['lat'] = $params['lat']>0?$params['lat']:0;
        $map['lng'] = $params['lng']>0?$params['lng']:0;
        $map['title'] = $params['title'];
        $map['content'] = json_encode($params['content']);
        $map['describe'] = $params['describe'];
        $map['preimg'] = $params['preimg'];
        $map['releaseScr'] = $params['releaseScr'];
        $map['create_at'] = time();
        return self::insertGetId($map);
    }

    public static function updateNews($params)
    {
        $info = self::where(['id' => $params['id']])->find();
        if($info){
            $map['uuid'] = User::getUuid($params['openid']);
            $map['townid'] = $params['townid'];
            $map['tid'] = $params['tid'];
            $map['lat'] = $params['lat']>0?$params['lat']:0;
            $map['lng'] = $params['lng']>0?$params['lng']:0;
            $map['title'] = $params['title'];
            $map['content'] = $params['content'];
            $map['describe'] = $params['describe'];
            $map['preimg'] = $params['preimg'];
            $map['releaseScr'] = $params['releaseScr'];
            $map['create_at'] = time();
            return  self::where(['id' => $params['id']])->update($map);
        }
        return 0;
    }

    public static function adminUpdateNews($params)
    {
        $info = self::where(['id' => $params['id']])->find();
        if($info){
            $map['uuid'] = User::getUuid($params['openid']);
            $map['townid'] = $params['townid'];
            $map['tid'] = $params['tid'];
            $map['lat'] = $params['lat']>0?$params['lat']:0;
            $map['lng'] = $params['lng']>0?$params['lng']:0;
            $map['title'] = $params['title'];
            $map['content'] = json_encode($params['content']);
            $map['describe'] = $params['describe'];
            $map['preimg'] = $params['preimg'];
            $map['releaseScr'] = $params['releaseScr'];
            $map['create_at'] = time();
            return self::where(['id' => $params['id']])->update($map);
        }
        return 0;
    }
}
