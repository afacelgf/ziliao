<?php
/**
 * Created by PhpStorm.
 * User: Xs___
 * Date: 2019/11/27
 * Time: 22:56:21
 * Desc:
 */

namespace app\api\controller;

use app\api\model\Config;
use app\api\model\NewsComments;
use app\api\model\NewsType;
use app\Utitls;
use Cassandra\Date;
use PhpOffice\PhpWord\PhpWord;
use RuntimeException;
use think\facade\Request;
//include_once "../../../vendor/autoload.php";
use PhpOffice\PhpWord\TemplateProcessor;

class Index
{
    /**
     * map 转 xml
     * @param $map
     * @return string
     */
//    private function MapConvertXML($map) {
//        if(!is_array($map) || count($map) <= 0) {
//            throw new RuntimeException('数据异常!');
//        }
//        $XML = '<xml>';
//        foreach ($map as $key=>$val) {
//            if (is_numeric($val)){
//                $XML.='<'.$key.'>'.$val.'</'.$key.'>';
//            }else{
//                $XML.='<'.$key.'><![CDATA['.$val.']]></'.$key.'>';
//            }
//        }
//        $XML.='</xml>';
//        return $XML;
//    }

    function testTextToAudio(){
//        $map = [
//            ''
//        ];
//        $this->MapConvertXML();
//        exit();
        $token_headers = [
            'Ocp-Apim-Subscription-Key:9a6d14c32ebb4061b03fa0524cc0108d'
        ];
        $fetch_token_url = "https://eastasia.api.cognitive.microsoft.com/sts/v1.0/issuetoken";
        $access_token =  $this->post_json_data($fetch_token_url,'',$token_headers);
        if(!$access_token){
            Utitls::sendJson(500, '','token接口出错了');
        }
        $tokenStr = $access_token['result'];
        $headers1 = [
            'Authorization:Bearer ' .$tokenStr ,
            'Content-Type:application/ssml+xml',
            'X-Microsoft-OutputFormat:riff-24khz-16bit-mono-pcm',
            'User-Agent:txtToAudioApi'
        ];
        $params = Request::param();
        $text = $params['text'];
        $speekPersonName = $params['speekPerson'];
        if ($speekPersonName==null){
            $speekPersonName = 'zh-CN-YunxiNeural';
        }
        if($text==null){
            Utitls::sendJson(500, '','内容不能为空');
        }
//        $style = $params['style'];
        $speed = $params['speed'];
//        xml:lang='en-us'
        $xmlstring =
            "<speak version='1.0' xmlns='http://www.w3.org/2001/10/synthesis' xmlns:mstts='https://www.w3.org/2001/mstts' xml:lang='zh-CN'>".
                "<voice xml:lang='en-US' name='".$speekPersonName."'".">"
                    ."<prosody rate='".$speed."'".">" //速度

//                        ."<mstts:express-as style='".$style."'".">" //类型
//                          ."<mstts:express-as role='YoungAdultFemale' style='calm'>"
                            .$text.
//                        "</mstts:express-as>".
                    "</prosody>".
                "</voice>
            </speak>";
//       $xmlstring =  file_get_contents('/www/wwwroot/hjb.jxpyq666.com/hjbxcx/application/api/controller/voiceInfo.xml');
//       var_dump(json_decode($xml));
//        var_dump($xmlstring);
//       exit();
//        var_dump('hao ren ');
        $fetch_data_url = "https://eastasia.tts.speech.microsoft.com/cognitiveservices/v1";
        $audioParams = $xmlstring;
        $data =  $this->post_json_data($fetch_data_url,$audioParams,$headers1);
        if ($data['code']==200){
            $fileName = "txtToAudio/".md5(time()).".mp3";
            $file = fopen($fileName,"w");
            $wr=  fwrite($file,$data['result']);
            fclose($file);
            Utitls::sendJson(200, ['filename'=>$fileName],'合成成功');
        }
        Utitls::sendJson(500, '','接口出错了');
    }

    public function test(){
        $tmp = new PhpWord();
//        $tmp=new \PhpOffice\PhpWord\TemplateProcessor('tmp.docx');//打开模板
        $tmp->setValue('name','李四');//替换变量name
        $tmp->setValue('mobile','18888888888');//替换变量mobile
        $tmp->saveAs('简历.docx');//另存为
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

    /* 视频压缩 */
//    public function compressVideo($file, $file_name) {
//        $file_content = file_get_contents($file);
//        $compress_path = PUBLIC_PATH;
//        $compress_file = $compress_path . $file_name . '.mp4';
//        $compress_after_file = $compress_path . $file_name . '_compress.mp4';
//        try{
//            file_put_contents($compress_file, $file_content);
//            $video_info;
//            exec(FFMPEG_PATH . "ffmpeg -i {$compress_file} 2>&1", $video_info);
//            $video_info = implode(' ', $video_info);
//            $bitrate = '';    // 比特率
//            $resolution = ''; // 分辨率
//            if(preg_match("/Duration: (.*?), start: (.*?), bitrate: (\d*) kb\/s/", $video_info, $match)) {
//                $bitrate = $match[3];
//            }
//            if(preg_match("/Video: (.*?), (.*?), (.*?)[,\s]/", $video_info, $match)) {
//                $resolution = $match[3];
//            }
//            $file_size = filesize($compress_file);
//            $file_size = intval($file_size / 1048576);
//            if(empty($bitrate)) throwErr('找不到比特率信息');
//            if(empty($resolution)) throwErr('找不到分辨率信息');
//
//            if($file_size < 10) throwErr('视频大小不足10M，不需要压缩', null, 1100);
//
//            $resolution = explode('x', $resolution);
//            $bitrate_update = '';
//            $resolution_width_update = '';
//            $resolution_height_update = '';
//            $bitrate_update = $this->getVideoCompressBitrate($bitrate);
//            $resolution_percent = 0;
//            if($resolution[0] > $resolution[1]) {
//                if($resolution[1] > 320) {
//                    $resolution_percent = $resolution[1] <= 520 ? 0.8 : 0.5;
//                }
//            }else {
//                if($resolution[0] > 320) {
//                    $resolution_percent = $resolution[0] <= 520 ? 0.8 : 0.5;
//                }
//            }
//            if($resolution_percent > 0) {
//                $resolution_width_update = intval($resolution[0] * $resolution_percent);
//                $resolution_height_update = intval($resolution[1] * $resolution_percent);
//            }
//            if(empty($bitrate_update) && empty($resolution_width_update)) throwErr('比特率和分辨率同时不满足压缩条件', null, 1100);
//
//            $compress_bitrate = '';
//            $compress_resolution = '';
//            if(!empty($bitrate_update)) {
//                $compress_bitrate = "-b:v {$bitrate_update}k";
//            }
//            if(!empty($resolution_width_update)) {
//                $compress_resolution = "-s {$resolution_width_update}x{$resolution_height_update}";
//            }
//            $compress_exec = FFMPEG_PATH . "ffmpeg -i {$compress_file} %s% %v% {$compress_after_file}";
//            $compress_exec = str_replace(array('%s%', '%v%'), array($compress_resolution, $compress_bitrate), $compress_exec);
//            exec($compress_exec);
//            unlink($compress_file);
//
//            return array('compress_file' => $compress_after_file);
//        }catch(\Exception $e) {
//            unlink($compress_file);
//            return array();
//        }
//    }
//
//    /* 获取视频压缩比特率 */
//    public function getVideoCompressBitrate($bitrate, $query_count = 0) {
//        $bitrate_update = '';
//        if($bitrate >= 700) {
//            if($bitrate <= 1000) {
//                $bitrate_update = intval($bitrate * 0.8);
//            }else {
//                $bitrate_update = intval($bitrate * 0.5);
//            }
//        }
//        if(empty($bitrate_update)) {
//            return $query_count == 0 ? $bitrate_update : $bitrate;
//        }else {
//            return $this->getVideoCompressBitrate($bitrate_update, ++$query_count);
//        }
//    }


    public function uploads()
    {
        $files = request()->file('images');
        if ($files) {
            $path = app()->getRootPath() . '/public/uploads/';
            $info = $files->move($path);
            if ($info) {
                $data['url'] = '/uploads/' . str_replace('\\', '/', $info->getSaveName());
                $data['show'] = config('api.base_url') . '/uploads/' . str_replace('\\', '/', $info->getSaveName());
                Utitls::sendJson(200, $data);
            } else {
                Utitls::sendJson(500);
            }
        } else {
            Utitls::sendJson(500);
        }
    }

    /**
     * 视频上传
     */
    public function video_add(){
        if (request()->isPost()){
            $video = $_FILES['video'];
            $res = upload_file($video);


            return $res;
        }
    }


//    汽车名片的价格
    public function carCardVIPMoney()
    {
        $files = ['1' => \config('api.mingpian30'), '2' => \config('api.mingpian60'), '3' => \config('api.mingpian90'), '6'=>\config('api.mingpian180')];
        Utitls::sendJson(200, $files);
    }
    //    店铺入驻信息
    public function ruzhuinfo()
    {
        $files = ['tip' => '店铺免费入驻','show' => 1,'pic'=>'https://www.haojiabang520.com/uploads/basepic/shopruzhubg.jpg'];
        Utitls::sendJson(200, $files);
    }

    //    店铺入驻信息'duijifen' =>100, //兑换积分数/1元
    //    'duijifenmax' =>10000, //单次最大的兑换数
    //    managesAccountOpenid管理员账号的openid
    public function systemTips()
    {
        $files = ['duijifen' => config('api.duijifen'),
            'duijifenmax' => config('api.duijifenmax'),
            'getjifentip'=>config('api.getjifentip'),
            'shopRuzhuTip'=>config('api.shopRuzhuTip'),
            'managesAccountOpenid'=>config('api.managesAccountOpenid'),
            'morenlat'=>config('api.morenlat'),
            'morenlng'=>config('api.morenlng'),

        ];
        Utitls::sendJson(200, $files);
    }

    //    祝福语获取
    public function getzhufu()
    {
        $params = Request::param();
        $zhufus = db('zhufuyu')->where(['delete_at' => 0])->order('sort desc')->select();

        $list = [];
        foreach ($zhufus as $key => $value) {
            $list[$key]['id'] = $value['id'];
            $list[$key]['content'] = $value['content'];
            $jieri_type = db('jieri_type')->where(['id' => $value['jieri']])->find();
            $list[$key]['jieri'] = $jieri_type['name'];
        }

        if ($list) {
            Utitls::sendJson(200, $list);
        } else {
            Utitls::sendJson(500);
        }
    }


//    随机一条祝福语
    public function suijigetzhufu()
    {
        $params = Request::param();
        $arr = db('zhufuyu')->where(['delete_at' => 0])->order('id asc')->select();
        $list = [];
        foreach ($arr as $key => $value) {
            $list[$key]['id'] = $value['id'];
            $list[$key]['content'] = $value['content'];
            $list[$key]['type'] = $value['jieri'];
        }
//        array_rand返回的是随机数组的下标
        $info = $list[array_rand($list)];
        if ($info) {
            Utitls::sendJson(200, $info);
        }
        Utitls::sendJson(500);

    }

//    根据id获取祝福语
    public function getzhufubyid()
    {
        $params = Request::param();
        $info = db('zhufuyu')->where(['id' => $params['id'], 'delete_at' => 0])->find();
        $map['id'] = $info['id'];
        $map['content'] = $info['content'];
        if ($info) {
            Utitls::sendJson(200, $map);
        }
        Utitls::sendJson(500);

    }

    //  获取乡镇信息
    public function getTownList()
    {
        $params = Request::param();
        $arr = db('town')->where( ['delete_at' => 0])->select();
        $list = [];
        foreach ($arr as $key => $value) {
            $list[$key]['id'] = $value['id'];
            $list[$key]['name'] = $value['name'];
            $list[$key]['desc'] = $value['desc'];
        }
        if ($list) {
            Utitls::sendJson(200, $list);
        }
        Utitls::sendJson(500);
    }


//    评论列表
    public function getCommentList()
    {
        $params = Request::param();
        $res = $this->getCommentListno($params['news_id']);
//        dump($res);
        if ($res) {
            Utitls::sendJson(200, $res);
        }
        Utitls::sendJson(500);
    }


//    评论列表
    public function getCommentListno($news_id, $comment_id = 0, &$result = array())
    {

//        $params = Request::param();
        if (empty($news_id)) {
            return array();
        }
        $where = ["news_id" => $news_id, 'comment_id' => $comment_id];

        $res = NewsComments::where($where)->select();
//        dump($res);
//        exit();
        if (empty($res)) {
            return array();
        }

        foreach ($res as $cm) {

            $thisArr = &$result[];
            $cm['child'] = $this->getCommentListno($news_id, $cm['id'], $thisArr);
            $thisArr = $cm;
        }
        return $result;
//        print_r('=====');
//        dump($result);
//


    }


}
