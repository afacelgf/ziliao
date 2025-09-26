<?php
/**
 * Created by PhpStorm.
 * User: Xs___
 * Date: 2019/11/27
 * Time: 22:56:21
 * Desc:
 */

namespace app\api\controller;

use app\api\model\Orders;
use app\api\model\User as UsersModel;
use app\api\model\UsersAddress;
use app\api\model\UsersWallet;
use app\Utitls;
use think\facade\Request;

//use WXBizDataCrypt;
include 'Tixian.php';
use QRcode;
class TtaUser
{
    public function test()
    {
        var_dump('0000' . '000');
    }
    //插入兑换码
    function addExchangeData(){
        for ($i=1; $i<=20; $i++){
            db('tta_exchange')->insert([
                    'code'=> makeCardPasswordByDigit(),
                    'type'=>6,
                    'status'=>0,
                    'create_t'=>getNowDate()
                ]
            );
        }
    }

    public function getUserQrCodeInfo(){
        $params = Request::param();
        $openid = $params['openid'];
        //引入phpqrcode类库文件
        require_once 'phpqrcode.php';
        //生成二维码图片
        $url = config('api.base_url')."sharedQR?p_id=".$openid; //扫描二维跳转连接
        $path = "./qrcode/".$openid.".png"; //生成二维码路径
        if(!file_exists('./qrcode/')){
            mkdir('./qrcode/',0777,true);
        }
        $allPath =  config('api.base_url')."qrcode/".$openid.".png";
        if (file_exists($path)){
            Utitls::sendJson(200,$allPath, '成功');
        }
        $level = 3;
        $size = 5;
        $errorCorrectionLevel = intval($level);
        $matrixPointSize = intval($size);
        # 调取类库  Extend （use ewm\QRcode;） QRcode文件设置命名空间 namespace ewm  （ewm文件夹）;
        $packet = new \QRcode();
        $packet->png($url, $path, $errorCorrectionLevel, $matrixPointSize, 2); //生成二维码

        if ($allPath) {
            Utitls::sendJson(200, $allPath, '成功');
        }
        Utitls::sendJson(500, '', '生成二维码失败，请联系管理员');
    }

    public function duihuan()
    {
        $params = Request::param();
        $userInfo = \app\api\model\TtaUser::where(['openid' => $params['openid']])->find();
        if ($userInfo) {
            //会员类型,0普通用户，1新用户送一天，2，正式会员，3终身会员
            if ($userInfo['vipType'] == 3) {
                Utitls::sendJson(500, '', '兑换码失败，您已是终身会员');
            }
            $code = $params['code'];
            $exInfo = db('tta_exchange')->where(['code' => $code, 'status' => 0])->find();
            if ($exInfo) {
                $addTime = 0;
                $remainTime = 0;
                if ($userInfo['vipEndTime']) {//原本是vip
                    if (strtotime($userInfo['vipEndTime']) > time()) { //未过期，则累加
                        $remainTime = strtotime($userInfo['vipEndTime']) - time();
                    }
                }
//                1月，2季，3年，4终身，5日卡，6周卡
                if ($exInfo['type'] == 1) { //月
                    $addTime = strtotime('+1 month');
                } elseif ($exInfo['type'] == 2) {//季度
                    $addTime = strtotime('+3 month');
                } elseif ($exInfo['type'] == 3) {//年
                    $addTime = strtotime('+1 year');
                } elseif ($exInfo['type'] == 4) {//终身
                    $addTime = strtotime('+50 year');
                }elseif ($exInfo['type'] == 5) {//日卡
                    $addTime = strtotime('+1 day');
                }elseif ($exInfo['type'] == 6) {//周卡
                    $addTime = strtotime('+7 day');
                }
                $end_t = date("Y-m-d H:i:s", $addTime + $remainTime);
                $up = db('tta_exchange')->where(['code' => $code, 'status' => 0])->update([
                    'u_id' => $userInfo['uuid'],
                    'status' => 1,
                    'update_t' => getNowDate(),
                    'use_t' => getNowDate(),
                    'end_t' => $end_t,
                ]);
                $upUse = \app\api\model\TtaUser::where(['openid' => $params['openid']])->update(
                    [
                        'vipType' => $exInfo['type'] == 4 ? 3 : 2,
                        'isVip' => 1,
                        'vipEndTime' => $end_t,
                        'vipCode' => $userInfo['vipCode'] . ',' . $code,
                    ]
                );
                if ($upUse) {
                    Utitls::sendJson(200, '', '兑换码成功');
                }
                Utitls::sendJson(500, '', '兑换码失败，请联系管理员');
            } else {
                Utitls::sendJson(500, '', '兑换码不存在或已兑换');
            }
        } else {
            Utitls::sendJson(500, '', '用户不存在');
        }
    }

    //兑换记录
    public function inviteExchangevipList()
    {
        $params = Request::param();
        $info = db('tta_exchangevip')->where(['uuid' => $params['openid']])->select();
        if ($info) {
            Utitls::sendJson(200, $info, '');
        } else {
            Utitls::sendJson(500, '', '暂无记录');
        }
    }

//    邀请兑换会员
    public function inviteExchangevip()
    {
        $params = Request::param();
        $userInfo = \app\api\model\TtaUser::where(['openid' => $params['openid']])->find();
        if ($userInfo) {
            $configArr = config('api.xcxSharedRule');
            $time = null;
            $num = 0;
            foreach ($configArr as $key => $value) {
                if ($params['id'] == $value['id']) {
                    if ($userInfo['current_shared_count'] >= $value['num']) {
                        $time = $value['time']; //应加的时间
                        $num = $value['num'];
                    } else {
                        Utitls::sendJson(500, '', '兑换码失败，邀请人数不够');
                    }
                }
            }
            if ($time) {
                $remainTime = 0;
                if ($userInfo['vipEndTime']) {//原本是vip
                    if (strtotime($userInfo['vipEndTime']) > time()) { //未过期，则累加
                        $remainTime = strtotime($userInfo['vipEndTime']) - time();
                    }
                }
                $addtime = strtotime($time);
                $end_t = date("Y-m-d H:i:s", $addtime + $remainTime);
                $up = db('tta_exchangevip')->insert([
                    'uuid' => $params['openid'],
                    'create_at' => getNowDate(),
                    'time' => $time,
                    'vipEndTime' => $end_t,
                ]);
                $upUse = \app\api\model\TtaUser::where(['openid' => $params['openid']])->update(
                    [
                        'vipType' => 2,
                        'isVip' => 1,
                        'vipEndTime' => $end_t,
                        'current_shared_count' => $userInfo['current_shared_count'] - $num, //剩下的邀请人数
                    ]
                );
                if ($upUse) {
                    Utitls::sendJson(200, '', '兑换码成功');
                }
                Utitls::sendJson(500, '', '兑换码失败，请联系客服');
            } else {
                Utitls::sendJson(500, '', '兑换不存在');
            }
        } else {
            Utitls::sendJson(500, '', '用户不存在');
        }

    }

    public function useLoginByWechat()
    {
        $params = Request::param();
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=wx3d6e522b59c93100&secret=dbe2ff5232f0fc014464d8c2a6d4aa49&grant_type=authorization_code&js_code=' . $params['js_code'];
        $curl = curlRequest($url, 'GET', [], false);
        $json = json_decode($curl, true);
        $userInfo = \app\api\model\TtaUser::where(['openid' => $json['openid']])->find();
        if ($userInfo) {
//            $userInfo['kefu'] = config('api.base_url').'ttaSource/icon/kefu.png';
//            $userInfo['guanyu'] = config('api.xcxAbout');
//            $userInfo['gongzhonghao'] = config('api.base_url').'ttaSource/icon/gongzhonghao.png';
            Utitls::sendJson(200, $userInfo);
        } else {
            $res = \app\api\model\TtaUser::register($json['openid']);
            if ($res) {
                $userInfo = \app\api\model\TtaUser::where(['openid' => $json['openid']])->find();
                Utitls::sendJson(200, $userInfo);
            }
            Utitls::sendJson(500, '', '注册出错了');
        }
    }

    public function login()
    {
        $params = Request::param();
        $info = UsersModel::where(['account' => $params['account'], 'password' => md5($params['password'])])->find();
        if ($info) {
            if ($info["status"] == 1) {
                Utitls::sendJson(500, '', '该用户已被列入黑名单');
            } else {
                $res["account"] = $info["account"];
                $res["collectSongIds"] = $info["collectSongIds"];
                $res["desc"] = $info["desc"];
                $res["fengmianIMG"] = $info["fengmianIMG"];
                $res["gedan"] = $info["gedan"];
                $res["name"] = $info["name"];
                $res["uid"] = $info["uid"];
                Utitls::sendJson(200, $res);
            }
        }
        Utitls::sendJson(500, '', '用户不存在');
    }

    //邀请记录
    function inviteList()
    {
        $params = Request::param();
        $info = db('tta_invitelist')->where(['uuid' => $params['openid']])->select();
        if ($info) {
            Utitls::sendJson(200, $info, '');
        } else {
            Utitls::sendJson(500, '', '暂无记录');
        }

    }

    public function info()
    {
        $params = Request::param();
        $res = UsersModel::where(['openid' => $params['openid'], 'delete_at' => 0])->update([
            'nickname' => $params['nickname'],
            'avatar' => $params['avatar'],
            'update_at' => time()
        ]);
        if ($res) {
            Utitls::sendJson(200);
        }
        Utitls::sendJson(500);
    }

    //  绑定父级 - money 邀请人数 积分
    public function tapParent()
    {
        $params = Request::param();
        if ($params['openid'] == $params['p_id']) {//自己绑定自己的情况
            return;
        }
//       找出自己，p_id加上
        $my_p_id = \app\api\model\TtaUser::where(['openid' => $params['openid'], 'delete_at' => 0])->value('p_id');//自己父类
        $my_c_id = \app\api\model\TtaUser::where(['openid' => $params['openid'], 'delete_at' => 0])->value('c_id');//自己子类
        $parent_c_id = \app\api\model\TtaUser::where(['openid' => $params['p_id'], 'delete_at' => 0])->value('c_id');//父类的子类
//       如果在我的pid里，说明他已经邀请过我，拼接我父类的ids
        if (empty($my_p_id)) {
            $my_p_id = array();
            array_push($my_p_id, $params['p_id']);
        } else {
            Utitls::sendJson(201, '', '别人已经邀请过我了，不能多次邀请');
//            $my_p_id = json_decode($my_p_id, true);
//            if (in_array($params['p_id'], $my_p_id)) {//已经邀请过了
//                Utitls::sendJson(201, '', '他已经邀请过我了，不能多次邀请');
//                exit();
//            } else {
//                array_push($my_p_id, $params['p_id']);
//            }
        }
//        如果在我的cid里，说明我已经邀请过他
        if (empty($my_c_id)) {

        } else {
            $my_c_id = json_decode($my_c_id, true);
            if (in_array($params['p_id'], $my_c_id)) {//已经邀请过了
                Utitls::sendJson(202, '', '我已经邀请过他了，不能多次邀请');
                exit();
            }
        }
//        修改父级的cid
        if (empty($parent_c_id)) {
            $parent_c_id = array();
            array_push($parent_c_id, $params['openid']);
        } else {
            $parent_c_id = json_decode($parent_c_id, true);
            if (in_array($params['openid'], $parent_c_id)) {//邀请过了
                Utitls::sendJson(201, '', '已经邀请过了，不能多次邀请');
                exit();
            } else {
                array_push($parent_c_id, $params['openid']);
            }
        }
//  子级 更新自己的pid和 父级c_id
        $p_idData = json_encode($my_p_id, true);
        \app\api\model\TtaUser::where(['openid' => $params['openid'], 'delete_at' => 0])->update(
            [
                'p_id' => $p_idData
            ]
        );
        $c_idData = json_encode($parent_c_id, true);
//父级更新cid
        \app\api\model\TtaUser::where(['openid' => $params['p_id'], 'delete_at' => 0])->update(
            [
                'c_id' => $c_idData,
            ]
        );
//        找出父级，分享数+1
        $where = ['openid' => $params['p_id'], 'delete_at' => 0];
        $p_userInfo = \app\api\model\TtaUser::where($where)->find();
        $res = \app\api\model\TtaUser::where($where)->update(
            [
                'total_shared_count' => $p_userInfo['total_shared_count'] + 1,
                'current_shared_count' => $p_userInfo['current_shared_count'] + 1,
            ]
        );
        $c_userInfo = \app\api\model\TtaUser::where(['openid' => $params['openid']])->find();
        //
        $data = [
            'uuid' => $params['openid'],
            'name' => $c_userInfo['nickname'],
            'p_uuid' => $p_userInfo['openid'],
            'p_name' => $p_userInfo['nickname'],
            'create_at' => date("Y-m-d H:i:s")];
        \db('tta_invitelist')->insert($data, false, true, null);;
        if ($res) {
            Utitls::sendJson(200);
        }
        Utitls::sendJson(500);
    }

    //下载文件
    function downttaFile()
    {
        $params = Request::param();
        $userInfo = \app\api\model\TtaUser::where(['openid' => $params['openid']])->find();
        if ($userInfo) {
            $lastDown = db('tta_filedownlist')->where(['uuid' => $params['openid']])->order('id desc')->limit(1)->find();
            if($lastDown){
                $lastisToday = isTodayWithTime($lastDown['create_at']);
                if($lastisToday){ //最后一条是今天
                    $today_output_count = $userInfo['today_output_count'];
                    $maxOutputCount = config('api.ttaVipOutputFileCount');
                    if ($userInfo['isVip']==0){
                        $maxOutputCount = config('api.ttaNormalOutputFileCount');
                    }
                    if ($today_output_count >= $maxOutputCount){
                        Utitls::sendJson(500, '', '抱歉，今日的导出次数已用完');
                    }else{
                        db('tta_filedownlist')->insert([
                            'uuid'=>$params['openid'],
                            'create_at'=> date("Y-m-d H:i:s"),
                            'fileName'=>$params['fileName'],
                        ]);

                        \app\api\model\TtaUser::where(['openid' => $params['openid']])->update([
                            'today_output_count'=>$today_output_count+1
                        ]);
                        Utitls::sendJson(200);
                    }
                }else{ //上次导出不是今天
                    $up = db('tta_filedownlist')->insert([
                        'uuid'=>$params['openid'],
                        'create_at'=> date("Y-m-d H:i:s"),
                        'fileName'=>$params['fileName'],
                    ]);

                    \app\api\model\TtaUser::where(['openid' => $params['openid']])->update([
                        'today_output_count'=>1
                    ]);

                    Utitls::sendJson(200);
                }
            }else{//无记录
                $up = db('tta_filedownlist')->insert([
                    'uuid'=>$params['openid'],
                    'create_at'=> date("Y-m-d H:i:s"),
                    'fileName'=>$params['fileName'],
                ]);

                \app\api\model\TtaUser::where(['openid' => $params['openid']])->update([
                    'today_output_count'=>1
                ]);

                Utitls::sendJson(200);
            }
        }
        Utitls::sendJson(500, '', '用户不存在');
    }

    //  所有子级 -
    public function getMyActivityNum()
    {
        $params = Request::param();
        $c_id = UsersModel::where(['openid' => $params['openid'], 'delete_at' => 0])->value('c_id');
        if (!empty($c_id)) {
            $c_idArr = json_decode($c_id, true);
            $list = [];
            foreach ($c_idArr as $key => $value) {
                $info = UsersModel::where(['openid' => $value, 'delete_at' => 0])->find();
                $list[$key]['nickname'] = $info['nickname'];
                $list[$key]['avatar'] = $info['avatar'];
            }
            if ($list) {
                Utitls::sendJson(200, $list);
            }
            Utitls::sendJson(500);
        }
        Utitls::sendJson(500);
    }


//    积分转现金
    public function jifenToMoney()
    {
        $params = Request::param();
        $user = UsersModel::where(['openid' => $params['openid'], 'delete_at' => 0])->value('uuid');
        $info = UsersWallet::where(['uuid' => $user, 'delete_at' => 0])->find();

        $uuid = UsersModel::getUuid($params['openid']);
        $point = floatval(UsersWallet::where(['uuid' => $uuid, 'delete_at' => 0])->value('point'));

        $count = floatval($params['count']);//要兑换的积分
        if ($count > config('api.duijifenmax')) {
            Utitls::sendJson(501, '兑换的积分数太多');
        } else {
            if ($point >= $count) {
                $info['point'] = $point - $count;
                $info['money'] = floatval($info['money']) + $count / config('api.duijifen');
                UsersWallet::where(['uuid' => $user, 'delete_at' => 0])->update(['money' => $info['money'], 'point' => $info['point']]);
                //积分记录里添加记录
                //根据id积分log，添加记录
                $data = ['uuid' => $user, 'type' => '0', 'point' => $count, 'iorr' => "0", 'desc' => '积分兑换', 'create_at' => time()];
                \db('user_point_log')->insert($data, false, true, null);
                Utitls::sendJson(200, '兑换成功');
            } else {
                Utitls::sendJson(500, '积分不足');
            }
        }
    }

    //  提现
    public function postTX()
    {
        $params = Request::param();
        $uuid = UsersModel::getUuid($params['openid']);
        $info = UsersWallet::where(['uuid' => $uuid, 'delete_at' => 0])->find();
        //先去查当天的提现次数，最多三次
        $mytixians = db('user_tixian_log')->where(['uuid' => $uuid])->select();
        $times = 0;
        foreach ($mytixians as $key => $value) {
            if (date('Ymd', time()) == date('Ymd', $value['create_at'])) {
                $times++;
            }
        }
        if ($times >= 3) {
            Utitls::sendJson(501, '', '今天已达到了提现次数，请明日再提现');
        }
        UsersWallet::where(['uuid' => $uuid, 'delete_at' => 0])->update(
            [
                'money' => $info['money'] - $params['money'] * 1.01,
                'deposit' => $info['deposit'] + $params['money'] * 1.01,
                'update_at' => time()
            ]);
        $data = ['uuid' => $uuid, 'money' => $params['money'], 'status' => "0", 'create_at' => time()];
        $insert = db('user_tixian_log')->insert($data, false, true, null);
        if ($insert) {
            $nickname = \app\api\model\User::where(['uuid' => $uuid])->value('nickname');
            $data = [
                "amount1" => [
                    "value" => $params['money']
                ],
                "time5" => [
                    "value" =>getNowDate()
                ],
                "thing6" => [
                    "value" => $nickname
                ]
            ];
            $page = '/pages/mine/mine/managed/user/tixian';
            $kuaidi = new Kuaidi();
            $ret = $kuaidi->sendKuaidiDaiSureMessage(config('api.laiguofengopenid'), 'Kif7c4hZGuPjI-4Wp5cwDLKWfkQ9UsiVh5AVRFsbuz0', $page, $data);
            Utitls::sendJson(200, null, '提现申请成功');
        }
        Utitls::sendJson(500);
    }

    //  提现记录列表
    public function gettxRecordList()
    {
        $params = Request::param();
        $uuid = UsersModel::getUuid($params['openid']);
        $info = db('user_tixian_log')->where(['uuid' => $uuid, 'delete_at' => 0])->order('id desc')->select();

        $list = [];
        foreach ($info as $key => $value) {
            $list[$key]['money'] = $value['money'];
            $list[$key]['id'] = $value['id'];
            switch ($value['status']) {
                case 0:
                    $list[$key]['status'] = '等待客服处理中...';
                    break;
                case 1:
                    $list[$key]['status'] = '提现成功';
                    break;
                case 2:
                    $list[$key]['status'] = '提现失败';
                    break;
                default:
                    break;
            }
            $list[$key]['time'] = date('Y-m-d H:i', $value['create_at']);
        }
        if ($list) {
            Utitls::sendJson(200, $list);
        }
        Utitls::sendJson(500);
    }

    //  全部提现列表
    public function gettxRecordListNoCheck()
    {
        $params = Request::param();
        $count = db('user_tixian_log')->where(['status' => $params['status'], 'delete_at' => 0])->count();
        $info = db('user_tixian_log')->where(['status' => $params['status'], 'delete_at' => 0])->order('id desc')->paginate(10);

        $list = [];
        foreach ($info as $key => $value) {
            $userinfo = \app\api\model\User::where(['uuid' => $value['uuid']])->find();
            $list[$key]['money'] = $value['money'];
            $list[$key]['nickname'] = $userinfo['nickname'];
            $list[$key]['avatar'] = $userinfo['avatar'];
            $list[$key]['openid'] = $userinfo['openid'];
            $list[$key]['id'] = $value['id'];
            $list[$key]['status'] = $value['status'];
            switch ($value['status']) {
                case 0:
                    $list[$key]['statusTip'] = '等待客服处理中...';
                    break;
                case 1:
                    $list[$key]['statusTip'] = '提现成功';
                    break;
                case 2:
                    $list[$key]['statusTip'] = '提现失败';
                    break;
                default:
                    break;
            }
            $list[$key]['time'] = date('Y-m-d H:i', $value['create_at']);

        }
        if ($list) {
            Utitls::sendJson(200, ['list' => $list, 'count' => $count]);
        }
        Utitls::sendJson(500);
    }

    public function getUserInfo()
    {
        $params = Request::param();
        $info = \app\api\model\TtaUser::where(['openid' => $params['openid'], 'delete_at' => 0, 'status' => 1])->find();
        if ($info) {
            $res['openid'] = $info['openid'];
            $res['nickname'] = $info['nickname'];
            $res['vipType'] = $info['vipType'];
            $res['total_shared_count'] = $info['total_shared_count'];
            $res['current_shared_count'] = $info['current_shared_count'];
            $res['vipEndTime'] = $info['vipEndTime'];
            $res['isVip'] = $info['isVip'];
            $endTime = getTimeStrampWithDate($info['vipEndTime']);
            if ($info['isVip'] == 1 && $endTime < time()) {//已过期
                \app\api\model\TtaUser::where(['openid' => $params['openid'], 'delete_at' => 0, 'status' => 1, 'isVip' => 1])->update([
                    'isVip' => 0,
                    'vipType' => 0,
                    'update_at' => time()
                ]);
                $res['isVip'] = 0;
                $res['vipType'] = 0;
            }
            Utitls::sendJson(200, $res);
        }
        Utitls::sendJson(500);
    }

}
