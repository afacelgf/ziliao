<?php
/**
 * Created by PhpStorm.
 * User: Xs___
 * Date: 2019/08/25
 * Time: 09:51
 * Desc:
 */

namespace app;
class Utitls
{
    //通用返回方法
    public static function sendJson($code = 200, $result = null, $msg = null)
    {
        if ($msg) {
            $message = $msg;
        } else {
            if ($code == 200) {
                $message = 'success';
            } else {
                $message = 'error';
            }
        }
        $data = [
            'code' => $code,
            'message' => $message,
            'result' => $result
        ];
        exit(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }


    //对数组进行排序
    public static function argSort($para)
    {
        ksort($para);
        reset($para);
        return $para;
    }


    //api签名
    public static function yzSign($params)
    {
        $sign = $params['api_sign'];
        unset($params['api_sign']);
//        $params['content'] =json_encode($params['content']);
//        unset($params['openid']);
        //数组排序
        $para_sort = self::argSort($params);

        //拼接成字符串
        $prestr = implode($para_sort);

        //sha1加密
        $sha1 = sha1(config('api.api_key') . $prestr);
//        dump(config('api.api_key') . $prestr);
        //Md5
        $md5 = md5($sha1);
//        dump($md5);

        if ($sign == $md5) {
            return true;
        }
        return false;
    }
    //字符串通过','转换为数组
    public static function StrToArr($str){
        if (!$str) return [];
        $arr = explode(',', $str);
       return  $arr;
    }

    //数组->字符串,通过','
    public static function ArrToStr($arr){
        if (count($arr)==0) return '';
        $str = implode(',', $arr);
        return  $str;
    }
}
