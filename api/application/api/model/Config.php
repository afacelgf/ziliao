<?php
/**
 * Created by PhpStorm.
 * User: Xs___
 * Date: 2019/11/28
 * Time: 19:50:21
 * Desc:
 */

namespace app\api\model;

use think\Model;

class Config extends Model
{


    public static function getValues($name, $value)
    {
        $values = self::where(['name' => $name])->value('value');
        $arr = explode(',', $values);
        return $arr[$value];
    }

//    获取Access_token
    public function getAccess_token()
    {
        if (file_exists('access_token.json')) {
//            var_dump('存在');
            // 从文件中读取数据到PHP变量
            $json_string = file_get_contents('access_token.json');
            //// 把JSON字符串转成PHP数组
            $data = json_decode($json_string, true);
            $time = $data['expires_timstramp'];
            if ($time - time() > 30) {
                return $data['access_token'];
            } else {
                return $this->getNewAccess_token();
            }
        } else {
            return $this->getNewAccess_token();
        }
    }


    protected function getNewAccess_token()
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . config('api.app_id') . '&secret=' . config('api.secret');
        $res = curlRequest($url, 'GET');
        $data = json_decode($res, true);

        $map['access_token'] = $data['access_token'];
        $map['expires_timstramp'] = time() + $data['expires_in'];
        $response = json_encode($map, true);
// 写入文件
        file_put_contents('access_token.json', $response);
        return $map['access_token'];
    }

    //日期转时间戳
    public static function DateToTimestramp($date)
    {
        return  strtotime($date);
    }

    //时间戳转日期
    public static function timestrampToDate($timestramp)
    {
        return  date('Y-m-d H:i:s', $timestramp);
    }


}
