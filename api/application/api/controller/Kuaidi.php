<?php
/**
 * Created by PhpStorm.
 * User: Xs___
 * Date: 2019/11/27
 * Time: 22:56:21
 * Desc:
 */

namespace app\api\controller;

use app\api\model\KuaidiType;
use app\api\model\Orders;
use app\Utitls;
use think\Console;
use think\facade\Request;
use EasyWeChat\Factory;
use function EasyWeChat\Kernel\Support\generate_sign;
use app\api\controller\Tixian;

class Kuaidi
{


    public function pay()
    {
        $params = Request::param();

        //支付参数
        $payment = Factory::payment(config()['api']);
        $result = $payment->order->unify([
            'body' => '支付' . 1 . '元',
            'out_trade_no' => order_number($params['openid']),
            'trade_type' => 'JSAPI',
            'openid' => $params['openid'],
            'total_fee' => 1
        ]);
        if ($result['return_code'] === 'SUCCESS') {
            $params = [
                'appId' => config()['api']['app_id'],
                'timeStamp' => time(),
                'nonceStr' => $result['nonce_str'],
                'package' => 'prepay_id=' . $result['prepay_id'],
                'signType' => 'MD5',
            ];
            $params['paySign'] = generate_sign($params, config()['api']['key']);
            Utitls::sendJson(200, $params);
        }
        Utitls::sendJson(500);
    }

    //status 0未接单 1接单中，待取件 2已取件，待配送 3，配送完成 4接单中，待用户确认  5已完成
//    获取快递类型
    public function getKuaidiType()
    {
        $info = KuaidiType::where(['delete_at' => 0])->select();
        $list = [];
        foreach ($info as $key => $value) {
            $list[$key]['id'] = $value['id'];
            $list[$key]['name'] = $value['name'];
        }
        if ($list) {
            Utitls::sendJson(200, $list);
        } else {
            Utitls::sendJson(500);
        }
    }

//    获取快递信息
    public function getKuaidiInfo()
    {
        $params = Request::param();
        $appKey = 'ddc96ac464130160';
        $url = 'https://api.jisuapi.com/express/query';
//       $kuaidiData = curlRequest($url, 'POST');

        $post_data = array(
            'appkey' => $appKey,
            'type' => 'auto',
            'number' => $params['number'],
            'mobile' => $params['phone'],
        );
//        print_r($post_data);
//        exit();
        $kuaidiData = $this->send_post($url, $post_data);
        $jsonarr = json_decode($kuaidiData, true);
        Utitls::sendJson(200, $jsonarr);
//        Utitls::sendJson(200, '0000');
    }

    public function send_post($url, $post_data)
    {
        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return $result;
    }

    //    添加取快递
    public function add()
    {
        $params = Request::param();
        $res = \app\api\model\Kuaidi::createKuaidi($params);
        if ($res) {
            Utitls::sendJson(200);
        }
        Utitls::sendJson(500);
    }

    //  取消/删除取快递
    public function delete()
    {
        $params = Request::param();
        //快递且已支付的订单号，退款。
        $kdinfo = \app\api\model\Kuaidi::where(['delete_at' => 0, 'pay' => 1, 'id' => $params['id']])->find();
        if ($kdinfo) {
            $where = ['delete_at' => 0, 'sn' => $kdinfo['order_id'], 'status' => 1];
            $kdorder = Orders::where($where)->find();
            $orderNo = $kdorder['sn'];      //商户订单号（商户订单号与微信订单号二选一，至少填一个）
            $wxOrderNo = '';    //微信订单号（商户订单号与微信订单号二选一，至少填一个）
            $totalFee = $kdorder['total'] / 100;       //订单金额，单位:元
            $refundFee = $kdorder['total'] / 100;      //退款金额，单位:元
            $tixian = new Tixian();
            $result = $tixian->doRefund($totalFee, $refundFee, $wxOrderNo, $orderNo);
            if ($result == true) {
                //业务逻辑处理,改为已退款，并移除
                $change = Orders::where($where)->update(
                    ['status' => 2, 'delete_at' => 1]
                );
                \app\api\model\Kuaidi::where(['delete_at' => 0, 'pay' => 1, 'id' => $params['id']])->update(
                    ['pay' => 2, 'delete_at' => 1,'status'=>6,'quxiaoTip'=>$params['quxiaoTip']]
                );

                //通知骑手，已取消
                $qsopenid = \app\api\model\User::getOpenid($kdinfo['qs_id']);

                //给用户发送待审核订阅消息
                $data = [
                    "character_string1" => [
                        "value"=>$kdinfo['order_id']
                    ],
                    "phrase2" => [
                        "value"=> '订单已取消'
                    ],
                    "date3" => [
                        "value"=> date('Y-m-d H:i:s',time())
                    ],
                    "thing10" => [
                        "value"=> "此单用户已取消，点击查看原因"
                    ]
                ];
                $page = '/pages/mine/jiedan/list';
                $ret= $this->sendKuaidiDaiSureMessage($qsopenid,'5NwTt8-X7ci6mNiBVZ4evdV7Cm78zVrMoFVsOMfc3y4',$page,$data);


                if ($change) {
                    Utitls::sendJson(200, $result,'取消成功');
                }
                Utitls::sendJson(500, $result,'取消失败');
            }
            Utitls::sendJson(500, $result,'取消失败');
        }
    }


    //    骑手-已取得快递
    public function yiqudekuaidi()
    {
        $params = Request::param();
        $qsid = \app\api\model\User::getUuid($params['openid']);
        $res = \app\api\model\Kuaidi::where(['qs_id' => $qsid, 'status' => 1, 'id' => $params['id']])->update([
            'status' => 2,
            'qujian_pic' => $params['qujian_pic'],
            'qujian_time' => time(),
        ]);
        if ($res) {
            Utitls::sendJson(200);
        }
        Utitls::sendJson(500);
    }

    //    骑手-已配送完成
    public function yipeisongkuaidi()
    {
        $params = Request::param();
        $qsid = \app\api\model\User::getUuid($params['openid']);
        $where = ['qs_id' => $qsid, 'status' => 2, 'id' => $params['id']];

        $res = \app\api\model\Kuaidi::where($where)->update([
            'status' => 3,
            'peisong_time' => time(),
        ]);
        if ($res) {
            $useruuid = \app\api\model\Kuaidi::where(['id' => $params['id'], 'delete_at' => 0])->value('uuid');
            $useropenid = \app\api\model\User::getOpenid($useruuid);
            //给用户发送待审核订阅消息
            $data = [
                "thing1" => [
                    "value"=>"您的快递单骑手已配送完成"
                ],
                "time3" => [
                    "value"=> date('Y-m-d H:i:s',time())
                ],
                "thing4" => [
                    "value"=> "请注意查收包裹是否已送达您家"
                ]
            ];
            $page = '/pages/mine/order/kuaidiOrder';
            $ret= $this->sendKuaidiDaiSureMessage($useropenid,'IelCikReFnSawIFDPVvJ4wMal4_mR7sCxdSyunwI54s',$page,$data);
            Utitls::sendJson(200,$ret);
        }
        Utitls::sendJson(500);
    }

    //    修改快递信息  status 0未接单 1接单中，待取件 2已取件，待配送 3，配送完成 4接单中，待确认
    public function update()
    {
        $params = Request::param();
        $res = \app\api\model\Kuaidi::updateKuaidi($params);

        if ($res) {
            $qsuuid = \app\api\model\Kuaidi::where(['id' => $params['id'], 'delete_at' => 0])->value('qs_id');
            $qsopenid = \app\api\model\User::getOpenid($qsuuid);
            //给用户发送待审核订阅消息
            $data = [
                "date4" => [
                    "value"=> date('Y-m-d H:i:s',time())
                ],
                "thing1" => [
                    "value"=> "您的接单已被用户确认,请开始配送流程"
                ],
                "thing5" => [
                    "value"=> date('Y-m-d H:i:s',time())
                ]
            ];
            $page = '/pages/mine/jiedan/list';
            $ret= $this->sendKuaidiDaiSureMessage($qsopenid,'RMfomll_5kDan7S2p56TFAZ0OeIKW3fOZ6FYy6-V4q8',$page,$data);


            Utitls::sendJson(200);
        }
        Utitls::sendJson(500);
    }

    // 骑手-接单
    public function jiedan()
    {
        $params = Request::param();
        $qsid = \app\api\model\User::getUuid($params['openid']);
       $kuaidiuuid = \app\api\model\Kuaidi::where(['id' => $params['id'], 'delete_at' => 0])->value('uuid');
        $kduseropenid = \app\api\model\User::getOpenid($kuaidiuuid);
        if($qsid == $kuaidiuuid){
            Utitls::sendJson(501);
        }else{
            $res = \app\api\model\Kuaidi::where(['id' => $params['id'], 'delete_at' => 0, 'status' => 0])->update(
                [
                    'qs_lat' => $params['qs_lat'],
                    'qs_lng' => $params['qs_lng'],
                    'qs_id' => $qsid,
                    'status' => 4,
                    'jiedan_time' => time()
                ]
            );
            if ($res) {
                //给用户发送待审核订阅消息
               $data = [
                    "thing1" => [
                        "value"=> "快递订单已被接单,请前往审核确认"
                    ],
                    "time6" => [
                        "value"=> date('Y-m-d H:i:s',time())
                    ],
                    "phrase2" => [
                        "value"=> "订单待确认"
                    ],
                    "date4" => [
                        "value"=> date('Y-m-d H:i:s',time())
                    ]
                ];
               $page = '/pages/mine/order/kuaidiOrder';
                $ret= $this->sendKuaidiDaiSureMessage($kduseropenid,'3iewGah4TrfQrNflNRRrRmI9-ZBkmA36hZrFsygxQDs',$page,$data);
                Utitls::sendJson(200,$ret,'已接单，待确认');
            }
            Utitls::sendJson(500);
        }
    }

    // 用户-同意骑手接单
    public function agreeTojiedan()
    {
        $params = Request::param();
        //status 0未接单 1接单中，待取件 2已取件，待配送 3，配送完成 4接单中，待确认
        $res = \app\api\model\Kuaidi::where(['id' => $params['id'], 'delete_at' => 0, 'status' => 4])->update(
            [
                'status' => 1,
                'agree_time' => time()
            ]
        );
        if ($res) {
            $qsuuid = \app\api\model\Kuaidi::where(['id' => $params['id'], 'delete_at' => 0])->value('qs_id');
            $qsopenid = \app\api\model\User::getOpenid($qsuuid);
            //给用户发送待审核订阅消息
            $data = [
                "date4" => [
                    "value"=> date('Y-m-d H:i:s',time())
                ],
                "thing1" => [
                    "value"=> "您的接单已被用户确认,请开始配送流程"
                ],
                "thing5" => [
                    "value"=> "请在规定时间内配送完成"
                ]
            ];
            $page = '/pages/mine/jiedan/list';
            $ret= $this->sendKuaidiDaiSureMessage($qsopenid,'RMfomll_5kDan7S2p56TFAZ0OeIKW3fOZ6FYy6-V4q8',$page,$data);

            Utitls::sendJson(200,$ret);
        }
        Utitls::sendJson(500);
    }

    // 用户-拒绝骑手接单-重新发布
    public function refuseTojiedan()
    {
        $params = Request::param();
        //status 0未接单 1接单中，待取件 2已取件，待配送 3，配送完成 4接单中，待确认
        $res = \app\api\model\Kuaidi::where(['id' => $params['id'], 'delete_at' => 0, 'status' => 4])->update(
            [
                'status' => 0,
                'agree_time' => null,
                'create_at' => time(),
                'read_total' => 0,
                'qs_id' => null,
            ]
        );
        if ($res) {
            Utitls::sendJson(200);
        }
        Utitls::sendJson(500);
    }

    // 骑手配送完成，用户点击订单完成
    public function completeOrder()
    {
        //更改快递订单状态，发钱给骑手。
        $params = Request::param();
        $kdinfo = \app\api\model\Kuaidi::where(['id' => $params['id'], 'delete_at' => 0])->find();
        $qs_openid = \app\api\model\User::getOpenid( $kdinfo['qs_id']);
       $tixian = new \app\api\controller\Tixian();
       $tixianresult = $tixian->sendMoney($kdinfo['money'],$qs_openid,'快递佣金','',2);
        if ($tixianresult == true){
            //status 0未接单 1接单中，待取件 2已取件，待配送 3，配送完成 4接单中，待确认  5已完成 6已取消
            $res = \app\api\model\Kuaidi::where(['id' => $params['id'], 'delete_at' => 0, 'status' => 3])->update(
                [
                    'status' => 5,
                    'complete_time' => time(),
                ]
            );
            if ($res) {
                Utitls::sendJson(200);
            }
            Utitls::sendJson(501,'','发佣金成功，确认订单失败');
        }

        Utitls::sendJson(502,'','发佣金失败');
    }


    // 骑手-接单-我的接单
    public function myJieDan()
    {
        $params = Request::param();
        $uuid = \app\api\model\User::getUuid($params['openid']);

        $info = \app\api\model\Kuaidi::where(['qs_id' => $uuid])->order('id desc')->select();
        $list = [];
        foreach ($info as $key => $value) {
//            $timestramp = strtotime($value['song_date'] . ' ' . $value['song_time']);
//            if ($timestramp - time() > 0) {//送达时间小于现在时间
                $location = \app\api\model\UsersAddress::where(['id' => $value['location_id']])->find();
                //取收货地址的经纬度
                $distance = getDistance($params['lat'], $params['lng'], $location['lat'], $location['lng']);
                $list[$key]['id'] = $value['id'];
                $list[$key]['qujian_address'] = $value['qujian_address'];
                $list[$key]['song_date'] = $value['song_date'];
                $list[$key]['song_time'] = $value['song_time'];
                $list[$key]['money'] = $value['money'];
                $list[$key]['status'] = $value['status'];
                $list[$key]['delete_at'] = $value['delete_at'];
                $list[$key]['distance'] = $distance;
                $list[$key]['detail_address'] = $location['detail'];
            $list[$key]['quxiaoTip'] = $value['quxiaoTip'];
//            } else {
//                $res = \app\api\model\Kuaidi::where(['id' => $value['id']])->update(
//                    ['delete_at' => 1]);
//            }
        }

        if ($list) {

            Utitls::sendJson(200, $list);
        }
        Utitls::sendJson(500);
    }

    public function testMessage()
    {

        $this->sendKuaidiDaiSureMessage('oIJO55eGYDMlQl5GXbC4vNVmOAQU','3iewGah4TrfQrNflNRRrRmI9-ZBkmA36hZrFsygxQDs');
    }

    //订单已被接单,请审核后骑手开始配送
    public function sendKuaidiDaiSureMessage($openId,$templateId,$page,$data)
    {
        $tokenUrl = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.config('api.app_id').'&secret='.config('api.secret');
        $tokenres = json_decode(curlRequest($tokenUrl, 'GET'), true);
        //要发送给微信接口的数据
        $send_data = [
            //用户openId
            "touser" => $openId,
            //模板id
            "template_id" => $templateId,
            //指定发送到开发版
            "miniprogram_state"=>"developer",
            //点击跳转到小程序的页面,快递订单界面
            "page"=>$page,
            "data"=>$data,
        ];
        $tokenUrl = 'https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token='.$tokenres['access_token'];
        $ret = self::curlPost($tokenUrl,json_encode($send_data));
        return $ret;
    }

    //发送post请求
    static function curlPost($url,$data)
    {
        $ch = curl_init();
        $params[CURLOPT_URL] = $url;    //请求url地址
        $params[CURLOPT_HEADER] = FALSE; //是否返回响应头信息
        $params[CURLOPT_SSL_VERIFYPEER] = false;
        $params[CURLOPT_SSL_VERIFYHOST] = false;
        $params[CURLOPT_RETURNTRANSFER] = true; //是否将结果返回
        $params[CURLOPT_POST] = true;
        $params[CURLOPT_POSTFIELDS] = $data;
        curl_setopt_array($ch, $params); //传入curl参数
        $content = curl_exec($ch); //执行
        curl_close($ch); //关闭连接
        return $content;
    }

    // 我的订单-快递
    public function myRecord()
    {
        $params = Request::param();
        $uuid = \app\api\model\User::getUuid($params['openid']);

        $info = \app\api\model\Kuaidi::where(['uuid' => $uuid,])->order('id desc')->select();
        $list = [];
        foreach ($info as $key => $value) {
            $timestramp = strtotime($value['song_date'] . ' ' . $value['song_time']);
//            if ($timestramp - time() > 0) {//送达时间小于现在时间
                $list[$key]['id'] = $value['id'];
                $list[$key]['qujian_address'] = $value['qujian_address'];
                $list[$key]['song_date'] = $value['song_date'];
                $list[$key]['song_time'] = $value['song_time'];
                $list[$key]['money'] = $value['money'];
                $list[$key]['create_at'] = date('Y-m-d H:i:s', $value['create_at']);
                $list[$key]['status'] = $value['status'];
                $list[$key]['delete_at'] = $value['delete_at'];
                $list[$key]['quxiaoTip'] = $value['quxiaoTip'];
//            } else {
//                $res = \app\api\model\Kuaidi::where(['id' => $value['id']])->update(
//                    ['delete_at' => 1]);
//            }
        }
        if ($list) {
            Utitls::sendJson(200, $list);
        }
        Utitls::sendJson(500);
    }

    // 所有快递信息
    public function index()
    {
        $params = Request::param();
        $where[] = ['delete_at', '=', 0];
        $where[] = ['status', '=', 0];
        $where[] = ['pay', '=', 1];
        $count = \app\api\model\Kuaidi::where($where)->count();
        $info = \app\api\model\Kuaidi::where($where)->order('id desc')->paginate($listRows = 3);
        $list = [];
        foreach ($info as $key => $value) {
            $timestramp = strtotime($value['song_date'] . ' ' . $value['song_time']);
            if ($timestramp - time() > 0) {//送达时间小于现在时间
                $location = \app\api\model\UsersAddress::where(['id' => $value['location_id']])->find();
                //取收货地址的经纬度
                $distance = getDistance($params['lat'], $params['lng'], $location['lat'], $location['lng']);
                $list[$key]['id'] = $value['id'];
                $list[$key]['kd_type'] = $value['kd_type'];
                $list[$key]['qujian_address'] = $value['qujian_address'];
                $list[$key]['song_date'] = $value['song_date'];
                $list[$key]['song_time'] = $value['song_time'];
                $list[$key]['money'] = $value['money'];
                $list[$key]['read_total'] = $value['read_total'];
                $list[$key]['distance'] = $distance;
                $list[$key]['detail_address'] = $location['detail'];
            } else {//过期删除
                $res = \app\api\model\Kuaidi::where(['id' => $value['id']])->update(
                    ['delete_at' => 1]);
            }
        }
        //        ascArray($list,'distance');
        array_multisort(array_column($list, 'distance'), SORT_ASC, $list);
        if ($list) {
            Utitls::sendJson(200, ['list' => $list, 'page' => ceil($count / 3)]);
        }
        Utitls::sendJson(500);
    }

    //搜索资讯
    public function search()
    {
        $params = Request::param();
        if (isset($params['search']) && !empty($params['search'])) {
            $where[] = ['info_address|info_name|qujian_address', 'like', '%' . $params['search'] . '%'];
        }
        $where[] = ['delete_at', '=', 0];
        $where[] = ['status', '=', 0];
        $where[] = ['pay', '=', 1];
        $count = \app\api\model\Kuaidi::where($where)->count();
        $info = \app\api\model\Kuaidi::where($where)->order('id desc')->paginate($listRows = 1);
        $list = [];
        foreach ($info as $key => $value) {
            $timestramp = strtotime($value['song_date'] . ' ' . $value['song_time']);
            if ($timestramp - time() > 0) {//送达时间小于现在时间
                $location = \app\api\model\UsersAddress::where(['id' => $value['location_id']])->find();
                //取收货地址的经纬度
                $distance = getDistance($params['lat'], $params['lng'], $location['lat'], $location['lng']);
                $list[$key]['id'] = $value['id'];
                $list[$key]['kd_type'] = $value['kd_type'];
                $list[$key]['qujian_address'] = $value['qujian_address'];
                $list[$key]['song_date'] = $value['song_date'];
                $list[$key]['song_time'] = $value['song_time'];
                $list[$key]['money'] = $value['money'];
                $list[$key]['read_total'] = $value['read_total'];
                $list[$key]['distance'] = $distance;
                $list[$key]['detail_address'] = $location['detail'];
            } else {//过期删除
                $res = \app\api\model\Kuaidi::where(['id' => $value['id']])->update(
                    ['delete_at' => 1]);
            }
        }
        //        ascArray($list,'distance');
        array_multisort(array_column($list, 'distance'), SORT_ASC, $list);
        if ($list) {
            Utitls::sendJson(200, ['list' => $list, 'page' => ceil($count / 1)]);
        }
        Utitls::sendJson(500);
    }

    // 挑选8条热门的在首页显示
    public function hostList()
    {
        $params = Request::param();
        $where[] = ['delete_at', '=', 0];
        $where[] = ['status', '=', 0];
        $where[] = ['pay', '=', 1];
        $info = \app\api\model\Kuaidi::where($where)->order('id desc')->limit(8)->select();
        $list = [];
        foreach ($info as $key => $value) {
            $timestramp = strtotime($value['song_date'] . ' ' . $value['song_time']);
            if ($timestramp - time() > 0) {//送达时间小于现在时间
                $location = \app\api\model\UsersAddress::where(['id' => $value['location_id']])->find();
                //取收货地址的经纬度
                $distance = getDistance($params['lat'], $params['lng'], $location['lat'], $location['lng']);
                $list[$key]['id'] = $value['id'];
                $list[$key]['kd_type'] = $value['kd_type'];
                $list[$key]['qujian_address'] = $value['qujian_address'];
                $list[$key]['song_date'] = $value['song_date'];
                $list[$key]['song_time'] = $value['song_time'];
                $list[$key]['money'] = $value['money'];
                $list[$key]['read_total'] = $value['read_total'];
                $list[$key]['distance'] = $distance;
                $list[$key]['detail_address'] = $location['detail'];
            } else {//过期删除
                $res = \app\api\model\Kuaidi::where(['id' => $value['id']])->update(
                    ['delete_at' => 1]);
            }

        }
        //        ascArray($list,'distance');
        array_multisort(array_column($list, 'distance'), SORT_ASC, $list);
        if ($list) {
            Utitls::sendJson(200, $list);
        }
        Utitls::sendJson(500);
    }

    // 根据id获取快递
    public function infoById()
    {
        $params = Request::param();
        $info = \app\api\model\Kuaidi::where(['id' => $params['id'], 'delete_at' => 0])->find();

        $readTotal = $info['read_total'] + 1;
        $this->increateClick();

        $map['location_id'] = $info['location_id'];
        $map['id'] = $info['id'];
        $map['info_phone'] = $info['info_phone'];
        $map['info_name'] = $info['info_name'];
        $map['info_address'] = $info['info_address'];
        $map['kd_type'] = $info['kd_type'];
        $map['qujian_address'] = $info['qujian_address'];
        $map['song_date'] = $info['song_date'];
        $map['song_time'] = $info['song_time'];
        $map['money'] = $info['money'];
        $map['other'] = $info['other'];
        $map['status'] = $info['status'];

        $map['qujian_pic'] = $info['qujian_pic'];//取件图片
        $map['peisong_pic'] = $info['peisong_pic'];//配送图片
        $map['jiedan_time'] = date('Y-m-d H:i', $info['jiedan_time']);//接单时间
        $map['qujian_time'] = date('Y-m-d H:i', $info['qujian_time']);//取件时间
        $map['peisong_time'] = date('Y-m-d H:i', $info['peisong_time']);//配送时间
        $map['read_total'] = $readTotal;
        $location = \app\api\model\UsersAddress::where(['id' => $info['location_id']])->find();
        //取收货地址的经纬度
        $distance = getDistance($params['lat'], $params['lng'], $location['lat'], $location['lng']);
        $map['lat'] = $location['lat'];
        $map['lng'] = $location['lng'];
        $map['detail_address'] = $location['detail'];
        $map['host_phone'] = $location['phone'];
        $map['host_name'] = $location['name'];
        $map['distance'] = $distance;
        if ($map) {
            Utitls::sendJson(200, $map);
        }
        Utitls::sendJson(500);
    }

    // 用户查看自己的取快递
    public function userGetKDinfoById()
    {
        $params = Request::param();
        $info = \app\api\model\Kuaidi::where(['id' => $params['id'], 'delete_at' => 0])->find();

        $qs_id = $info['qs_id'];
        if ($qs_id) {
            $qs_info = \app\api\model\User::where(['uuid' => $info['qs_id'], 'delete_at' => 0])->find();
            $map['qs_name'] = $qs_info['name'];
            $map['qs_phone'] = $qs_info['phone'];
//            $map['qs_idcard'] = $qs_info['idcard'];
            $map['user_jinzhao'] = $qs_info['user_jinzhao'];
        }
        //        骑手信息

        $map['location_id'] = $info['location_id'];
        $map['id'] = $info['id'];
        $map['info_phone'] = $info['info_phone'];
        $map['info_address'] = $info['info_address'];
        $map['info_name'] = $info['info_name'];
        $map['order_number'] = $info['order_id'];
        $map['kd_type'] = $info['kd_type'];
        $map['qujian_address'] = $info['qujian_address'];
        $map['song_date'] = $info['song_date'];
        $map['song_time'] = $info['song_time'];
        $map['money'] = $info['money'];
        $map['other'] = $info['other'];
        $map['status'] = $info['status'];

        $map['qujian_pic'] = $info['qujian_pic'];//取件图片
        $map['peisong_pic'] = $info['peisong_pic'];//配送图片
        $map['jiedan_time'] = date('Y-m-d H:i', $info['jiedan_time']);//接单时间
        $map['qujian_time'] = date('Y-m-d H:i', $info['qujian_time']);//取件时间
        $map['peisong_time'] = date('Y-m-d H:i', $info['peisong_time']);//配送时间

        $location = \app\api\model\UsersAddress::where(['id' => $info['location_id']])->find();
        //取收货地址的经纬度
        $distance = getDistance($params['lat'], $params['lng'], $location['lat'], $location['lng']);
        $map['lat'] = $location['lat'];
        $map['lng'] = $location['lng'];
        $map['detail_address'] = $location['detail'];
        $map['host_phone'] = $location['phone'];
        $map['host_name'] = $location['name'];
        $map['distance'] = $distance;
        if ($map) {
            Utitls::sendJson(200, $map);
        }
        Utitls::sendJson(500);
    }


    /** 浏览量加一   三分钟内重复访问无效
     * @param $id  快递id
     */
    public function increateClick()
    {
        $id = Request::param('id');
        if ($id) {
            $sessonName = md5($id . getip());
            if (!\think\facade\Session::has($sessonName)) { //没有session进来 +1
                \think\facade\Session::set($sessonName, time());
                $info = \app\api\model\Kuaidi::where(['delete_at' => 0, 'id' => $id])->find();
                $readTotal = $info['read_total'] + 1;
                $info = Db('kuaidi')->where(['id' => $id])->update(['read_total' => $readTotal]);//setInc()实现hits+1
            } else if (time() - \think\facade\Session::get($sessonName) < 180) { //session创建了10秒内  不+1

            } else { //session创建了大于10秒  +1并清除这个session
                \think\facade\Session::delete($sessonName);
                $info = \app\api\model\Kuaidi::where(['delete_at' => 0, 'id' => $id])->find();
                $readTotal = $info['read_total'] + 1;
                $info = Db('kuaidi')->where(['id' => $id])->update(['read_total' => $readTotal]);
            }
        }
    }
}
