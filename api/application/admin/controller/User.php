<?php
/**
 * Created by PhpStorm.
 * User: Xs___
 * Date: 2019/11/27
 * Time: 22:56:21
 * Desc:
 */

namespace app\admin\controller;

use app\api\model\Roles;
use app\api\model\UserType;
use app\api\model\User as UsersModel;
use app\api\model\UsersAddress;
use app\api\model\UsersWallet;
use app\Utitls;
use think\facade\Request;
//use WXBizDataCrypt;

class User
{
    //登陆
    public function login()
    {
        $params = Request::param();
        $user = Db('user')->where(['account' => $params['name'], 'password' => md5($params['password']),'identity' => 2])->find();
        if ($user) {
            if ($user['status'] == 0) {
                $token = md5(mt_rand(111111, 999999) . time());
                Utitls::sendJson(200, $token, '登陆成功');
            }
            Utitls::sendJson(500, '', '您的用户状态不可用，请联系管理员');
        } else {
            Utitls::sendJson(500, '', '用户名或密码错误');
        }
    }

//    获取用户的权限模块
    public function getUseruthority()
    {
        $params = Request::param();
//        $role = $params['role']; // 1管理者 2经销商
//        if ($role == '1') {
//            $managed = db('managed')->where(['phone' => $params['phone']])->find();
//            if($managed['role']>0){
//                $menulist = $this->getMenuListByRoleId($managed['role']);
                $menulist = $this->getMenuListByRoleId();
                if (count($menulist) > 0) {
                    Utitls::sendJson(200, $menulist, '成功');
                }
//            }
//            Utitls::sendJson(200, [], '暂无相关权限');
//        } else { //经销商可导出自己的财务报表myConsumeList
//            $menulist = [];
//            $a =[
//                "id" => 102,
//                "name" => "经销商管理",
//                "path" => "myConsumeList",
//                "children" => [
//                    [
//                        "id" => 1,
//                        "name" => '我的财务列表',
//                        "path" => 'myConsumeList',
//                        "children" => [],
//                        "order" => null
//                    ]
//                ],
//                "order" => 4
//
//            ];
//            array_push($menulist, $a);
//            if (count($menulist) > 0) {
//                Utitls::sendJson(200, $menulist, '成功');
//            } else {
//                Utitls::sendJson(200, [], '暂无相关权限');
//            }
//        }
    }

    //根据角色id获取菜单列表
//    public function getMenuListByRoleId($id)
    public function getMenuListByRoleId()
    {
//        $user_roleids = UserType::where(['id' => $id])->value('role_ids');
//        $rolesIdArr = json_decode($user_roleids, true);
//        if (!$rolesIdArr) {
//            return [];
//        }

        $allRights = Roles::select();
        $allRightssss = $this->sort($allRights);
//        return  $allRightssss;
        $haveList = [];
//        foreach ($rolesIdArr as $key => $value) {
            foreach ($allRightssss as $key1 => $value1) {
//                if ($value['id'] == $value1['id']) { //我有的权限id
//                    if($value1['pid']>0){
//                        $value1['canLook'] = $value['canLook'];
//                    }

                    array_push($haveList, $value1);
//                }
            }
//        }
        $oneList = [];
        foreach ($haveList as $key => $value) {
            if ($value['pid'] == 0) {//一级菜单
                $twolist = [];
                foreach ($haveList as $key1 => $value1) {
                    if ($value1['pid'] == $value['id']) {
                        array_push($twolist, $value1);
                    }
                }
                $value['children'] = $twolist;
                array_push($oneList, $value);
            }
        }

        return $oneList;
    }
    public function sort($data, $pid = 0, $level = 0)
    {
        static $arr = array();
        foreach ($data as $key => $v) {
            if ($v['pid'] == $pid) {
                $v['level'] = $level;
                $arr[] = $v;
                $this->sort($data, $v['id'], $level + 1);
            }
        }
        return $arr;
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


    //  提现所有的记录
    public function getAlltxRecordList()
    {
        $params = Request::param();
        $type = $params['type'];
        if ($type==0){//未审核
            $count = db('user_tixian_log')->where([ 'delete_at' => 0,'status'=>0])->count();
            $info = db('user_tixian_log')->where([ 'delete_at' => 0,'status'=>0])->order('id desc')->paginate(10);

        }else if ($type==1){//已审核
            $count = db('user_tixian_log')->where([ 'delete_at' => 0,'status'=>1])->count();
            $info = db('user_tixian_log')->where([ 'delete_at' => 0,'status'=>1])->order('id desc')->paginate(10);

        }else{//已拉黑
            $count = db('user_tixian_log')->where([ 'delete_at' => 1])->count();
            $info = db('user_tixian_log')->where([ 'delete_at' => 1])->order('id desc')->paginate(10);
        }
        $list=[];
        foreach ($info as $key =>$value){
            $list[$key]['upic'] = \app\api\model\User::where(['delete_at' => 0, 'uuid' => $value['uuid']])->value('avatar');
            $list[$key]['uname'] = \app\api\model\User::where(['delete_at' => 0, 'uuid' => $value['uuid']])->value('nickname');

            $list[$key]['money'] = $value['money'];
            $list[$key]['id'] = $value['id'];
            $list[$key]['skewm'] = $value['skewm'];
            $list[$key]['time'] = date('Y-m-d H:i', $value['create_at']);
        }
        if ($list) {
            Utitls::sendJson(200, ['list' => $list, 'page' => ceil($count / 10),'count'=>$count]);
        }
        Utitls::sendJson(500);
    }


    //设置提现是否转账  status=0拒绝 status=1通过
    public function setTX()
    {
        $params = Request::param();
        $info = db('user_tixian_log')->where(['id'=>$params['id']])->find();
        if ($info){
            $res= db('user_tixian_log')->where('id', $params['id'])->update(['status' => $params['status'],'delete_at' => !$params['status']]);
            if($res){
                Utitls::sendJson(200, $info,'设置成功');
            }else{
                Utitls::sendJson(500,null,'设置失败');
            }
        }else{
            Utitls::sendJson(500,null,'数据已被删除');
        }
    }

}
