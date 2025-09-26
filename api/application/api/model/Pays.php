<?php
/**
 * Created by PhpStorm.
 * Project: zhuyou
 * User: Xs2296
 * Date: 2020/06/13
 * Time: 20:13
 * Desc:
 */

namespace app\api\model;

use think\Model;

class Pays extends Model
{
    public static function createOrder($datas)
    {
        $datas['create_at'] = time();
        return self::insertGetId($datas);
    }
}
