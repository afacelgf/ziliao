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

class User
{


    public function test()
    {
        var_dump('0000' . '000');
    }

    public function register()
    {
        $params = Request::param();
        $info = UsersModel::where(['account' =>$params['account']])->find();
        if ($info) {
            Utitls::sendJson(500,'','用户已存在');
        }
        $res = UsersModel::reg($params);
        if ($res) {
            Utitls::sendJson(200, ['uid' => $res]);
        }
        Utitls::sendJson(500,'','注册出错了');
    }

    public function login()
    {
        $params = Request::param();
        $info = UsersModel::where(['account' =>$params['account'],'password'=>md5($params['password'])])->find();
        if ($info) {
            if ($info["status"]==1){
                Utitls::sendJson(500,'','该用户已被列入黑名单');
            }else{
                $res["account"] =  $info["account"];
                $res["collectSongIds"] =  $info["collectSongIds"];
                $res["desc"] =  $info["desc"];
                $res["fengmianIMG"] =  $info["fengmianIMG"];
                $res["gedan"] =  $info["gedan"];
                $res["name"] =  $info["name"];
                $res["uid"] =  $info["uid"];
                Utitls::sendJson(200,$res);
            }
        }
        Utitls::sendJson(500,'','用户不存在');
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

//    我的收藏
    public function getMyCollection()
    {
        $params = Request::param();
        $collect_news_ids = UsersModel::where(['openid' => $params['openid'], 'delete_at' => 0])->value('collect_news_ids');


        $list = [];
        if ($collect_news_ids) {
            $arr = json_decode($collect_news_ids, true);
            $list = [];
            foreach ($arr as $key => $value) {
                $info = \app\api\model\News::where(['id' => $value])->find();
                $comment_count = \app\api\model\NewsComments::where(['news_id' => $info['id'], 'delete_at' => 0])->count();
                $list[$key]['comment_count'] = $comment_count;

                $list[$key]['collectCount'] = $info['collectCount'];
                $list[$key]['title'] = $info['title'];
                $list[$key]['id'] = $info['id'];
                $list[$key]['tid'] = $info['tid'];
                $list[$key]['desc'] = $info['describe'];
                $list[$key]['time'] = date('Y-m-d H:i', $info['create_at']);
                $list[$key]['upic'] = \app\api\model\User::where(['delete_at' => 0, 'uuid' => $info['uuid']])->value('avatar');
                $list[$key]['uname'] = \app\api\model\User::where(['delete_at' => 0, 'uuid' => $info['uuid']])->value('nickname');
                $list[$key]['content'] = json_decode($info['content']);
                $list[$key]['read_total'] = $info['read_total'];
//               array_push($list,$info);
            }

        }
        if ($list) {
            Utitls::sendJson(200, $list);
        }
        Utitls::sendJson(500);
    }

//    我的店铺收藏
    public function getMyShopsCollection()
    {
        $params = Request::param();
        $collect_shops_ids = UsersModel::where(['openid' => $params['openid'], 'delete_at' => 0])->value('collect_shops_ids');
        $uuid = UsersModel::getUuid($params['openid']);

        $list = [];
        if ($collect_shops_ids) {
            $arr = json_decode($collect_shops_ids, true);
            $list = [];
            foreach ($arr as $key => $value) {
                $info = \app\api\model\Shop::where(['id' => $value])->find();
//                $distance = getDistance($params['lat'],$params['lng'], $value['lat'], $value['lng']);

//           print $distance ;
                $list[$key]['upic'] = \app\api\model\User::where(['delete_at' => 0, 'uuid' => $info['uuid']])->value('avatar');
                $list[$key]['uname'] = \app\api\model\User::where(['delete_at' => 0, 'uuid' => $info['uuid']])->value('nickname');
                $list[$key]['name'] = $info['name'];
                $list[$key]['id'] = $info['id'];
                $list[$key]['info'] = $info['info'];
                $list[$key]['address'] = $info['address'];
                $list[$key]['phone'] = $info['phone'];
                $list[$key]['fengmian'] = $info['fengmian'];
                $list[$key]['shop_time'] = $info['shop_time'];
                $list[$key]['read_total'] = $info['read_total'];
//                $list[$key]['distance'] =  $distance;
            }

        }
        if ($list) {
            Utitls::sendJson(200, $list);
        }
        Utitls::sendJson(500);
    }

    //  绑定父级 - money 邀请人数 积分
    public function tapParent()
    {

        $params = Request::param();
        if ($params['openid'] == $params['p_id']){//自己绑定自己的情况
            return;
        }

//       找出自己，p_id加上
        $my_p_id = \app\api\model\User::where(['openid' => $params['openid'], 'delete_at' => 0])->value('p_id');
        $my_c_id = \app\api\model\User::where(['openid' => $params['openid'], 'delete_at' => 0])->value('c_id');
        $parent_c_id = \app\api\model\User::where(['openid' => $params['p_id'], 'delete_at' => 0])->value('c_id');
//        如果在我的pid里，说明他已经邀请过我
        if (empty($my_p_id)) {
            $my_p_id = array();
            array_push($my_p_id, $params['p_id']);

        } else {
            $my_p_id = json_decode($my_p_id, true);
            if (in_array($params['p_id'], $my_p_id)) {//已经邀请过了
                Utitls::sendJson(201, '', '他已经邀请过我了，不能多次邀请');
                exit();
            } else {

                array_push($my_p_id, $params['p_id']);
            }
        }
//        如果在我的cid里，说明我已经邀请过他
        if (empty($my_c_id)) {

        } else {
            $my_c_id = json_decode($my_c_id, true);
            if (in_array($params['p_id'], $my_c_id)) {//已经邀请过了
                Utitls::sendJson(202, '', '我已经邀请过他了，不能多次邀请');
                exit();
            } else {

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

        \app\api\model\User::where(['openid' => $params['openid'], 'delete_at' => 0])->update(
            [
                'p_id' => $p_idData
            ]
        );

        $c_idData = json_encode($parent_c_id, true);
//父级更新cid
        \app\api\model\User::where(['openid' => $params['p_id'], 'delete_at' => 0])->update(
            [
                'c_id' => $c_idData,
            ]
        );
//        找出父级，分享数+1
        $where = ['openid' => $params['p_id'], 'delete_at' => 0];
        $shared_count = \app\api\model\User::where($where)->value('shared_count');
        $res = \app\api\model\User::where($where)->update(
            [
                'shared_count' => $shared_count + 1
            ]
        );

        //收益
        $parent_uuid = \app\api\model\User::getUuid($params['p_id']);
        $walletWhere = ['uuid' => $parent_uuid, 'delete_at' => 0];
        $money = UsersWallet::where($walletWhere)->value('money');
        $point = UsersWallet::where($walletWhere)->value('point');

        $res = \app\api\model\UsersWallet::where($walletWhere)->update(
            [
                'money' => $money + 1,
                'point' => $point + \config('api.collectjifen'),
            ]
        );

        // 积分log
        $data = ['uuid'=>$parent_uuid,'type'=>'1','point'=>config('api.invitationjifen'),'iorr'=>"1",'desc'=>'邀请好友获取积分','create_at'=>time()];
        \db('user_point_log')->insert($data, false, true, null);;

        if ($res) {
            Utitls::sendJson(200);
        }
        Utitls::sendJson(500);
    }

    //  所有子级 -
    public function getMyActivityNum()
    {
        $params = Request::param();
        $c_id = UsersModel::where(['openid' => $params['openid'], 'delete_at' => 0])->value('c_id');

        if (!empty($c_id)) {
            $c_idArr = json_decode($c_id, true);
            $list = [];
//            foreach ($list as $key => $value) {
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

    //  钱包- money 邀请人数 积分
    public function getWallet(){
        $params = Request::param();
        $userinfo = UsersModel::where(['openid' => $params['openid'], 'delete_at' => 0])->find();
        $uuid = UsersModel::getUuid($params['openid']);
        $res = UsersWallet::where(['uuid' => $uuid, 'delete_at' => 0])->find();
        $map['shared_count'] = $userinfo['shared_count'];

       $myNews = \app\api\model\News::where(['uuid' => $uuid,'delete_at'=>0])->select();
       $commentCount = \app\api\model\NewsComments::where(['user_id' => $uuid,'delete_at'=>0])->count();
        $readtotal = 0;
        foreach ($myNews as $index => $value){
            $readtotal += $value['read_total'];
        }
        $map['read_jifen'] = $readtotal* config('api.readjifen');  //累计阅读积分
        $map['comment_jifen'] = $commentCount*config('api.commentjifen');//累计评论积分

        $map['money'] = $res['money'];
        $map['skewm'] = $userinfo['skewm'];
        $map['tixianCount'] = config('api.tixianCount');
        $map['point'] = $res['point'];
        $map['deposit'] = $res['deposit'];
        $map['kouhaotip'] = config('api.kouhaotip');
        if ($map) {
            Utitls::sendJson(200, $map);
        }
        Utitls::sendJson(500);
    }

    //  所有用户
    public function getAllUsers()
    {

        $userArr = UsersModel::where(['delete_at' => 0])->order('id desc')->paginate(10);
        $count = UsersModel::where(['delete_at' => 0])->count();
        if ($userArr) {
            $list = [];
            foreach ($userArr as $key => $value) {
                $userWallet = UsersWallet::where(['uuid'=>$value['uuid']])->find();
                $list[$key]['money'] = $userWallet['money'];
                $list[$key]['point'] = $userWallet['point'];
                $list[$key]['deposit'] = $userWallet['deposit'];
                $list[$key]['nickname'] = $value['nickname'];
                $list[$key]['avatar'] = $value['avatar'];
                $list[$key]['create_at'] =date('Y-m-d H:i', $value['create_at']);
            }
            if ($list) {
                Utitls::sendJson(200, ['list' => $list, 'page' => ceil($count / 10),'count'=>$count]);
            }
            Utitls::sendJson(500);
        }
        Utitls::sendJson(500);
    }

     //  积分明细
    public function getjifenList()
    {
        $params = Request::param();
//        $shared_count = UsersModel::where(['openid' => $params['openid'], 'delete_at' => 0])->value('shared_count');
        $uuid = UsersModel::getUuid($params['openid']);
        $info = db('user_point_log')->where(['uuid' => $uuid, 'delete_at' => 0])->order('id desc')->paginate($listRows = 3);
        $count =  db('user_point_log')->where(['uuid' => $uuid, 'delete_at' => 0])->count();

        $list=[];
        foreach ($info as $key =>$value){
            $list[$key]['point'] = $value['point'];
            $list[$key]['id'] = $value['id'];
            $list[$key]['iorr'] = $value['iorr'];
            $list[$key]['desc'] = $value['desc'];
            $list[$key]['time'] = date('Y-m-d H:i', $value['create_at']);
            $list[$key]['type'] =  $value['type'];

        }
        if ($list) {
            Utitls::sendJson(200,  ['list' => $list, 'page' => ceil($count / 1)]);
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
        $point =floatval( UsersWallet::where(['uuid' => $uuid, 'delete_at' => 0])->value('point'));

        $count = floatval($params['count']);//要兑换的积分
        if($count>config('api.duijifenmax')){
            Utitls::sendJson(501, '兑换的积分数太多');
        }else{
            if($point >= $count){
                $info['point'] = $point - $count;
                $info['money'] =floatval($info['money'])+ $count/config('api.duijifen');
                UsersWallet::where(['uuid' => $user, 'delete_at' => 0])->update(['money' => $info['money'],'point'=>$info['point']]);
                //积分记录里添加记录
                //根据id积分log，添加记录
                $data = ['uuid'=>$user,'type'=>'0','point'=>$count,'iorr'=>"0",'desc'=>'积分兑换','create_at'=>time()];
                \db('user_point_log')->insert($data, false, true, null);
                Utitls::sendJson(200, '兑换成功');
            }else{
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
       $mytixians = db('user_tixian_log')->where(['uuid'=>$uuid])->select();

       $times=0;
       foreach ($mytixians as $key=>$value){
//           dump(date('Ymd', time()));
//           dump(time());
//           dump($value['create_at']);
//           dump(date('Ymd',$value['create_at']));
           if(date('Ymd', time()) == date('Ymd',$value['create_at'])) {

               $times++;
           }
       }
//dump($times);
//       exit();
       if($times>=3){
           Utitls::sendJson(501,'','今天已达到了提现次数，请明日再提现');
       }


        UsersWallet::where(['uuid' => $uuid, 'delete_at' => 0])->update(
            [
            'money'=> $info['money'] - $params['money']*1.01,
            'deposit'=> $info['deposit'] + $params['money']*1.01,
            'update_at'=>time()
        ]);

        $data = ['uuid'=>$uuid,'money'=>$params['money'],'status'=>"0",'create_at'=>time()];

        $insert = db('user_tixian_log')->insert($data, false, true, null);

        if ($insert) {
            $nickname = \app\api\model\User::where(['uuid'=>$uuid])->value('nickname');

            $data = [
                "amount1" => [
                    "value"=> $params['money']
                ],
                "time5" => [
                    "value"=> date('Y-m-d H:i:s',time())
                ],
                "thing6" => [
                    "value"=> $nickname
                ]
            ];
            $page = '/pages/mine/mine/managed/user/tixian';
            $kuaidi =  new Kuaidi();
            $ret= $kuaidi->sendKuaidiDaiSureMessage(config('api.laiguofengopenid'),'Kif7c4hZGuPjI-4Wp5cwDLKWfkQ9UsiVh5AVRFsbuz0',$page,$data);
            Utitls::sendJson(200, null,'提现申请成功');
        }
        Utitls::sendJson(500);
    }

    //  提现记录列表
    public function gettxRecordList()
    {
        $params = Request::param();
        $uuid = UsersModel::getUuid($params['openid']);
        $info = db('user_tixian_log')->where(['uuid' => $uuid, 'delete_at' => 0])->order('id desc')->select();

        $list=[];
        foreach ($info as $key =>$value){
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
        $count = db('user_tixian_log')->where(['status'=>$params['status'], 'delete_at' => 0])->count();
        $info = db('user_tixian_log')->where(['status'=>$params['status'], 'delete_at' => 0])->order('id desc')->paginate(10);

        $list=[];
        foreach ($info as $key =>$value){
            $userinfo = \app\api\model\User::where(['uuid'=>$value['uuid']])->find();
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
            Utitls::sendJson(200,['list'=>$list,'count'=>$count]);
        }
        Utitls::sendJson(500);
    }


    // 我的订单
    public function myorder()
    {
        $params = Request::param();
        $uuid = UsersModel::getUuid($params['openid']);
        $count = Orders::where(['uuid' => $uuid, 'delete_at' => 0])->order('id desc')->count();
        $info = Orders::where(['uuid' => $uuid, 'delete_at' => 0])->order('id desc')->paginate(10);

        $list=[];
        foreach ($info as $key =>$value){
            $list[$key]['money'] = $value['total']/100;
            $list[$key]['id'] = $value['id'];
            $list[$key]['sn'] = $value['sn'];
            $list[$key]['payTip'] = $value['payTip'];
            switch ($value['status']) {
                case -1:
                    $list[$key]['status'] = '已取消';
                    break;
                case 0:
                    $list[$key]['status'] = '等待支付';
                    break;
                case 1:
                    $list[$key]['status'] = '支付成功';
                    break;
                default:
                    break;
            }
            $list[$key]['time'] = date('Y-m-d H:i', $value['create_at']);

        }
        if ($list) {
            Utitls::sendJson(200, ['list'=>$list,'count'=>$count/10]);
        }
        Utitls::sendJson(500);
    }


    //更新用户收款二维码updateEWMToUserInfo
    public function updateEWMToUserInfo()
    {
        $params = Request::param();
        $info = \app\api\model\User::where(['openid' => $params['openid'], 'delete_at' => 0])->update(
            [ 'skewm'=>$params['ewm'],

            ]
        );
        if ($info) {
            Utitls::sendJson(200, null,'更新收款二维码成功');
        }
        Utitls::sendJson(500);

    }

    // 是否已认证
    public function getAuthStatus()
    {
        $params = Request::param();

//     0未审核   1审核中 2审核通过 3审核失败
        $res = \app\api\model\User::where(['openid' => $params['openid'], 'delete_at' => 0])->value('is_auth');
        $map = \app\api\model\User::where(['openid' => $params['openid'], 'delete_at' => 0])->find();
        $userinfo['name'] = $map['name'];
        $userinfo['phone'] = $map['phone'];
        $userinfo['idcard'] = $map['idcard'];
        $userinfo['idcard_pic'] = $map['idcard_pic'];
        $userinfo['user_jinzhao'] = $map['user_jinzhao'];
        $userinfo['status'] = $map['is_auth'];

        if ($res == 0) {
            Utitls::sendJson(200, ['status' => 0], '未认证');
        } else if ($res == 1) {
            Utitls::sendJson(200, $userinfo, '审核中', $userinfo);
        } else if ($res == 2) {
            Utitls::sendJson(200, $userinfo, '审核通过');
        } else if ($res == 3) {
            Utitls::sendJson(200, $userinfo, '资料不符合，请确认后重新提交');
        } else {
            Utitls::sendJson(500);
        }
    }

    // 是否已认证司机
    public function getAuthcarHoster()
    {
        $params = Request::param();
//     0未审核   1审核中 2审核通过 3审核失败
        $res = \app\api\model\User::where(['openid' => $params['openid'], 'delete_at' => 0])->value('is_carhoster');
        if ($res == 0) {
            Utitls::sendJson(200, ['status' => 0], '未认证');
        } else if ($res == 1) {
            Utitls::sendJson(200, ['status' => 1], '审核中');
        } else if ($res == 2) {
            Utitls::sendJson(200, ['status' => 2], '审核通过');
        } else if ($res == 3) {
            Utitls::sendJson(200, ['status' => 3], '资料不符合，请重新提交');
        } else {
            Utitls::sendJson(500);
        }
    }

    //  用户提交审核信息
    public function bind()
    {
        $params = Request::param();
        $user = UsersModel::where(['openid' => $params['openid'], 'delete_at' => 0])->update(
            [
                'name' => $params['name'],
                'phone' => $params['phone'],
                'idcard' => $params['idcard'],
                'idcard_pic' => $params['idcard_pic'],
                'is_auth' => 1,
                'user_jinzhao' => $params['user_jinzhao'],
            ]
        );
        if ($user) {
            Utitls::sendJson(200);
        }
        Utitls::sendJson(500);
    }

    //  拒绝-用户绑定信息
    public function refusebindUserInfo()
    {
        $params = Request::param();
        $user = UsersModel::where(['openid' => $params['openid']])->update(
            [
                'name' => null,
                'phone' => null,
                'idcard' => null,
                'idcard_pic' => null,
                'is_auth' => 3,
                'auth_text' => $params['auth_text']
            ]
        );
        if ($user) {
            Utitls::sendJson(200);
        }
        Utitls::sendJson(500);
    }

    //  同意-用户绑定信息 0未审核   1审核中 2审核通过 3审核失败
    public function agreebindUserInfo()
    {

        $params = Request::param();
        $user = UsersModel::where(['openid' => $params['openid']])->update(
            ['is_auth' => 2,]
        );
        if ($user) {
            Utitls::sendJson(200);
        }
        Utitls::sendJson(500);
    }

    //  用户认证驾驶证
    public function bindcarCert()
    {
        $params = Request::param();
        //校验验证码是否正确
        if ($params['code'] != '8888') {
            Utitls::sendJson(500, '', '验证码不正确');
        }

        $user = UsersModel::where(['openid' => $params['openid'], 'delete_at' => 0, 'is_carhoster' => 0])->update(
            [
                'name' => $params['name'],
                'phone' => $params['phone'],
                'idcard' => $params['idcard'],
                'idcard_pic' => $params['idcard_pic'],
                'car_cert' => $params['car_cert'],
                'is_auth' => 1,
                'is_carhoster' => 1,
            ]
        );
        if ($user) {
            Utitls::sendJson(200);
        }
        Utitls::sendJson(500);
    }

    //  拒绝-用户绑定驾照信息
    public function refusebindUserCarHoster()
    {
        $params = Request::param();
        $user = UsersModel::where(['openid' => $params['openid']])->update(
            [
                'idcard_pic' => null,
                'is_carhoster' => 3,
                'is_carhoster_text' => $params['is_carhoster_text']
            ]
        );
        if ($user) {
            Utitls::sendJson(200);
        }
        Utitls::sendJson(500);
    }

    //  同意-用户绑定驾照信息 0未审核   1审核中 2审核通过 3审核失败
    public function agreebindUserCarHoster()
    {

        $params = Request::param();
        $user = UsersModel::where(['openid' => $params['openid']])->update(
            ['is_auth' => 2,
                'is_carhoster' => 2,
            ]
        );
        if ($user) {
            Utitls::sendJson(200);
        }
        Utitls::sendJson(500);
    }


    //  设置默认地址
    public function AddressToMoren()
    {
        $params = Request::param();
        $uuid = UsersModel::getUuid($params['openid']);
        //获取个人的地址
        $list = UsersAddress::where(['uuid' => $uuid, 'delete_at' => 0])->select();
        foreach ($list as $key => $value) {
            if ($value['id'] == $params['id']) {
                UsersAddress::where(['delete_at' => 0, 'id' => $value['id']])->update(['moren' => 1]);
            } else {
                UsersAddress::where(['delete_at' => 0, 'id' => $value['id']])->update(['moren' => 0]);
            }
        }
        Utitls::sendJson(200);
    }

    //  默认的收货地址
    public function getMorenAddress()
    {
        $params = Request::param();
        $uuid = UsersModel::getUuid($params['openid']);
        $res = UsersAddress::where(['uuid' => $uuid, 'delete_at' => 0, 'moren' => 1])->find();
        if($res){
            $map['name'] = $res['name'];
            $map['id'] = $res['id'];
            $map['phone'] = $res['phone'];
            $map['detail'] = $res['detail'];
            $map['other'] = $res['other'];
            $map['lat'] = $res['lat'];
            $map['lng'] = $res['lng'];
            Utitls::sendJson(200, $map);
        }

        Utitls::sendJson(500);
    }

    public function tuikuan(){
        $params = Request::param();
        //移除到期且未付款的
        $gqnopaykdarr = \app\api\model\Kuaidi::where(['delete_at'=>0,'pay'=>0])->whereTime('songdao_time', '<=', time())->select();
        if($gqnopaykdarr) {
            foreach ($gqnopaykdarr as $key => $value) {
                \app\api\model\Kuaidi::where(['id'=>$value['id']])->update(
                    ['delete_at'=>1]
                );
            }
        }
        //查快递的过期且已支付的订单号，退款。
        $kdarr = \app\api\model\Kuaidi::where(['delete_at'=>0,'pay'=>1])->whereTime('songdao_time', '<=', time())->select();
        if($kdarr){
            foreach ($kdarr as $key =>$value){
                $where = ['delete_at'=>0,'sn'=>$value['order_id'],'status'=>1];
                $kdorder = Orders::where($where)->find();
                $orderNo = $kdorder['sn'];      //商户订单号（商户订单号与微信订单号二选一，至少填一个）
                $wxOrderNo = '';    //微信订单号（商户订单号与微信订单号二选一，至少填一个）
                $totalFee =$kdorder['total'] /100;       //订单金额，单位:元
                $refundFee = $kdorder['total']/100;      //退款金额，单位:元
                $tixian = new Tixian();
                $result = $tixian->doRefund($totalFee, $refundFee, $wxOrderNo, $orderNo);
                if ($result == true) {
                    //业务逻辑处理,改为已退款，并移除
                    $change =  Orders::where($where)->update(
                        ['status'=>2,'delete_at'=>1]
                    );
                    \app\api\model\Kuaidi::where(['delete_at'=>0,'order_id'=>$value['order_id'],'pay'=>1])->update(
                        ['pay'=>2,'delete_at'=>1]
                    );
                    if($change){
                        Utitls::sendJson(200,$result,'退款已成功，请留意到账信息');
                    }
                    Utitls::sendJson(200,$result,'退款已成功，请留意到账信息');
                }
                Utitls::sendJson(500,$result,'当前操作人数较多，请稍后重试');
            }
        }
    }

    //  收货地址
    public function getAddressById()
    {
        $params = Request::param();
        $res = UsersAddress::where(['id' => $params['id'], 'delete_at' => 0])->find();
        $map['name'] = $res['name'];
        $map['phone'] = $res['phone'];
        $map['detail'] = $res['detail'];
        $map['other'] = $res['other'];
        $map['lat'] = $res['lat'];
        $map['lng'] = $res['lng'];
        if ($map) {
            Utitls::sendJson(200, $map);
        }
        Utitls::sendJson(500);
    }

    // 添加收货地址
    public function addAddress()
    {
        $params = Request::param();

        $map['uuid'] = UsersModel::getUuid($params['openid']);
       $address = UsersAddress::where(['uuid'=>$map['uuid'],'delete_at'=>0])->find();
       if($address){
           $map['moren'] = 0;
       }else{
           $map['moren'] = 1;
       }
        $map['name'] = $params['name'];
        $map['phone'] = $params['phone'];
        $map['detail'] = $params['detail'];
        $map['other'] = $params['other'];
        $map['lat'] = $params['lat'];
        $map['lng'] = $params['lng'];
        $res = UsersAddress::create($map);
        if ($res) {
            Utitls::sendJson(200);
        }
        Utitls::sendJson(500);
    }

    //  收货地址列表
    public function getAddress()
    {
        $params = Request::param();
        $uuid = UsersModel::getUuid($params['openid']);
        $res = UsersAddress::where(['uuid' => $uuid, 'delete_at' => 0])->select();
        if ($res) {
            Utitls::sendJson(200, $res);
        }
        Utitls::sendJson(500);
    }

    //  删除地址
    public function delAddress()
    {
        $params = Request::param();
        $res = UsersAddress::where(['id' => $params['id']])->update(['delete_at' => 1]);
        if ($res) {
            Utitls::sendJson(200);
        }
        Utitls::sendJson(500);
    }

    //  编辑地址
    public function editAddress()
    {
        $params = Request::param();
        $res = UsersAddress::where(['id' => $params['id'], 'delete_at' => 0])->update(
            [
                'name' => $params['name'],
                'phone' => $params['phone'],
                'detail' => $params['detail'],
                'other' => $params['other'],
                'lat' => $params['lat'],
                'lng' => $params['lng'],
            ]
        );
        if ($res) {
            Utitls::sendJson(200);
        }
        Utitls::sendJson(500);
    }

    public function base()
    {
        $params = Request::param();
        $info = UsersModel::where(['openid' => $params['openid'], 'delete_at' => 0])->find();
        $res['openid'] = $info['openid'];
        $res['nickname'] = $info['nickname'];
        $res['avatar'] = $info['avatar'];
        $res['phone'] = $info['phone'];
        $res['kefuwx'] = config('api.kefuwx');
        $res['zsphone'] = config('api.zsphone');
        $res['inviteTip'] = '每邀请一位新朋友，将得到1-3元的现金红包喔~';
        $res['ruzhubg'] = '';
        Utitls::sendJson(200, $res);
    }

}
