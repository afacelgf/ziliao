<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件


/**
 *视频上传
 * @param $files
 * @param string $path
 * @param array $imagesExt
 * @return string
 */
function upload_file($files, $path = "../public/uploads/video", $imagesExt = ['mp4'])
{
    // 判断错误号
    if ($files['error'] == 00) {
        $ext = strtolower(pathinfo($files['name'], PATHINFO_EXTENSION));
        // 判断文件类型
        if (!in_array($ext, $imagesExt)) {
            return 1000;//非法文件类型
        }
//        print_r();
        if ($files['size'] > 100000000) {
            return 1002;//文件太大
        }

        // 判断是否存在上传到的目录
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        // 生成唯一的文件名
        $fileName = md5(uniqid(microtime(true), true)) . '.' . $ext;
        // 将文件名拼接到指定的目录下
        $destName = $path . "/" . $fileName;
        // 进行文件移动
        if (!move_uploaded_file($files['tmp_name'], $destName)) {
            return 1001;//文件上传失败
        }
        return $destName;//上传成功，返回上传路径
    } else {
        // 根据错误号返回提示信息
        switch ($files['error']) {
            case 1:
                echo 2000;//上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值
                break;
            case 2:
                echo 2001;//上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值
                break;
            case 3:
                echo 2002;//文件只有部分被上传
                break;
            case 4:
                echo 2003;//没有文件被上传
                break;
            case 6:
                echo 2004;//找不到临时文件夹
                break;
            case 7:
                echo 2005;//文件写入错误
                break;
        }
    }
}

//生成UUID
function makeUuid()
{
    $chars = md5(uniqid(mt_rand(), true));
    return substr($chars, 0, 8) . substr($chars, 8, 4) . substr($chars, 12, 4) . substr($chars, 16, 4) . substr($chars, 20, 8);
}


//唯一订单号
function orderNo()
{
    return date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8) . rand(1000, 9999);
}


//生成订单号
function order_number($openid)
{
    //date('Ymd',time()).time().rand(10,99);//18位
    return md5($openid . time() . rand(10, 99));//32位
}

//随机字符串
function randomStr($length = 4)
{
    $chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
        $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return str_shuffle($str);
}

function curls($url, $data_string)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'X-AjaxPro-Method:ShowList',
        'User-Agent:Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.154 Safari/537.36'
    ));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

//curl
function curlRequest($url, $method, $params = [], $https = false)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    if ($https) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
    } else {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    switch ($method) {
        case "GET" :
            curl_setopt($ch, CURLOPT_HTTPGET, 1);
            break;
        case "POST":
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            break;
        case "PUT" :
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            break;
        case "DELETE":
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            break;
    }
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}


//lng经度，lat纬度
function getAdcode($lat, $lng)
{
    $url = 'https://apis.map.qq.com/ws/geocoder/v1/?location=' . $lat . ',' . $lng . '&key=MKJBZ-6VTK4-WDHU4-XU357-LWRF6-HJBBS';
    $curl = curlRequest($url, 'GET', [], false);
    $json = json_decode($curl, true);
//    print_r($json);
    if (isset($json['result']['ad_info']['adcode']) && isset($json['result']['ad_info']['district'])) {
        return ['code' => $json['result']['ad_info']['adcode'], 'district' => $json['result']['ad_info']['district']];
    }
    return false;
}

/**
 * 根据经纬度获取之间距离
 * @param $lat1
 * @param $lng1
 * @param $lat2
 * @param $lng2
 * @return int
 */
function getDistance($lat1, $lng1, $lat2, $lng2)
{

    // 将角度转为狐度
    $radLat1 = deg2rad($lat1);// deg2rad()函数将角度转换为弧度
    $radLat2 = deg2rad($lat2);
    $radLng1 = deg2rad($lng1);
    $radLng2 = deg2rad($lng2);

    $a = $radLat1 - $radLat2;
    $b = $radLng1 - $radLng2;

    $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137;

    return round($s, 2);;

}

/*
 * 对数组的某一列进行排序，
 * SORT_ASC升序
 * SORT_DESC降序
 * $argu列的键
 */

function ascArray($list, $argu)
{
    //先用array_column 多维数组按照纵向（列）取出
    $date = array_column($list, $argu);
    array_multisort($date, SORT_ASC, $list);
}

/*
 * 对数组的某一列进行降序排序，
 * SORT_ASC升序
 * SORT_DESC降序
 * $argu列的键
 */

function descArray($list, $argu)
{
    //先用array_column 多维数组按照纵向（列）取出
    $date = array_column($list, $argu);
    array_multisort($date, SORT_DESC, $list);
}


function uni($array, $nums, $unique = true)
{

    $newarray = array();
    if ((bool)$unique) {
        $array = array_unique($array);// 移除数组中重复的值，并且返回数组。
    }
    if (shuffle($array)) {// return bool
        for ($i = 0; $i < count($array); $i++) {
            $newarray[] = $array[$i];
        }
    }
    return $newarray;

}

//获取用户ip
function getip()
{
    static $realip;
    if (isset($_SERVER)) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $realip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $realip = $_SERVER['REMOTE_ADDR'];
        }
    } else {
        if (getenv('HTTP_X_FORWARDED_FOR')) {
            $realip = getenv('HTTP_X_FORWARDED_FOR');
        } else if (getenv('HTTP_CLIENT_IP')) {
            $realip = getenv('HTTP_CLIENT_IP');
        } else {
            $realip = getenv('REMOTE_ADDR');
        }
    }
    return $realip;
}

//随机生成不重复的几位卡密
function makeCardPasswordByDigit($digit = 6)
{
    $code = 'abcdefghijklmnpqrstuvwxyz';
    $rand = $code[rand(0, 24)]
        . strtoupper(dechex(date('m')))
        . date('d') . substr(time(), -5)
        . substr(microtime(), 2, 5)
        . sprintf('%02d', rand(0, 99));
    for (
        $a = md5($rand, true),
        $s = '123456789abcdefghijklmnpqrstuvwxyz',
        $d = '',
        $f = 0;
        $f < $digit;
        $g = ord($a[$f]),
        $d .= $s[($g ^ ord($a[$f + $digit])) - $g & 0x1F],
        $f++
    ) ;
    return $d;
}

//汉字截取函数,同时支持 utf-8、gb2312都支持的汉字截取函数 ,默认编码是utf-8
function cut_str($string, $sublen, $start = 0, $code = 'UTF-8')
{
    if ($code == 'UTF-8') {
        $pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
        preg_match_all($pa, $string, $t_string);
        if (count($t_string[0]) - $start > $sublen) return join('', array_slice($t_string[0], $start, $sublen)) . "。";
        return join('', array_slice($t_string[0], $start, $sublen));
    } else {
        $start = $start * 2;
        $sublen = $sublen * 2;
        $strlen = strlen($string);
        $tmpstr = '';
        for ($i = 0; $i < $strlen; $i++) {
            if ($i >= $start && $i < ($start + $sublen)) {
                if (ord(substr($string, $i, 1)) > 129) {
                    $tmpstr .= substr($string, $i, 2);
                } else {
                    $tmpstr .= substr($string, $i, 1);
                }
            }
            if (ord(substr($string, $i, 1)) > 129) $i++;
        }
        if (strlen($tmpstr) < $strlen) $tmpstr .= "。";
        return $tmpstr;
    }
}

//去除表情，即4个字节的字符
function filterEmoji($str)
{
    $str = preg_replace_callback(
        '/./u',
        function (array $match) {
            return strlen($match[0]) >= 4 ? '' : $match[0];
        },
        $str);
    return $str;
}

function getTimeStrampWithDate($date)
{
    return strtotime($date);
}

function getDateWithTimeStramp($timeStramp)
{
    return date("Y-m-d H:i:s", $timeStramp);
}

function getNowDate()
{
    return date("Y-m-d H:i:s", time());
}

//传入日期，判断是否为今天
function isTodayWithTime($date)
{
    // 今天最大时间
    $todayLast = strtotime(date('Y-m-d 23:59:59'));
    $agoTime = $todayLast - getTimeStrampWithDate($date) ;
    $agoDay = floor($agoTime / 86400);
    if ($agoDay == 0) {
        return true;
    }
    return false;

}

function get_time($targetTime)
{
    // 今天最大时间
    $todayLast = strtotime(date('Y-m-d 23:59:59'));
    $agoTimeTrue = time() - $targetTime;
    $agoTime = $todayLast - $targetTime;
    $agoDay = floor($agoTime / 86400);

    if ($agoTimeTrue < 60) {
        $result = '刚刚';
    } elseif ($agoTimeTrue < 3600) {
        $result = (ceil($agoTimeTrue / 60)) . '分钟前';
    } elseif ($agoTimeTrue < 3600 * 12) {
        $result = (ceil($agoTimeTrue / 3600)) . '小时前';
    } elseif ($agoDay == 0) {
        $result = '今天 ' . date('H:i', $targetTime);
    } elseif ($agoDay == 1) {
        $result = '昨天 ' . date('H:i', $targetTime);
    } elseif ($agoDay == 2) {
        $result = '前天 ' . date('H:i', $targetTime);
    } elseif ($agoDay > 2 && $agoDay < 16) {
        $result = $agoDay . '天前 ' . date('H:i', $targetTime);
    } else {
        $format = date('Y') != date('Y', $targetTime) ? "Y-m-d H:i" : "m-d H:i";
        $result = date($format, $targetTime);
    }
    return $result;
}