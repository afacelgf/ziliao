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
use PhpOffice\PhpWord\PhpWord;
use think\facade\Request;
use think\facade\Cache;
class Tta
{


   public function test(){
//       new \QRcode();
        $params = Request::param();
       $openid = $params['openid'];
//        echo config('api.base_url');
//        echo config('api.ttaTextLimit');
//       $messages = $params['messages'];
       $lastOpenhistory =  db('open_history')->where(['uuid'=>$openid])->order('id desc')->limit(1)->find();
//       $msg_arr_str = $params['msg_arr_str'];
//        if (strlen($text)>600*3){
//            $text =  $this->cut_str($params['text'],600);
//        }
       var_dump($lastOpenhistory);
//         $this->singerSendTxtToOpenAI($messages);
    }
    function getConfigData(){
        $params = Request::param();
        $openid = $params['openid'];
        $userInfo = \app\api\model\TtaUser::where(['openid' =>$openid])->find();
        if (!$userInfo) {
            Utitls::sendJson(500, '','用户不存在');
        }

        $xcxTtahorseRaceLamp = $userInfo['isVip']==1?'上线通知！！！年度最火【云野】隆重上线，欢迎大家体验使用。':'温馨提示：非会员字数限制为'.config('api.ttaNormalTextCount').'字,开通会员可输入'.config('api.ttaVipTextCount').'字的文字';

       $xcxIsInCheck = config('api.xcxIsInCheck');
       $data=[
           'chatNormalUseCountLimit' => config('api.chatNormalUseCountLimit'),//chat每天次数-普通用户
           'chatVipUseCountLimit' =>  config('api.chatVipUseCountLimit'),//chat每天次数-vip
           'chatNormalTextCountLimit' =>  config('api.chatNormalTextCountLimit'), //chat问题字数限制-普通用户
           'chatVipTextCountLimit' =>  config('api.chatVipTextCountLimit'),//chat问题字数限制-vip
           'chatContextTip' => "全新升级4.0模型，赶紧输入内容进行对话吧！\n（pro版单次支持". config('api.chatVipTextCountLimit')."字输入）",//chat问题字数限制-vip
           'chatContextNavTitle' => $xcxIsInCheck==1?config('api.chatContextNavTitleInCheck'):config('api.chatContextNavTitle'),
           'chatSingleNavTitle' => $xcxIsInCheck==1?config('api.chatSingleNavTitleInCheck'):config('api.chatSingleNavTitle'),
           'chatColorList' => config('api.chatColorList'),
           'ttaGanqingChineseArr' =>  config('api.ttaGanqingChineseArr'),//配音感情
           'ttaganqingEnglishArr' =>  config('api.ttaganqingEnglishArr'),//配音感情
           'ttaSpeeckName' =>  config('api.ttaSpeeckName'),//默认配音员
           'ttaSpeeckIcon' =>  config('api.ttaSpeeckIcon'),//默认配音员icon
           'ttaSpeeckRealName' =>  config('api.ttaSpeeckRealName'),//默认名称

           'ttaNormalUseCountLimit' =>  config('api.ttaNormalUseCountLimit'),//tta每天使用次数-普通用户
           'ttaVipUseCountLimit' =>  config('api.ttaVipUseCountLimit'),//tta每天使用次数-vip
           'ttaNormalTextCount' =>  config('api.ttaNormalTextCount'), //tta配音字数限制
           'ttaVipTextCount' =>  config('api.ttaVipTextCount'), //tta配音vip字数限制
           'ttaNormalOutputFileCount' =>  config('api.ttaNormalOutputFileCount'), //tta配音导出文件限制
           'ttaVipOutputFileCount' =>  config('api.ttaVipOutputFileCount'), //tta配音vip导出文件限制
           'xcxTtahorseRaceLamp'=>$xcxIsInCheck==0?$xcxTtahorseRaceLamp:"温馨提示：请输入字符后点击一键生成",

           'kefu' => config('api.base_url').'ttaSource/icon/kefu.png', //客服
           'guanyu' =>  config('api.xcxAbout'), //关于
           'xcxHomehorseRaceLamp' => $xcxIsInCheck==0? config('api.xcxHomehorseRaceLamp'):config('api.xcxHomehorseRaceLampInCheck'), //openai首页跑马灯
           'xcxNewUserIn' =>  config('api.xcxNewUserIn'), //新用户欢迎
           'gongzhonghao' =>  config('api.base_url').'ttaSource/icon/gongzhonghao.png', //公众号
           'xcxHomeSwiper'=> $xcxIsInCheck==0?config('api.xcxHomeSwiper'):config('api.xcxHomeSwiperInCheck'),//首页轮播图
           'xcxOpenVipTip'=>config('api.xcxOpenVipTip'),//首页开vip提示语
           'xcxMineOpenVipTip'=>config('api.xcxMineOpenVipTip'), //我的页面vip弹框
           'chatAnswerShowSpeed'=>config('api.chatAnswerShowSpeed'),//gpt结果逐字展示的速度
           'xcxSharedRule'=>config('api.xcxSharedRule'),//邀请等级说明数组
           'xcxVipWxts'=>config('api.xcxVipWxts'),////vip页面的温馨提示
           'xcxInviteTip'=>config('api.xcxInviteTip'),////邀请页温馨提示
           'xcxIsInCheck'=>config('api.xcxIsInCheck'), ///是否正在审核
           'xcxMineFourList'=> $xcxIsInCheck==0?config('api.xcxMineFourList'):config('api.xcxMineFourListInCheck'), ///是否正在审核
           'xcxqrcodeTip'=> $xcxIsInCheck==0?config('api.xcxqrcodeTip'):config('api.xcxqrcodeTipInCheck'), ///是否正在审核
           'xcxMineProblemList'=> $xcxIsInCheck==0?config('api.xcxMineProblemList'):config('api.xcxMineProblemListInCheck'), ///是否正在审核
       ];
        Utitls::sendJson(200, $data,'成功');
    }


//    获取当前一言
    function getTodaySay(){
      $day =  date("d");

      $info = db('tta_daysay')->where(['day'=>$day])->find();
//        var_dump($info);
        if (!$info) {
            Utitls::sendJson(500, '','用户不存在');
        }
        Utitls::sendJson(200, $info['content'],'成功');
    }

//    public $mp3FilePath  = "";
    function textToAudio(){
        $params = Request::param();
        $openid = isset($params['openid']) ? $params['openid'] : '';
        $userInfo = \app\api\model\TtaUser::where(['openid' =>$openid])->find();
        if (!$userInfo) {
            Utitls::sendJson(500, '','用户不存在');
        }
        $text = str_replace(' ','',$params['text']);//去除空格
        $text = preg_replace("/\s+/", "", $text);
        $text = filterEmoji($text);//去除表情
        if($text==null){
            Utitls::sendJson(500, '','内容不能为空');
        }
        //次数是否可用
        $isCanUse = $this->judgeIsCanUseContinue($userInfo);
        if (!$isCanUse){
            Utitls::sendJson(505, '', config('api.ttaUseLimitTip'));
        }

        if (strlen($text)>config('api.ttaNormalTextCount')*3){ //200字符开始校验会员,普通用户
            if ($userInfo['isVip'] == 0  || getTimeStrampWithDate($userInfo['vipEndTime']) - time() < 0){ //不是vip
                Utitls::sendJson(500, '',config('api.ttaNormalTextCount').'字以上需要会员资格');
            }
        }
        if (strlen($text)>config('api.ttaVipTextCount')*3){ //最多600字符-vip用户限制字数
            $text = cut_str($params['text'],config('api.ttaVipTextCount'));
        }
       //ip间隔逻辑
        $ip = getiP();
        $ipsRe = db('tta_ips')->where(['ip'=>$ip])->find();
        $requestTime = time();
        if ($ipsRe){
            if ($requestTime - $ipsRe['lastTimeStram'] >config('api.ttaIpLimitTimes')){ //同一个ip间隔超过10s
                db('tta_ips')->where(['ip'=>$ip])->update([
                    'lastDate'=>date('Y-m-d H:i:s',$requestTime),
                    'lastTimeStram'=>$requestTime,
                    'totalCount'=>$ipsRe['totalCount']+1
                ]);
            }else{
                Utitls::sendJson(500, '','请求过于频繁，请间隔'.config('api.ttaIpLimitTimes').'s左右');
            }
        }else{
            db('tta_ips')->insert([
                'ip'=>$ip,
                'lastTimeStram'=>$requestTime,
                'lastDate'=>date('Y-m-d H:i:s',$requestTime),
                'totalCount'=>1
            ]);
        }

        $ipsResult = db('tta_ips')->where(['ip'=>$ip])->find();
        $now = new \DateTime();
        $currenttime =  $now->format('YmdHi');
        $fileNamemp3 = "txtToAudio/".$currenttime.'_'.md5(time()).".mp3";
        $this->logTimeWithTip('token开始',$fileNamemp3,$text,$openid);
        $tokenStr = $this->getCacheToken();
//        var_dump($tokenStr);
//        $this->logTimeWithTip('token结束',$fileNamemp3,$text);
        if(!$tokenStr){
            Utitls::sendJson(500, '','token接口出错了');
        }
//        $this->logTimeWithTip('token结束-',$fileNamemp3);
         $headers1 = [
            'Authorization:Bearer ' .$tokenStr ,
            'Content-Type:application/ssml+xml',
//            'X-Microsoft-OutputFormat:riff-24khz-16bit-mono-pcm',
//            'X-Microsoft-OutputFormat:riff-8khz-8bit-mono-alaw',
//            'X-Microsoft-OutputFormat:riff-8khz-16bit-mono-pcm',
            'X-Microsoft-OutputFormat:riff-16khz-16bit-mono-pcm',
            'User-Agent:txtToAudioApi'
        ];

        $speekPersonName = $params['speekPerson'];
        if ($speekPersonName==null){
            $speekPersonName = config('api.ttaDefaultSpeeckPerson');
        }
//        $style = isset($params['style']);
        $haveFileType = isset($params['fileType']);
        $express = isset($params['express']);
        $speed = $params['speed'];
        $this->logTimeWithTip('语音接口开始',$fileNamemp3);
        $xmlstring =
            "<speak version='1.0' xmlns='http://www.w3.org/2001/10/synthesis' xmlns:mstts='https://www.w3.org/2001/mstts' xml:lang='zh-cn'>".
                "<voice xml:lang='zh-cn' name='".$speekPersonName."'".">"
                    ."<prosody rate='".$speed."'".">" //速度
                        ."<mstts:express-as style='".$express."'".">" //类型
//                          ."<mstts:express-as role='YoungAdultFemale' style='calm'>"
                            .$text.
                        "</mstts:express-as>".
                    "</prosody>".
                "</voice>
            </speak>";
        $fetch_data_url = "https://".config('api.ttaArea').".tts.speech.microsoft.com/cognitiveservices/v1";
        $audioParams = $xmlstring;
        $data =  $this->post_json_data($fetch_data_url,$audioParams,$headers1);
        if ($data['code']==200){
            $this->updateUserChatCount($userInfo);//更新用户表tta次数
            $this->logTimeWithTip('语音接口结束',$fileNamemp3);
            $file = fopen($fileNamemp3,"w");
            $wr=  fwrite($file,$data['result']);
            fclose($file);
            if ($haveFileType){ //返回MP4
                $fileType = $params['fileType'];
                $fileNameMp4 = str_replace('mp3','mp4',$fileNamemp3);
                if ($fileType == 'mp4'){
//                    $this->logTimeWithTip('文件转换开始',$fileNamemp3);
                    $fileConverResult = $this->changeMp3ToMp41($fileNamemp3,$fileNameMp4,'ttaSource/logo.png');
                    if ($fileConverResult){
                        db('tta_ips')->where(['ip'=>$ip])->update([
                            'successCount'=>$ipsResult['successCount']+1
                        ]);
                        Utitls::sendJson(200, ['filename'=>$fileNameMp4,'fileConverResult'=>$fileConverResult],'合成成功');
                    }else{
                        Utitls::sendJson(500, '','转换出错了');
                    }
                }
            }
            db('tta_ips')->where(['ip'=>$ip])->update([
                'successCount'=>$ipsResult['successCount']+1
            ]);
           Utitls::sendJson(200, ['filename'=>$fileNamemp3],'合成成功');
        }
        Utitls::sendJson(500, $data,'接口出错了');
    }

    //    判断次数是否已达最大
    function judgeIsCanUseContinue($user){
        $isToday = isTodayWithTime($user['last_tta_time']);
        if (!$isToday){
            return true;
        }
        if($user['isVip'] > 0 ){//会员，50次
            if ($user['today_tta_count'] >= config('api.ttaVipUseCountLimit')){
                return false;
            }
            return true;
        }else{
            if ($user['today_tta_count'] >= config('api.ttaNormalUseCountLimit')){
                return false;
            }
            return true;
        }
    }
//    更新用户chat次数
    function updateUserChatCount($user){
        if($user){
            $openid = $user['openid'];
            $lastOpenhistory =  db('tta_time')->where(['uuid'=>$openid])->order('id desc')->limit(1)->find();
            if ($lastOpenhistory){
                $isToday = isTodayWithTime($lastOpenhistory['create_at']);
                if ($isToday){
                    \app\api\model\TtaUser::where(['openid'=>$openid])->update([
                        'today_tta_count'=>$user['today_tta_count']+1,
                        'last_tta_time'=> date('Y-m-d H:i:s', time())
                    ]);
                }else{
                    \app\api\model\TtaUser::where(['openid'=>$openid])->update([
                        'today_tta_count'=>1,
                        'last_tta_time'=> date('Y-m-d H:i:s', time())
                    ]);
                }
            }else{
                \app\api\model\TtaUser::where(['openid'=>$openid])->update([
                    'today_tta_count'=>1,
                    'last_tta_time'=> date('Y-m-d H:i:s', time())
                ]);
            }
        }
    }

    public function getToken(){
        $token = $this->getCacheToken();
        if ($token){
            Utitls::sendJson(200, '','获取token成功');
        }
        Utitls::sendJson(500, '','获取token失败');
    }

   public function getCacheToken(){
//        $key = '9a6d14c32ebb4061b03fa0524cc0108d';eastasia
//        $key = 'c1d6138d8ada461fb7d6d1f700a210f8'; southeastasia
//        bdfd583f9d8449a9b608c26abcb4b73e   japanwest
        if (Cache::get('ttaToken')) {//判断是否存在
//            var_dump('有');
//            $this->logTimeWithTip('有token');
            $tokenStr = Cache::get('ttaToken');  //存在就读缓存
        } else {
            $token_headers = [
                'Ocp-Apim-Subscription-Key:'.config('api.ttaKey')
            ];
            $fetch_token_url = "https://".config('api.ttaArea').".api.cognitive.microsoft.com/sts/v1.0/issuetoken";
            $access_token =  $this->post_json_data($fetch_token_url,'',$token_headers);
            if(!$access_token){
                return '';
            }
            $tokenStr = $access_token['result'];
//            $this->logTimeWithTip('无token');
            Cache::set('ttaToken', $tokenStr,9.5*60);  //不存在就设置缓存
        }
        return $tokenStr;
    }

    //  获取配音员列表
    public function getppyList(){
        $params = Request::param();
        $where = ['status' => 1];
        if (config('api.xcxIsInCheck')==1){
            $where = ['isVip'=>0,'status' => 1] ;
        }
        $arr = db('tta_pyy')->where($where)->select();
        $list = [];
        foreach ($arr as $key => $value) {
            $list[$key]['id'] = $value['id'];
            $list[$key]['name'] = $value['name'];
            $list[$key]['realName'] = $value['realName'];
            $list[$key]['useCount'] = $value['useCount'];
            $list[$key]['desc'] = $value['desc'];
            $list[$key]['icon'] = config('api.base_url').'ttaSource/icon/pyyicon/'. $value['icon'];
            $list[$key]['isVip'] = $value['isVip'];
            $list[$key]['shitingUrl'] = $value['shitingUrl'];
            $list[$key]['biaoqian'] = $value['biaoqian'];
        }
        if ($list) {
            Utitls::sendJson(200, $list);
        }
        Utitls::sendJson(500);
    }

    //删除前一天的音频视频文件
    function dealLastDayFile(){
        $path = 'txtToAudio';
        $result = $this->scanFile($path);
        foreach ($result as $item){
            $filePath = $path.'/'.$item;
            if (file_exists($filePath)){
                $timeStamp = filectime($filePath);
               $day = floor((time()-$timeStamp)/86400);
               if ($day>=1){//删除一天前的文件
                   echo '相差天数='.$day.'='.'删除文件='.$filePath.'=';
                    unlink($filePath);
               }
            }else{
                echo '文件不存在';
            }
        }
//        var_dump($result);
    }

    function scanFile($path) {
        global $result;
        $files = scandir($path);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                if (is_dir($path . '/' . $file)) {
                    scanFile($path . '/' . $file);
                } else {
                    $result[] = basename($file);
                }
            }
        }
        return $result;
    }

    /*
     * post 发送JSON 格式数据
     * @param $url string URL
     * @param $data_string string 请求的具体内容
     * @return array
     *      code 状态码
     *      result 返回结果
     */
    function post_json_data($url, $data_string,$headers) {
        //初始化
        $ch = curl_init();
        //设置post方式提交
        curl_setopt($ch, CURLOPT_POST, 1);
        //设置抓取的url
        curl_setopt($ch, CURLOPT_URL, $url);
        //设置post数据
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

        //设置头文件的信息作为数据流输出
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        ob_start();
        //执行命令
        curl_exec($ch);
        $return_content = ob_get_contents();
        ob_end_clean();
        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return array('code'=>$return_code, 'result'=>$return_content);
    }

    /**
     * 视频加水印 $file = ‪D:/source.mp4,$outPutFile=d:/compression.mp4
     * $position = (leftTop,rightTop,leftBottom,rightBottom,middle);左上角,右上角,左下角,右下角
     * $xOffset x坐标的偏移量，$yOffset x坐标的偏移量
     * ffmpeg -i E:\ffmpeg\bin\source_cut_zip.mp4 -i E:\ffmpeg\bin\logo.png -filter_complex "overlay=30:30" E:\ffmpeg\bin\source_cut_zip_logo.mp4
     */
    function WaterVideo($sourceFile='',$outPutFile='',$logoFile="",$position="leftTop",$xOffset=30,$yOffset=30){
        if (!file_exists($sourceFile)||!is_file($logoFile)){
            return false;//"file not exist!";
        }
        $outPut   = $returnVar = "";
        switch($position){
            case 'leftTop':
                $position = "$xOffset:$yOffset";break;
            case 'rightTop':
                $position = "main_w-overlay_w-$xOffset:$yOffset";break;
            case 'leftBottom':
                $position = "$xOffset:main_h-overlay_h-$yOffset";break;
            case 'rightBottom':
                $position = "main_w-overlay_w-$xOffset:main_h-overlay_h-$yOffset";break;
            case 'middle':
                $position = "overlay=main_w/2-overlay_w/2:main_h/2-overlay_h/2";break;
            default:
                $position = "$xOffset:$yOffset";
        }
        $H264 = PHP_OS!='WINNT'?" -vcodec libx264 ":' ';
        exec($this->sowarePath . "ffmpeg -i $sourceFile -i $logoFile -filter_complex \"overlay=$position\" $H264 $outPutFile",$outPut,$returnVar);
        if($returnVar==1){
            return false;//命令执行失败
        }else{
            return $outPutFile;//命令执行成功,返回路径
        }
    }

    public function changeMp3ToMp41($sourceFile, $outPutFile, $logoFile)
    {
        if (!file_exists($sourceFile)||!is_file($logoFile)){
            return false;//"file not exist!";
        }
        $outPut   = $ret = "";
        $timeR = $this->getTime($sourceFile);
//        var_dump($timeR); -t $timeR -b:a 24k -vcodec libx264
        $shell = "ffmpeg -loop 1 -y -i $logoFile -i $sourceFile  -shortest -r 5 -t $timeR -s 320*240 -vcodec libx264 -pix_fmt yuvj420p  $outPutFile";
//        $shell = "ffmpeg -loop 1 -y  -i $logoFile -i $sourceFile  -shortest -r 5 -t $timeR -s 320*240 -vcodec libx264  $outPutFile";
        exec($shell, $output, $ret);

        $this->logTimeWithTip('格式转换结束',$sourceFile);
//        var_dump('转换结束 -'.$ret);
        if($ret==1){
            return false;//命令执行失败
        }else{
            return $outPutFile;//命令执行成功,返回路径
        }
    }

    function logTimeWithTip($desc,$fileName,$text='',$openid=''){
       $res = db('tta_time')->where(['fileName'=>$fileName])->find();
       if ($res){
           $lastDesc = $res['desc'].$desc.date('H:i:s',time()).'|';
           $upRes =  db('tta_time')->where(['fileName'=>$fileName])->update(
               ['desc'=>$lastDesc]
           );
       }else{
           $arr = db('tta_time')->insert(
               [
                   'create_at' => date('Y-m-d H:i:s', time()),
                   'uuid'=>$openid,
                   'desc'=>$desc.date('H:i:s',time()).'|','fileName'=>$fileName,'text'=>$text,'textLength'=>strlen($text)/3]
           );
       }
    }

    /**
     * Notes:获取视频时长
     * User: li
     * Date: 2019/12/31
     * Time: 13:39
     * @param $file 文件路径
     * @return array
     */
    protected function getTime($file){
        $vtime = exec("ffmpeg -i ".$file." 2>&1 | grep 'Duration' | cut -d ' ' -f 4 | sed s/,//");//总长度
        $ctime = date("Y-m-d H:i:s",filectime($file));//创建时间
        return $vtime;
    }

}
