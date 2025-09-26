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

class NewsComments
{


//    添加资讯
    public function add()

    {
//        $url = "http://www.xxxx.com";
//        $data = array(xxxxxxx);

        $params = Request::param();


        $tokenUrl = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.config('api.app_id').'&secret='.config('api.secret');
        $tokenres = json_decode(curlRequest($tokenUrl, 'GET'), true);
        $securityurl = 'https://api.weixin.qq.com/wxa/msg_sec_check?access_token='.$tokenres['access_token'];
        $data1 = json_encode(array('content'=>$params['content']),JSON_UNESCAPED_UNICODE);//第二个参数中文不转码
        $post_datas =json_decode(curls($securityurl, $data1),true) ;

        if($post_datas['errcode'] == 87014){//危险内容
            Utitls::sendJson(502,'','系统检测到您的内容存在风险，请重新输入内容');

        }else {//合法内容

            $create = \app\api\model\NewsComments::createComment($params);
            if($create==500){
                Utitls::sendJson(501,'自己不能回复自己的评论喔');
            }else{
                if ($create) {
                    $where = ['news_id'=>$params['news_id'],'delete_at' => 0];
                    $count = \app\api\model\NewsComments::where($where)->count();
                    $res = \app\api\model\NewsComments::where($where)->select();
                    $list = [];
                    foreach ($res as $key => $value) {
                        $list[$key]['u_name'] = \app\api\model\User::where(['delete_at' => 0, 'uuid' => $value['user_id']])->value('nickname');
                        if($value['p_uid']>0){
                            $list[$key]['p_name'] = \app\api\model\User::where(['delete_at' => 0, 'uuid' => $value['p_uid']])->value('nickname');
                        }
                        $list[$key]['p_uid'] = $value['p_uid'];
                        $list[$key]['user_id'] = $value['user_id'];
                        $list[$key]['id'] = $value['id'];
                        $list[$key]['content'] = $value['content'];
                    }

                    $uuid = \app\api\model\News::where(['id'=>$params['news_id'],'delete_at'=>0])->value('uuid');
                    if($uuid == \app\api\model\User::getUuid($params['openid'])){//自己评论自己的文章不得积分

                    }else{
                        //根据id查文章主人，查主人的积分+，查积分log，添加记录
                        $userpoint = \app\api\model\UsersWallet::where(['uuid'=>$uuid,'delete_at'=>0])->value('point');
                        \app\api\model\UsersWallet::where(['uuid'=>$uuid,'delete_at'=>0])->update(
                            [
                                'point'=>$userpoint+ \config('api.commentjifen')
                            ]
                        );
                    }

                    if ($res) {
                        Utitls::sendJson(200, ['list'=>$list,'count'=>$count]);
                    }
                    Utitls::sendJson(500);
                }
            }

        }

//        $securityurlres = curlRequest($securityurl, 'POST',["content"=>"完2347全dfji试3726测asad感3847知qwez到"]);

//        curl -d '{ "content":"hello world!" }' 'https://api.weixin.qq.com/wxa/msg_sec_check?access_token=ACCESS_TOKEN'

//        $response = json_decode($securityurlres, true);
//        dump($response);
//        exit();


    }

    // 查询
    public function index()
    {
        $params = Request::param();
        $where = ['news_id'=>$params['news_id'],'delete_at' => 0];
        $count = \app\api\model\NewsComments::where($where)->count();
        $res = \app\api\model\NewsComments::where($where)->select();
        $list = [];
        foreach ($res as $key => $value) {
            $list[$key]['u_name'] = \app\api\model\User::where(['delete_at' => 0, 'uuid' => $value['user_id']])->value('nickname');
            if($value['p_uid']>0){
                $list[$key]['p_name'] = \app\api\model\User::where(['delete_at' => 0, 'uuid' => $value['p_uid']])->value('nickname');
            }
            $list[$key]['p_uid'] = $value['p_uid'];
            $list[$key]['user_id'] = $value['user_id'];
            $list[$key]['id'] = $value['id'];
            $list[$key]['content'] = $value['content'];
        }
        if ($res) {
            Utitls::sendJson(200, ['list'=>$list,'count'=>$count]);
        }
        Utitls::sendJson(500);
    }

}
