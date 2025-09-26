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

class Shop
{
//获取类型，已过滤无内容的
    public function type()
    {
        $params = Request::param();
        $res = \app\api\model\ShopType::where(['delete_at' => 0])->order('id sort')->select();
//        dump($res);
//        exit();
        $list = [];
        $townid = $params['townid'];
        foreach ($res as $key => $value) {

            if ($townid == 0) {//全部乡镇
                $re = \app\api\model\Shop::where(['status' => 1, 'type' => $value['id']])->find();
                if ($re) {
                    array_push($list, $value);
                }
            } else {
                $re = \app\api\model\Shop::where(['status' => 1, 'type' => $value['id'], 'townid' => $params['townid']])->find();
                if ($re) {
                    array_push($list, $value);
                }
            }

        }
        if ($list) {
            Utitls::sendJson(200, $list);
        }
        Utitls::sendJson(500);
    }

    //获取系统所有店铺类型
    public function alltypes()
    {
        $params = Request::param();
        $res = \app\api\model\ShopType::where(['delete_at' => 0])->order('id sort')->select();

        if ($res) {
            Utitls::sendJson(200, $res);
        }
        Utitls::sendJson(500);
    }

//搜索
    public function search()
    {
        $params = Request::param();
        if (isset($params['search']) && !empty($params['search'])) {
            $where[] = ['name', 'like', '%' . $params['search'] . '%'];
        }
        $where[] = ['delete_at', '=', 0, 'status', '=', 1];

        $count = \app\api\model\Shop::where($where)->whereIn('vipEndTime', '>=', time())->count();
        $info = db('shop')->where($where)->whereIn('vipEndTime', '>=', time())->order('id desc')->paginate($listRows = 3);

        $list = [];
        foreach ($info as $key => $value) {

            $list[$key]['name'] = $value['name'];
            $list[$key]['id'] = $value['id'];
            $list[$key]['tip'] = $value['tip'];
            $list[$key]['type'] = $value['type'];
            $list[$key]['phone'] = $value['phone'];
            $list[$key]['address'] = $value['address'];
            $list[$key]['shop_time'] = $value['shop_time'];
            $list[$key]['fengmian'] = $value['fengmian'];
            $list[$key]['shop_time'] = $value['shop_time'];
            $distance = getDistance($params['lat'], $params['lng'], $value['lat'], $value['lng']);
            $list[$key]['distance'] = $distance;
        }
        ascArray($list, 'distance');
        if ($list) {
            Utitls::sendJson(200, ['list' => $list, 'page' => ceil($count / 3)]);
        }
        Utitls::sendJson(500);
    }

//    添加
    public function add()
    {
        $params = Request::param();
        $res = \app\api\model\Shop::createShop($params);
        if ($res) {
            $qsopenid = config('api.laiguofengopenid');
            //给用户发送待审核订阅消息
            $data = [
                "thing1" => [
                    "value" => '店铺入驻提醒'
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

    //店铺审核通过
    public function agreenShop()
    {
        $params = Request::param();


        $qsopenid = config('api.laiguofengopenid');
        //给用户发送待审核订阅消息
        $data = [
            "thing1" => [
                "value" => '店铺入驻提醒'
            ],
            "time3" => [
                "value" => date('Y-m-d H:i:s', time())
            ],
            "phone_number5" => [
                "value" => $params['phone']
            ],
            "phrase4" => [
                "value" => "审核"
            ]
        ];
        $page = '/pages/mine/ruzhu/managed';
        $kuaidi = new Kuaidi();
        $ret = $kuaidi->sendKuaidiDaiSureMessage($qsopenid, 'A3MHxijurk_7PxH3QLMhAcmvzQtN0eZ3PUV3e2mUhmU', $page, $data);

    }

//    添加收藏
    public function addCollect()
    {
        $params = Request::param();
        $collect_shops_ids = \app\api\model\User::where(['openid' => $params['openid'], 'delete_at' => 0])->value('collect_shops_ids');
        if (empty($collect_shops_ids)) {
            $collect_shops_ids = array();
            array_push($collect_shops_ids, $params['id']);
        } else {
            $collect_shops_ids = json_decode($collect_shops_ids, true);
            array_push($collect_shops_ids, $params['id']);
        }

        $addData = json_encode($collect_shops_ids, true);
        \app\api\model\User::where(['openid' => $params['openid'], 'delete_at' => 0])->update(
            [
                'collect_shops_ids' => $addData
            ]
        );

        $where = ['id' => $params['id'], 'delete_at' => 0];
        $collectShopCount = \app\api\model\Shop::where($where)->value('collectShopCount');
        $res = \app\api\model\Shop::where($where)->update(
            [
                'collectShopCount' => $collectShopCount + 1
            ]
        );
        if ($res) {
            Utitls::sendJson(200);
        }
        Utitls::sendJson(500);
    }

    // 取消收藏资讯
    public function cancelCollect()
    {
        $params = Request::param();
        //取出自己的collect_news_ids，转数组
        $collect_shops_ids = \app\api\model\User::where(['openid' => $params['openid'], 'delete_at' => 0])->value('collect_shops_ids');
        //是否包含这个资讯id,包含就取消收藏 ，不包含就收藏数+1，collect_news_ids添加ID，更新表
        if ($collect_shops_ids) {
            $arr = json_decode($collect_shops_ids, true);
//           删除数组某一个元素
            if (in_array($params['id'], $arr)) {
                foreach ($arr as $k => $v) {
                    if ($v == $params['id']) {
                        unset($arr[$k]);
                    }
                }
                $addData = json_encode($arr, true);
                \app\api\model\User::where(['openid' => $params['openid'], 'delete_at' => 0])->update(
                    [
                        'collect_shops_ids' => $addData
                    ]
                );
                $where = ['id' => $params['id'], 'delete_at' => 0];
                $collectCount = \app\api\model\Shop::where($where)->value('collectShopCount');
                $res = \app\api\model\Shop::where($where)->update(
                    [
                        'collectShopCount' => $collectCount - 1
                    ]
                );

                Utitls::sendJson(200);
            } else {
                Utitls::sendJson(201, ['status' => 0]);
            }
        } else {
            Utitls::sendJson(500);
        }
    }

    //取出我的商店
    public function myShop()
    {
        $params = Request::param();
        $uuid = \app\api\model\User::where(['openid' => $params['openid']])->value('uuid');
        // status 0审核中，1审核通过，2拒绝  3已过期
        $info = \app\api\model\Shop::where(['delete_at' => 0, 'uuid' => $uuid])->select();
        $list = [];
        foreach ($info as $key => $value) {
            $list[$key]['upic'] = \app\api\model\User::where(['delete_at' => 0, 'uuid' => $uuid])->value('avatar');
            $list[$key]['uname'] = \app\api\model\User::where(['delete_at' => 0, 'uuid' => $uuid])->value('nickname');
            $list[$key]['name'] = $value['name'];
            $list[$key]['id'] = $value['id'];
            $list[$key]['fengmian'] = $value['fengmian'];
            $list[$key]['info'] = $value['tip'];
            $list[$key]['status'] = $value['status'];
            $list[$key]['vipEndTime'] = $value['vipEndTime'];
            $list[$key]['refuseTip'] = $value['refuseTip'];
            $list[$key]['time'] = date('Y-m-d H:i', $value['create_at']);
        }
        if ($list) {

            Utitls::sendJson(200, $list);
        }
        Utitls::sendJson(500);
    }

    //热门
    public function host()
    {
//      status 0审核中，1审核通过，2.拒绝 3.过期了
        $params = Request::param();
        $info = \app\api\model\Shop::where(['delete_at' => 0, 'status' => 1, 'ishost' => 1])->select();
        $list = [];
        foreach ($info as $key => $value) {
            $list[$key]['townName'] = db('town')->where(['delete_at' => 0, 'id' => $value['townid']])->value('name');

            $list[$key]['name'] = $value['name'];
            $list[$key]['id'] = $value['id'];
            $list[$key]['fengmian'] = $value['fengmian'];
            $list[$key]['info'] = $value['info'];
            $list[$key]['phone'] = $value['phone'];
            $list[$key]['shop_time'] = $value['shop_time'];
            $list[$key]['address'] = $value['address'];
            $list[$key]['read_total'] = $value['read_total'];
            $distance = getDistance($params['lat'], $params['lng'], $value['lat'], $value['lng']);
            $list[$key]['distance'] = $distance;
        }
        Utitls::sendJson(200, $list);
    }

    //轮播图
    public function getSwiper()
    {
//      status 0审核中，1审核通过，2拒绝
        $info = \db('swiper')->where(['delete_at' => 0, 'type' => 1])->select();
        $list = [];
        foreach ($info as $key => $value) {
            $list[$key]['id'] = $value['id'];
            $list[$key]['url'] = $value['url'];
            $list[$key]['page'] = $value['page'];
            $list[$key]['tid'] = $value['tid'];
            $list[$key]['vipEndTime'] = $value['vipEndTime'];
        }
        Utitls::sendJson(200, $list);
    }

    //新店
    public function newShop()
    {
        $params = Request::param();
//      status 0审核中，1审核通过，2拒绝
        $info = \app\api\model\Shop::where(['delete_at' => 0, 'status' => 1, 'isNews' => 1])->select();
        $list = [];
        foreach ($info as $key => $value) {
            $distance = getDistance($params['lat'], $params['lng'], $value['lat'], $value['lng']);
            $list[$key]['name'] = $value['name'];
            $list[$key]['id'] = $value['id'];
            $list[$key]['fengmian'] = $value['fengmian'];
            $list[$key]['info'] = $value['info'];
            $list[$key]['phone'] = $value['phone'];
            $list[$key]['shop_time'] = $value['shop_time'];
            $list[$key]['address'] = $value['address'];
            $list[$key]['read_total'] = $value['read_total'];
            $list[$key]['distance'] = $distance;

        }
        Utitls::sendJson(200, $list);
    }

    //随机
    public function suijiNews()
    {
        $num = 5;    //需要抽取的默认条数
        $table = 'news';    //需要抽取的数据表
        $countcus = Db::name($table)->count();    //获取总记录数
        $min = Db::name($table)->min('id');    //统计某个字段最小数据
        if ($countcus < $num) {
            $num = $countcus;
        }
        $i = 1;
        $flag = 0;
        $ary = array();
        while ($i <= $num) {
            $rundnum = rand($min, $countcus);//抽取随机数
            if ($flag != $rundnum) {
                //过滤重复
                if (!in_array($rundnum, $ary)) {
                    $ary[] = $rundnum;
                    $flag = $rundnum;
                } else {
                    $i--;
                }
                $i++;
            }
        }
        $info = Db::name($table)->where(['delete_at' => 0, 'status' => 1])->select();
        $list = [];
        foreach ($info as $key => $value) {
            $list[$key]['title'] = $value['title'];
            $list[$key]['id'] = $value['id'];
            $list[$key]['tid'] = $value['tid'];
            $list[$key]['desc'] = $value['describe'];
            $list[$key]['time'] = date('Y-m-d H:i', $value['create_at']);
            $list[$key]['upic'] = \app\api\model\User::where(['delete_at' => 0, 'uuid' => $value['uuid']])->value('avatar');
            $list[$key]['uname'] = \app\api\model\User::where(['delete_at' => 0, 'uuid' => $value['uuid']])->value('nickname');
            $list[$key]['content'] = json_decode($value['content']);
            $list[$key]['read_total'] = $value['read_total'];
            $list[$key]['townName'] = db('town')->where(['delete_at' => 0, 'id' => $value['townid']])->value('name');

        }
        Utitls::sendJson(200, $list);
    }

//取出所有的数据
    public function index()
    {
        $count = \app\api\model\Shop::where(['delete_at' => 0])->count();
        $info = \app\api\model\Shop::where(['delete_at' => 0])->order('id desc')->paginate(10);
        $list = [];
        foreach ($info as $key => $value) {

            $list[$key]['upic'] = \app\api\model\User::where(['delete_at' => 0, 'uuid' => $value['uuid']])->value('avatar');
            $list[$key]['uname'] = \app\api\model\User::where(['delete_at' => 0, 'uuid' => $value['uuid']])->value('nickname');
            $list[$key]['name'] = $value['name'];
            $list[$key]['townName'] = db('town')->where(['delete_at' => 0, 'id' => $value['townid']])->value('name');

            $list[$key]['id'] = $value['id'];
            $list[$key]['tip'] = $value['tip'];
            $list[$key]['info'] = $value['info'];
            $list[$key]['type'] = $value['type'];
            $list[$key]['phone'] = $value['phone'];
            $list[$key]['address'] = $value['address'];
            $list[$key]['shop_time'] = $value['shop_time'];
            $list[$key]['fengmian'] = $value['fengmian'];
            $list[$key]['zhizhao'] = $value['zhizhao'];
            $list[$key]['shop_time'] = $value['shop_time'];
            $list[$key]['content'] = json_encode($value['content']);;
            $list[$key]['time'] = date('Y-m-d H:i', $value['create_at']);
            $list[$key]['read_total'] = $value['read_total'];
        }
        Utitls::sendJson(200, ['list' => $list, 'page' => ceil($count / 10)]);
    }

//按类型取出
    public function getShopsByType()
    {

        $params = Request::param();
        $type = Request::param('type');
        $townid = Request::param('townid');
        if ($townid == 0) {//全部乡镇
            if ($type == 0) {//推荐
                $count = \app\api\model\Shop::where(['status' => 1, 'delete_at' => 0])->count();
                $info = \app\api\model\Shop::where(['status' => 1, 'delete_at' => 0])->order('read_total desc')->paginate(10);
            } else {
                $count = \app\api\model\Shop::where(['status' => 1, 'delete_at' => 0, 'type' => $type])->count();
                $info = \app\api\model\Shop::where(['status' => 1, 'delete_at' => 0, 'type' => $type])->order('read_total desc')->paginate(10);
            }
        } else {
            if ($type == 0) {//推荐
                $count = \app\api\model\Shop::where(['status' => 1, 'delete_at' => 0, 'townid' => $townid])->count();
                $info = \app\api\model\Shop::where(['status' => 1, 'delete_at' => 0, 'townid' => $townid])->order('read_total desc')->paginate(10);
            } else {
                $count = \app\api\model\Shop::where(['status' => 1, 'delete_at' => 0, 'townid' => $townid, 'type' => $type])->count();
                $info = \app\api\model\Shop::where(['status' => 1, 'delete_at' => 0, 'townid' => $townid, 'type' => $type])->order('read_total desc')->paginate(10);

            }
        }

        $list = [];
        foreach ($info as $key => $value) {

            $distance = getDistance($params['lat'], $params['lng'], $value['lat'], $value['lng']);
            $list[$key]['townName'] = db('town')->where(['delete_at' => 0, 'id' => $value['townid']])->value('name');
            $list[$key]['upic'] = \app\api\model\User::where(['delete_at' => 0, 'uuid' => $value['uuid']])->value('avatar');
            $list[$key]['uname'] = \app\api\model\User::where(['delete_at' => 0, 'uuid' => $value['uuid']])->value('nickname');
            $list[$key]['name'] = $value['name'];
            $list[$key]['id'] = $value['id'];
            $list[$key]['info'] = $value['info'];
            $list[$key]['address'] = $value['address'];
            $list[$key]['phone'] = $value['phone'];
            $list[$key]['fengmian'] = $value['fengmian'];
            $list[$key]['shop_time'] = $value['shop_time'];
            $list[$key]['read_total'] = $value['read_total'];
            $list[$key]['distance'] = $distance . '公里';

        }

        Utitls::sendJson(200, ['list' => $list, 'page' => ceil($count / 10)]);
    }

    //根据id获取详情
    public function detail()
    {
        $params = Request::param();
        $info = \app\api\model\Shop::where(['delete_at' => 0, 'id' => $params['id']])->find();

        $readTotal = $info['read_total'] + 1;
        $this->increateClick();
        //是否已收藏
        $collect_shops_ids = \app\api\model\User::where(['openid' => $params['openid'], 'delete_at' => 0])->value('collect_shops_ids');
        //是否包含这个资讯id
        $arr = json_decode($collect_shops_ids, true);
        if ($arr) {
            $iscollect = in_array($params['id'], $arr);//是否包含
        } else {
            $iscollect = 0;
        }
        if ($info) {
            $info['townName'] = db('town')->where(['delete_at' => 0, 'id' => $info['townid']])->value('name');
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
            $info['is_collect'] = $iscollect ? 1 : 0;
            unset($info['istuijian'], $info['update_at'], $info['create_at'], $info['delete_at']);
            Utitls::sendJson(200, $info);
        } else {
            Utitls::sendJson(500, null, '数据已被删除');
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
