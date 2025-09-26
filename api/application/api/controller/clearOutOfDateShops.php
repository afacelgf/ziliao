<?php
namespace app\api\controller;

use think\console\Command;
use think\console\Input;
use think\console\Output;

//已配置了30分钟执行,作用：清除已过期的店铺

class clearOutOfDateShops extends Command
{
    protected function configure(){
        $this->setName('clearOutOfDateShops')->setDescription("计划任务clearOutOfDateShops");
    }

    //调用SendMessage 这个类时,会自动运行execute方法
    protected function execute(Input $input, Output $output){
        $output->writeln('清除已过期的店铺'.date("Y-m-d H:i:s",time()) );

        $info= $this->clear();//

        $output->writeln('清除结束'.date("Y-m-d H:i:s",time()).'---'.$info);
    }

    public function clear(){

        //status 0审核中，1审核通过，2拒绝
        $shopArr = \app\api\model\Shop::where(['delete_at'=>0,'status'=>1])->select();

        if($shopArr) {
            foreach ($shopArr as $key => $value) {
                \app\api\model\Shop::where(['id'=>$value['id'],'delete_at'=>0,'status'=>1])
                    ->whereIn('vipEndTime','<=',time())->update(
                    ['status'=>3]
                );
                $qsopenid = \app\api\model\User::getOpenid($value['uuid']);
                //给用户发送待审核订阅消息
                $data = [
                    "thing1" => [
                        "value"=> '店铺入驻已过期'
                    ],
                    "time3" => [
                        "value"=> date('Y-m-d H:i:s',time())
                    ],
                    "phone_number5" => [
                        "value"=> $value['phone']
                    ],
                    "phrase4" => [
                        "value"=> "到期了"
                    ]
                ];
                $page = '/pages/mine/ruzhu/managed';
                $kuaidi =  new Kuaidi();
                $ret= $kuaidi->sendKuaidiDaiSureMessage($qsopenid,'A3MHxijurk_7PxH3QLMhAcmvzQtN0eZ3PUV3e2mUhmU',$page,$data);

            }
        }
        return '没有要确认的订单佣金';
    }


    /**
     * [企业付款到零钱]
     * @param  [type] $amount     [发送的金额（分）目前发送金额不能少于1元]
     * @param  [type] $re_openid  [发送人的 openid]
     * @param  string $desc       [企业付款描述信息 (必填)]
     * @param  string $check_name [收款用户姓名 (选填)]
     * @return [type]             [description]
     */
    public function sendMoney($amount,$re_openid,$desc='提现',$check_name=''){

        $total_amount = (100) * $amount;
        $data=array(
            'mch_appid'=>config('api.app_id'),//商户账号appid
            'mchid'=> config('api.mch_id'),//商户号
            'nonce_str'=>$this->createNoncestr(),//随机字符串
            'partner_trade_no'=> date('YmdHis').rand(1000, 9999),//商户订单号
            'openid'=> $re_openid,//用户openid
            'check_name'=>'NO_CHECK',//校验用户姓名选项,
            're_user_name'=> $check_name,//收款用户姓名
            'amount'=>$total_amount,//金额
            'desc'=> $desc,//企业付款描述信息
            'spbill_create_ip'=> '118.31.2.245',//Ip地址
        );

        //生成签名算法
        $secrect_key=config('api.key');///这个就是个API密码。MD5 32位。
        $data=array_filter($data);
        ksort($data);
        $str='';
        foreach($data as $k=>$v) {
            $str.=$k.'='.$v.'&';
        }
        $str.='key='.$secrect_key;
        $data['sign']=md5($str);
        //生成签名算法
        $xml= $this->arraytoxml($data);
        $url='https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers'; //调用接口
        $res = $this->curl_post_ssl($url,$xml);
        $return = $this->xmltoarray($res);
        $responseObj = simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA);
        if($responseObj->return_code=='SUCCESS'){
            return true;
        }
        return false;
    }


    /**
     * [curl_post_ssl 发送curl_post数据]
     * @param  [type]  $url     [发送地址]
     * @param  [type]  $xmldata [发送文件格式]
     * @param  [type]  $second [设置执行最长秒数]
     * @param  [type]  $aHeader [设置头部]
     * @return [type]           [description]
     */
    function curl_post_ssl($url, $vars, $second = 30, $aHeader = array()){
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);//设置执行最长秒数
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_URL, $url);//抓取指定网页
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// 终止从服务端进行验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');//证书类型
//        curl_setopt($ch, CURLOPT_SSLCERT, config('api.cert_path'));//证书位置
        curl_setopt($ch, CURLOPT_SSLCERT, '/www/wwwroot/haojiabang/cert/apiclient_cert.pem');//证书位置
        curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');//CURLOPT_SSLKEY中规定的私钥的加密类型
//        curl_setopt($ch, CURLOPT_SSLKEY, config('api.key_path'));//证书位置
        curl_setopt($ch, CURLOPT_SSLKEY, '/www/wwwroot/haojiabang/cert/apiclient_key.pem');//证书位置
        curl_setopt($ch, CURLOPT_CAINFO, 'PEM');
//        curl_setopt($ch, CURLOPT_SSLKEY, config('api.rootca.pem'));//证书位置
        curl_setopt($ch, CURLOPT_CAINFO, '/www/wwwroot/haojiabang/cert/rootca.pem');
        if (count($aHeader) >= 1) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);//设置头部
        }
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);//全部数据使用HTTP协议中的"POST"操作来发送

        $data = curl_exec($ch);//执行回话
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            echo "call faild, errorCode:$error\n";
            curl_close($ch);
            return false;
        }
    }

    /**
     * [xmltoarray xml格式转换为数组]
     * @param  [type] $xml [xml]
     * @return [type]      [xml 转化为array]
     */
    public function xmltoarray($xml) {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring),true);
        return $val;
    }

    /**
     * [arraytoxml 将数组转换成xml格式（简单方法）:]
     * @param  [type] $data [数组]
     * @return [type]       [array 转 xml]
     */
    public function arraytoxml($data){
        $str='<xml>';
        foreach($data as $k=>$v) {
            $str.='<'.$k.'>'.$v.'</'.$k.'>';
        }
        $str.='</xml>';
        return $str;
    }

    /**
     * [createNoncestr 生成随机字符串]
     * @param  integer $length [长度]
     * @return [type]          [字母大小写加数字]
     */
    public function createNoncestr($length =32){
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYabcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";

        for($i=0;$i<$length;$i++){
            $str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }


}
