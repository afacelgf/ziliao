<?php
/**
 * Created by PhpStorm.
 * User: Xs___
 * Date: 2019/11/29
 * Time: 15:46:19
 * Desc:
 */

namespace app\api\controller;

use app\api\model\Orders;
use app\api\model\Pays;
use app\api\model\User;
use think\facade\Request;

class Notify
{


    //Xml转数组
    public function xml_array($xml)
    {
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }

    public function test()
    {
        $params = Request::param();
        file_put_contents("/www/wwwroot/haojiabang/1.log", $params);
        file_put_contents("/www/wwwroot/haojiabang/1.log", var_export($params, true));
    }


    public function index()
    {
        //获取通知的数据
        $xml = file_get_contents("php://input");
        //Xml解析
        $res = $this->xml_array($xml);
        //交易订单号
        $pay_info = Pays::where(['sn' => $res['out_trade_no']])->find();
        if ($res['result_code'] == 'SUCCESS') {

            if ($pay_info['is_pay'] == 0) {
                Pays::where(['sn' => $res['out_trade_no']])->update(['is_pay' => 1, 'update_at' => time()]);
                Orders::where(['id' => $pay_info['order_id']])->update(['status' => 1, 'update_at' => time()]);

                if ($pay_info['type'] == 10) { //开一个月会员
                    $times = strtotime('+1 month');
                }
                User::where(['uuid' => $pay_info['uuid']])->update(['vip' => 1, 'vipEndTime' => $times, 'update_at' => time()]);

            }
            //返回结果
            echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
            exit();
        }
    }


}
