<?php
/**
 * Created by PhpStorm.
 * User: Xs___
 * Date: 2019/11/27
 * Time: 22:56:21
 * Desc:
 */

namespace app\admin\controller;

use app\Utitls;
use function PHPSTORM_META\elementType;
use think\facade\Request;

class Shops
{
    // 取出所有的数据
    public function index()
    {
        $params = Request::param();
        $type = $params['type'];
        if ($type==0){//未审核
            $count = \app\api\model\Shop::where([ 'delete_at' => 0,'status'=>0])->count();
            $info = \app\api\model\Shop::where([ 'delete_at' => 0,'status'=>0])->order('id desc')->paginate(4);

        }else if ($type==1){//已审核
            $count = \app\api\model\Shop::where([ 'delete_at' => 0,'status'=>1])->count();
            $info = \app\api\model\Shop::where([ 'delete_at' => 0,'status'=>1])->order('id desc')->paginate(4);

        }else{//已拉黑
            $count = \app\api\model\Shop::where([ 'delete_at' => 1])->count();
            $info = \app\api\model\Shop::where([ 'delete_at' => 1])->order('id desc')->paginate(4);
        }

        $list=[];
        foreach ($info as $key => $value) {

//            $distance = getDistance($params['lat'], $params['lng'], $value['lat'], $value['lng']);
            $list[$key]['townName'] = db('town')->where(['delete_at' => 0,'id'=>$value['townid']])->value('name');

            $list[$key]['upic'] = \app\api\model\User::where(['delete_at' => 0, 'uuid' => $value['uuid']])->value('avatar');
            $list[$key]['uname'] = \app\api\model\User::where(['delete_at' => 0, 'uuid' => $value['uuid']])->value('nickname');
            $list[$key]['name'] = $value['name'];
            $list[$key]['id'] = $value['id'];
            $list[$key]['info'] = $value['info'];
            $list[$key]['address'] = $value['address'];
            $list[$key]['phone'] = $value['phone'];
            $list[$key]['read_total'] = $value['read_total'];
            $list[$key]['fengmian'] = $value['fengmian'];
            $list[$key]['time'] = date('Y-m-d H:i', $value['create_at']);
            $list[$key]['shop_time'] = $value['shop_time'];
//            $list[$key]['distance'] = $distance . '公里';
           $shoptype = db('shop_type')->where(['id'=>$value['type']])->find();
            $list[$key]['shoptype'] = $shoptype['name'];

        }
        Utitls::sendJson(200,  ['list' => $list, 'page' => ceil($count / 4)]);
    }

    // 取出所有的店铺数据
    public function allShop()
    {
        $params = Request::param();

        $count = \app\api\model\Shop::where([ 'delete_at' => 0])->count();
        $info = \app\api\model\Shop::where([ 'delete_at' => 0])->order('id desc')->paginate(4);

        $list=[];
        foreach ($info as $key => $value) {

//            $distance = getDistance($params['lat'], $params['lng'], $value['lat'], $value['lng']);
            $list[$key]['townName'] = db('town')->where(['delete_at' => 0,'id'=>$value['townid']])->value('name');

            $list[$key]['upic'] = \app\api\model\User::where(['delete_at' => 0, 'uuid' => $value['uuid']])->value('avatar');
            $list[$key]['uname'] = \app\api\model\User::where(['delete_at' => 0, 'uuid' => $value['uuid']])->value('nickname');
            $list[$key]['name'] = $value['name'];
            $list[$key]['id'] = $value['id'];
            $list[$key]['info'] = $value['info'];
            $list[$key]['address'] = $value['address'];
            $list[$key]['phone'] = $value['phone'];
            $list[$key]['fengmian'] = $value['fengmian'];
            $list[$key]['time'] = date('Y-m-d H:i', $value['create_at']);
            $list[$key]['shop_time'] = $value['shop_time'];

           $shoptype = db('shop_type')->where(['id'=>$value['type']])->find();
            $list[$key]['shoptype'] = $shoptype['name'];

        }
        Utitls::sendJson(200,  ['list' => $list, 'page' => ceil($count / 4)]);
    }



    //根据id获取详情
    public function detail()
    {
        $params = Request::param();
        $info = \app\api\model\Shop::where(['id' => $params['id']])->find();

        if ($info) {
            $info['townName'] = db('town')->where(['delete_at' => 0,'id'=>$info['townid']])->value('name');
            $info['collectShopCount'] = $info['collectShopCount'];
            $info['name'] = $info['name'];
            $info['id'] = $info['id'];
            $info['tip'] = $info['tip'];
            $info['info'] = $info['info'];
            $info['type'] = $info['type'];
            $info['phone'] = $info['phone'];
            $info['address'] = $info['address'];
            $info['shop_time'] = $info['shop_time'];
            $info['fengmian'] = $info['fengmian'];
            $info['zhizhao'] = $info['zhizhao'];
            $info['shop_time'] = $info['shop_time'];
            $info['content'] = json_decode($info['content']);;
            $info['time'] = date('Y-m-d H:i', $info['create_at']);
            $info['read_total'] = $info['read_total'];
            unset($info['istuijian'], $info['update_at'], $info['create_at'], $info['delete_at']);
            Utitls::sendJson(200, $info);
        } else {
            Utitls::sendJson(500, null, '数据已被删除');
        }
    }

    //设置是否通过  status=0拒绝 status=1通过，refusetip:拒绝理由
    public function set()
    {
        $params = Request::param();
        $info = \app\api\model\Shop::where(['id'=>$params['id']])->find();
        if ($info){
           $res= \app\api\model\Shop::where('id', $params['id'])->update(
               [   'status' => $params['status'],
                   'delete_at' => !$params['status'],
                   'create_at' => time(),
                   'vipEndTime' => strtotime('next month'),//默认一个月免费时间
                   'refusetip' => $params['refusetip']]
           );
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
