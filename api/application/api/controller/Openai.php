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
use think\facade\Request;

class Openai
{
    public function getHomeList()
    {
        $check = config('api.xcxIsInCheck');
        $Info = [

            [
                'title' => '计算器',
                "name" => '生活助手神器',
                "pageName" => '/pages/tools/calculate',
                "color" => 'grey',
                "icon" => 'newsfill',
                "needVip"=> 0,
            ],
            [
                'title' => 'MD5',
                "name" => 'MD5的加密工具',
                "pageName" => '/pages/tools/md',
                "color" => 'grey',
                "icon" => 'newsfill',
                "needVip"=> 0,
            ],
            [
                'title' => '每日一言',
                "name" => '温暖每个心灵',
                "pageName" => '/pages/tools/daySay',
                "color" => 'grey',
                "icon" => 'newsfill',
                "needVip"=> 0,
            ],
        ];
        if($check){
            $Info = [
                [
                    'title' => '计算器',
                    "name" => '生活助手神器',
                    "pageName" => '/pages/tools/calculate',
                    "color" => 'green',
                    "icon" => 'newsfill',
                    "needVip"=> 0,
                ],
                [
                    'title' => 'MD5',
                    "name" => 'MD5的加密工具',
                    "pageName" => '/pages/tools/md',
                    "color" => 'grey',
                    "icon" => 'newsfill',
                    "needVip"=> 0,
                ],
                [
                    'title' => '每日一言',
                    "name" => '温暖每个心灵',
                    "pageName" => '/pages/tools/daySay',
                    "color" => 'blue',
                    "icon" => 'newsfill',
                    "needVip"=> 0,
                ]];
            Utitls::sendJson(200, [
                'data' => $Info,
            ], '成功');
        }
       $freeGPTInfo = [
            'title' => 'GPT Chat',
            "subtitle" => '普通版-与AI免费对话',
            "pageName" => '/pages/gpt/singleChat',
            "color" => 'gradual-blue',
            "icon" => config('api.base_url').config('api.xcxHomegptLogo'),
            "hidden" => 0,
        ];
        Utitls::sendJson(200, [
            'data' => $Info,
            'xcxHomegptpic'=>config('api.base_url').config('api.xcxHomegptpic')
            ,'freeGPTInfo'=>$freeGPTInfo
        ], '成功');
    }

    public function singleChat()
    {
        $params = Request::param();
        $messages = $params['messages'];
        if (strlen($messages) > config('api.chatNormalTextCountLimit') * 3) {
            Utitls::sendJson(500, '', '普通版支持' . config('api.chatNormalTextCountLimit') . '个汉字，更多请使用加强版！');
        }
        $openid = isset($params['openid']) ? $params['openid'] : '';
        $this->singerSendTxtToOpenAI($messages, $openid);
    }

    //单次询问
    function singerSendTxtToOpenAI($messages, $openid)
    {
        $user = \app\api\model\TtaUser::where(['openid'=>$openid])->find(); //这个算次数
        $isCanUse = $this->judgeIsCanUseContinue($user);
        if (!$isCanUse){
            Utitls::sendJson(505, '', config('api.ttaUseLimitTip'));
        }
        $openai_url = config('api.chatUrl');
        $msg_arr = array(["role" => "user", "content" => $messages]);
        $arr = ["messages" => $msg_arr];
        $body = json_encode($arr, JSON_UNESCAPED_UNICODE);
        $headers = [
            'api-key:' . config('api.chatKey'),
            'Content-Type:application/json',
        ];
        $data = $this->btnPost($openai_url, $body, $headers);

//        var_dump($data);
        if ($data['code'] == 200) {
            $this->updateUserChatCount($user);//更新chat次数
            $resultDict = json_decode($data['result'], true);
            $Info = $resultDict['choices'][0]['message']['content'];
            $prompt_tokens = $resultDict['usage']['prompt_tokens'];//提问
            $completion_tokens = $resultDict['usage']['completion_tokens'];//回答
            $total_token = $resultDict['usage']['total_tokens'];//总
            if ($Info) {
                db('open_history')->insert([
                    'uuid' => $openid,
                    'question' => $body,
                    'answer' => $Info,
                    'prompt_tokens' => $prompt_tokens,
                    'completion_tokens' => $completion_tokens,
                    'total_tokens' => $total_token,
                    'isSingle' => 1,
                    'create_at' => date('Y-m-d H:i:s', time())
                ]);
                $speech = 80;
                if (300*3 > strlen($Info) && strlen($Info)> 100 * 3) {
                    $speech = 50;
                }elseif (500*3 > strlen($Info) && strlen($Info) > 300 * 3){
                    $speech = 40;
                }elseif(500*3 < strlen($Info) ){
                    $speech = 30;
                }
                Utitls::sendJson(200, ['data' => $Info, 'token' => $total_token,'speech'=>$speech], '成功');
//                Utitls::sendJson(200, ['data' => $Info, 'token' => $total_token,'speech'=>'70'], '成功');
            } else {
                Utitls::sendJson(500, '', '暂无结果');
            }
        }
        Utitls::sendJson(500, '', '您的输入内容不合法或者接口出小差了，请稍后重试！');
    }

//    判断次数是否已达最大
    function judgeIsCanUseContinue($user){
        $isToday = isTodayWithTime($user['last_gpt_time']);
        if (!$isToday){
            return true;
        }
        if($user['isVip']>0 ){//会员，50次
            if ($user['today_chat_count'] >= config('api.chatVipUseCountLimit')){
                return false;
            }
            return true;
        }else{
            if ($user['today_chat_count'] >= config('api.chatNormalUseCountLimit')){
                return false;
            }
            return true;
        }
    }
//    更新用户chat次数
    function updateUserChatCount($user){
        if($user){
            $openid = $user['openid'];
            $lastOpenhistory =  db('open_history')->where(['uuid'=>$openid])->order('id desc')->limit(1)->find();
            if ($lastOpenhistory){
                $isToday = isTodayWithTime($lastOpenhistory['create_at']);
                if ($isToday){
                    \app\api\model\TtaUser::where(['openid'=>$openid])->update([
                        'today_chat_count'=>$user['today_chat_count']+1,
                        'last_gpt_time'=> date('Y-m-d H:i:s', time())
                    ]);
                }else{
                    \app\api\model\TtaUser::where(['openid'=>$openid])->update([
                        'today_chat_count'=>1,
                        'last_gpt_time'=> date('Y-m-d H:i:s', time())
                    ]);
                }
            }else{
                \app\api\model\TtaUser::where(['openid'=>$openid])->update([
                    'today_chat_count'=>1,
                    'last_gpt_time'=> date('Y-m-d H:i:s', time())
                ]);
            }
        }
    }
//    上下文
    public function contextChat()
    {
        $params = Request::param();
        $msg_arr = $params['messages'];
        $currentText = $params['currentText'];
        if (strlen($currentText) > config('api.chatVipTextCountLimit') * 3) {
            Utitls::sendJson(500, '', '目前仅支持' . config('api.chatVipTextCountLimit') . '个汉字输入！');
        }
        $openid = isset($params['openid']) ? $params['openid'] : '';

        $user = \app\api\model\TtaUser::where(['openid'=>$openid])->find(); //这个算次数
        $isCanUse = $this->judgeIsCanUseContinue($user);
        if (!$isCanUse){
            Utitls::sendJson(505, '', config('api.xcxOpenAllVipTip'));
        }
//       $msg_arr_str = $params['msg_arr_str'];

//       var_dump($messages);
        $openai_url = config('api.chatUrl');
//      $msg_arr = array(["role"=>"user","content"=>"谁是马云"]);
        $arr = ["messages" => $msg_arr];
        $body = json_encode($arr, JSON_UNESCAPED_UNICODE);
        $headers = [
            'api-key:' . config('api.chatKey'),
            'Content-Type:application/json',
        ];
        $data = $this->btnPost($openai_url, $body, $headers);

        if ($data['code'] == 200) {
            $this->updateUserChatCount($user);
            $resultDict = json_decode($data['result'], true);
            $Info = $resultDict['choices'][0]['message']['content'];
            $prompt_tokens = $resultDict['usage']['prompt_tokens'];//提问
            $completion_tokens = $resultDict['usage']['completion_tokens'];//回答
            $total_token = $resultDict['usage']['total_tokens'];//总
            if ($Info) {
                db('open_history')->insert([
                    'uuid' => $openid,
                    'question' => $body,
                    'answer' => $Info,
                    'prompt_tokens' => $prompt_tokens,
                    'completion_tokens' => $completion_tokens,
                    'total_tokens' => $total_token,
                    'isSingle' => 0,
                    'create_at' => date('Y-m-d H:i:s', time())
                ]);
                $speech = 80;
                if (300*3 > strlen($Info) && strlen($Info)> 100 * 3) {
                    $speech = 50;
                }elseif (500*3 > strlen($Info) && strlen($Info) > 300 * 3){
                    $speech = 40;
                }elseif(500*3 < strlen($Info) ){
                    $speech = 30;
                }
                Utitls::sendJson(200, ['data' => $Info, 'token' => $total_token,'speech'=>$speech], '成功');
            } else {
                Utitls::sendJson(500, '', '暂无结果');
            }
        }
        Utitls::sendJson(500, '', '您的输入内容不合法或者接口出小差了，请稍后重试！');
    }

    function insertOpenaiHistory($params)
    {
        db('open_history')->insert([
            'uuid' => $params['openid'],
            'question' => $params['question'],
            'answer' => $params['answer'],
            'prompt_tokens' => $params['prompt_tokens'],
            'completion_tokens' => $params['completion_tokens'],
            'total_token' => $params['total_token'],
            'create_at' => date('Y-m-d H:i:s', time())
        ]);
    }

    # url,请求路径，postdata 请求body参数，注意是json格式
    public static function btnPost($url, $postdata, $headers)
    {
        # 初始化一个curl会话
        $ch = curl_init();
        # 启用时会发送一个常规的POST请求
        curl_setopt($ch, CURLOPT_POST, 1);
        # 需要获取的URL地址
        curl_setopt($ch, CURLOPT_URL, $url);
        # 全部数据使用HTTP协议中的"POST"操作来发送。
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        # 将curl_exec()获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        # 一个用来设置HTTP请求头字段的数组
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        # curl_exec 执行一个cURL会话。
        $response = curl_exec($ch);
        ob_end_clean();
        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return array('code' => $return_code, 'result' => $response);
    }

}
