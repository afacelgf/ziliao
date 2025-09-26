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

class Music extends Model
{
    public static function createMusic($params)
    {
        $map['name'] = $params['name'];
        $map['url'] = $params['url'];
        $map['fengmian'] = $params['fengmian'];
        $map['author'] = $params['author']?$params['author']:"佚名";
        $map['type'] = 1;
        $map['gedan_id'] = $params['gedan_id'];
        $map['status'] = 1;
        $map['sort'] = 1;
        $map['collect'] = 0;
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
            return  self::where(['id' => $params['id']])->update($map);
        }
        return 0;
    }
}
