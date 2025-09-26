<?php
/**
 * Created by PhpStorm.
 * Project: zhuyou
 * User: Xs2296
 * Date: 2020/07/07
 * Time: 14:37
 * Desc:
 */

namespace app\api\model;

use think\Model;

class Orders extends Model
{

    public static function createOrder($params)
    {
        $map['type'] = $params['type'];
        $map['payTip'] = $params['payTip'];
        $map['uuid'] = User::getUuid($params['openid']);
        $map['sn'] = order_number($params['openid']);
        //计算钱多少  1.置顶3天 2.置顶7天  3.上推荐1周 4.商家入驻一个月
        // 5.商家入驻3个月 6.商家入驻6个月 7-10.名片30-60-90-180 11.帮取快递费用 12.投放广告7天 13.投放广告1个月 14.用户vip30 15.其他
        $total = 0;
        switch ($params['type']){
            case 1:
                $total = config('api.top3');
                break;
            case 2:
                $total = config('api.top7');
                break;
            case 3:
                $total = config('api.tuijian7');
                break;
            case 4:
                $total = config('api.ruzhu30');
                break;
            case 5:
                $total = config('api.ruzhu90');
                break;
            case 6:
                $total = config('api.ruzhu180');
                break;
            case 7:
                $total = config('api.mingpian30');
                break;
            case 8:
                $total = config('api.mingpian60');
                break;
            case 9:
                $total = config('api.mingpian90');
                break;
            case 10:
                $total = config('api.mingpian180');
                break;
            case 11:
                $total = $params['money'];
                break;
            case 12:
                $total = config('api.ad7');
                break;
            case 13:
                $total = config('api.ad30');
                break;
            case 14:
                $total = config('api.vip30');
                break;
            case 15:
                $total = $params['money'];
                break;
            default:
                break;
        }
        $map['total'] =$total* 100;
        $map['create_at'] = time();
        return self::insertGetId($map);
    }

}
