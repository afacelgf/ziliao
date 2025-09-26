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

class NewsComments extends Model
{
    public static function createComment($params)
    {

        $uuid = User::getUuid($params['openid']);
        $map['user_id'] = $uuid;
        if($params['p_uid'] == $uuid){
            return 500;
        }else{
            $map['news_id'] = $params['news_id'];
            $map['p_uid'] = $params['p_uid'];
            $map['content'] = $params['content'];
            $map['add_time'] = time();
            return self::insertGetId($map);
        }

    }
}