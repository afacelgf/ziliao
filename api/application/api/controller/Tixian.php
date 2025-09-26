<?php
namespace app\api\controller;

//企业付款到微信零钱，PHP接口调用方法
use app\Utitls;
use app\api\model\User as UsersModel;
use app\api\model\UsersWallet as UsersWallet;
use think\facade\Request;

define("APPID", config('api.app_id')); // 商户账号appid
define("MCHID", config('api.mch_id'));      // 商户号
define("SECRECT_KEY", config('api.key'));  //支付密钥签名
define("IP", $_SERVER['REMOTE_ADDR']);   //IP


class Tixian {

    //管理员同意提现
    public function agreentixianPost(){
        $params = Request::param();
        //查这条记录
       $info = db('user_tixian_log')->where(['id'=>$params['id'], 'delete_at' => 0,'status'=>0])->find();
        $openid = \app\api\model\User::getOpenid($info['uuid']);
       $res= $this->sendMoney($info['money'],$openid);

       if($res){
           $uuid = UsersModel::getUuid($openid);
           $update = db('user_tixian_log')->where(['uuid'=>$uuid,'id'=>$params['id']])->update(['status'=>"1",'update_at'=>time()]);
           if ($update) {
               $nickname = \app\api\model\User::where(['uuid'=>$uuid])->value('nickname');
               //给用户发送待审核订阅消息
               $data = [
                   "amount1" => [
                       "value"=> $info['money']
                   ],
                   "time7" => [
                       "value"=> date('Y-m-d H:i:s',time())
                   ],
                   "thing9" => [
                       "value"=> $nickname
                   ]
               ];
               $page = '/pages/mine/mine/managed/user/tixian';
               $kuaidi =  new Kuaidi();
               $ret= $kuaidi->sendKuaidiDaiSureMessage(config('api.laiguofengopenid'),'yMG714twuFxwbMhQCv1kawppCeHpiLYyo_36mcTU2Gg',$page,$data);


           }
           Utitls::sendJson(200, $res,'提现成功');
       }
        Utitls::sendJson(500, $res,'提现失败了');
    }

     //管理员拒绝提现
    public function refusetixianPost(){
        $params = Request::param();
        //查这条记录
       $info = db('user_tixian_log')->where(['id'=>$params['id'], 'delete_at' => 0,'status'=>0])->update(
           ['status'=>2]
       );

       if($info){
           Utitls::sendJson(200, $info,'拒绝提现成功');
       }
        Utitls::sendJson(500, $info,'拒绝提现失败了');
    }


    /**
     * [企业付款到零钱]
     * @param  [type] $amount     [发送的金额（分）目前发送金额不能少于1元]
     * @param  [type] $re_openid  [发送人的 openid]
     * @param  string $desc       [企业付款描述信息 (必填)]
     * @param  string $check_name [收款用户姓名 (选填)]
     * @param $type 1默认是用户提现  2.给骑手发佣金
     * @return [type]             [description
     */
    public function sendMoney($amount,$re_openid,$desc='提现',$check_name='',$type=1){

        $total_amount = (100) * $amount;
        $data=array(
            'mch_appid'=>APPID,//商户账号appid
            'mchid'=> MCHID,//商户号
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
        $secrect_key=SECRECT_KEY;///这个就是个API密码。MD5 32位。
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
            if ($type==1){ //用户提现
               return true;
            }else  if ($type==2) { //发钱给骑手
                return true;
            }
            return false;
        }
        return false;
    }

    public function test(){
        return 'w shi test';
    }


    /**
     * 退款
     * @param float $totalFee 订单金额 单位元
     * @param float $refundFee 退款金额 单位元
     * @param string $refundNo 退款单号
     * @param string $wxOrderNo 微信订单号
     * @param string $orderNo 商户订单号随机生成
     * @return string
     */
    public function doRefund($totalFee, $refundFee, $wxOrderNo = '', $orderNo = '') {

        $unified = array(
            'appid' => config('api.app_id'),
            'mch_id' => config('api.mch_id'),
            'nonce_str' => self::createNonceStr(),
            'total_fee' => intval($totalFee * 100), //订单金额  单位 转为分
            'refund_fee' => intval($refundFee * 100), //退款金额 单位 转为分
            'sign_type' => 'MD5', //签名类型 支持HMAC-SHA256和MD5，默认为MD5
//            'transaction_id' => $orderNo, //微信订单号
            'out_trade_no' => $orderNo, //商户订单号
            'out_refund_no' => orderNo(), //商户退款单号
            'refund_desc' => '退款', //退款原因（选填）
        );
        $unified['sign'] =$this->getSign($unified);
        $responseXml = $this->curl_post_ssl('https://api.mch.weixin.qq.com/secapi/pay/refund', self::arrayToXml($unified));
        $unifiedOrder = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($unifiedOrder === false) {
            return false;
        }
        if ($unifiedOrder->return_code != 'SUCCESS') {
            return false;
        }
        if ($unifiedOrder->result_code != 'SUCCESS') {
            return false;
        }
        return true;
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


    protected function getSign($Obj){

        foreach ($Obj as $k => $v){

            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //签名步骤二：在string后加入KEY
        $String = $String."&key=".config('api.key');
        //签名步骤三：MD5加密
        $String = md5($String);
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        return $result_;
    }

    /*
   *排序并格式化参数方法，签名时需要使用
   */
    protected function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v)
        {
            if($urlencode)
            {
                $v = urlencode($v);
            }
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar='';
        if (strlen($buff) > 0)
        {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }


}

