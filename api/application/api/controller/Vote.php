<?php
/**
 * Created by PhpStorm.
 * User: Xs___
 * Date: 2019/11/27
 * Time: 22:56:21
 * Desc:
 */

namespace app\api\controller;

use app\Utitls;
use function PHPSTORM_META\elementType;
use think\Db;
use think\facade\Request;

class Vote
{

//    添加
    public function add()
    {
        $params = Request::param();
        $res = \app\api\model\Vote::createVote($params);
        if ($res) {
          Utitls::sendJson(200, $res);
        }
        Utitls::sendJson(500);
    }

//    编辑
    public function edit()
    {
        $params = Request::param();
        $shopinfo = \app\api\model\Shop::where(['id' => $params['id'], 'delete_at' => 0])->find();
        if ($shopinfo) {
//            dump($params);
            $params['status'] = 0;
            $res = \app\api\model\Shop::updateShop($params);

            if ($res) {
                $qsopenid = config('api.laiguofengopenid');
                //给用户发送待审核订阅消息
                $data = [
                    "thing1" => [
                        "value" => '店铺信息编辑提醒'
                    ],
                    "time3" => [
                        "value" => date('Y-m-d H:i:s', time())
                    ],
                    "phone_number5" => [
                        "value" => $params['phone']
                    ],
                    "phrase4" => [
                        "value" => "未审核"
                    ]
                ];
                $page = '/pages/mine/ruzhu/managed';
                $kuaidi = new Kuaidi();
                $ret = $kuaidi->sendKuaidiDaiSureMessage($qsopenid, 'A3MHxijurk_7PxH3QLMhAcmvzQtN0eZ3PUV3e2mUhmU', $page, $data);
                Utitls::sendJson(200, $ret);

            }

        }
        Utitls::sendJson(500);
    }

//取出所有的数据
    public function index()
    {
        $info = \app\api\model\Vote::where(['delete_at' => 0])->whereIn('status',[1,3])->order('id desc')->select();
        $list = [];
        foreach ($info as $key => $value) {
            $list[$key]['id'] = $value['id'];
            $list[$key]['fengmian'] = $value['fengmian'];
            $list[$key]['title'] = $value['title'];
            $list[$key]['startTime'] = $value['startTime'];
            $list[$key]['startDate'] = $value['startDate'];
            $list[$key]['endDate'] = $value['endDate'];
            $list[$key]['endTime'] = $value['endTime'];
            $list[$key]['desc'] = $value['desc'];
            $list[$key]['tule'] = $value['rule'];
            $list[$key]['award'] = $value['award'];
            $list[$key]['status'] = $value['status'];
        }
        Utitls::sendJson(200, $list);
    }

    //根据id获取详情
    public function detail()
    {
        $params = Request::param();
        $info = \app\api\model\Vote::where(['delete_at' => 0, 'id' => $params['id']])->find();
        if ($info) {
//            $info['collectShopCount'] = $info['collectShopCount'];
//            $info['name'] = $info['name'];
//            $info['id'] = $info['id'];
//            $info['tip'] = $info['tip'];
//            $info['info'] = $info['info'];
//            $info['type'] = $info['type'];
//            $info['phone'] = $info['phone'];
//            $info['address'] = $info['address'];
//            $info['shop_time'] = $info['shop_time'];
//            $info['fengmian'] = $info['fengmian'];
//            $info['zhizhao'] = $info['zhizhao'];
//            $info['shop_time'] = $info['shop_time'];
//            $info['content'] = json_decode($info['content']);;
//            $info['time'] = date('Y-m-d H:i', $info['create_at']);
//            $info['read_total'] = $info['read_total'];
//            unset($info['istuijian'], $info['update_at'], $info['create_at'], $info['delete_at']);
            Utitls::sendJson(200, $info);
        } else {
            Utitls::sendJson(500, null, '数据已被删除');
        }
    }

    //参与投票
    public function join()
    {
        $params = Request::param();
        $info = \app\api\model\Vote::where(['delete_at' => 0, 'id' => $params['v_id']])->find();
        if ($info) {
//            $info['collectShopCount'] = $info['collectShopCount'];
            $map['title'] = $params['title'];
            $map['v_id'] = $params['v_id'];
            $map['fengmian'] = $params['fengmian'];
            $map['title'] = $params['title'];
            $map['imgArr'] = $params['imgArr'];
            $map['desc'] = $params['desc'];
            $map['create_at'] = time();
            $res = \db('vote_item')->insertGetId($map);
            Utitls::sendJson(200, $res);
        } else {
            Utitls::sendJson(500, null, '此投票活动已被删除');
        }
    }

    /** 浏览量加一   三分钟内重复访问无效
     * @param $id  文章id
     */
    public function increateClick()
    {
        $id = Request::param('id');
//        dump($id);
        if ($id) {
//            session_start();
            $sessonName = md5($id . $this->getip());
//            dump($sessonName);
//            \think\facade\Session::set($sessonName,time());
//           $se= \think\facade\Session::get($sessonName);
//            dump(!$se);
//            exit();

            //设置缓存（有效期3600秒）


//获取缓存数据可以使用：

//            print(\think\facade\Cache::get($sessonName));
//            if (!\think\facade\Session::get($sessonName)) { //没有session进来 +1
            if (!\think\facade\Cache::get($sessonName)) { //没有session进来 +1
//                print('meiyou '.\think\facade\Cache::get($sessonName));

//                \think\facade\Session::set($sessonName,time());
//                $has= \think\facade\Session::has($sessonName);
//                $se= \think\facade\Session::get($sessonName);
                $has = \think\facade\Cache::set($sessonName, time(), 3600);
//                dump($has);

//            exit();
//                \think\facade\Session::set($sessonName, time());
                $info = \app\api\model\Shop::where(['delete_at' => 0, 'id' => $id])->find();
                $readTotal = $info['read_total'] + 1;
                $info = Db('shop')->where(['id' => $id])->update(['read_total' => $readTotal]);//setInc()实现hits+1

            } else if (time() - \think\facade\Cache::get($sessonName) < 300) { //session创建了10秒内  不+1
//                print(time() - \think\facade\Cache::get($sessonName));
            } else { //session创建了大于10秒  +1并清除这个session
//                \think\facade\Session::delete($sessonName);
                $has = \think\facade\Cache::set($sessonName, null, 3600);
//                print('清除Cache');
//                $de = \think\facade\Session::get($sessonName);
                $info = \app\api\model\Shop::where(['delete_at' => 0, 'id' => $id])->find();
                $readTotal = $info['read_total'] + 1;
                $info = Db('shop')->where(['id' => $id])->update(['read_total' => $readTotal]);
            }
        }
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

}
