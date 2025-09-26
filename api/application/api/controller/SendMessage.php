<?php
namespace app\api\controller;

use app\api\controller\Tixian;
use app\api\controller\User;
use app\api\model\Orders;
use app\Utitls;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\Log;
//已配置每20分钟执行一次，查快递的过期且已支付且未被接单成功的的 订单号，退款给用户。

class SendMessage extends Command
{
    protected function configure(){
        $this->setName('SendMessage')->setDescription("计划任务 SendMessage");
    }

    //调用SendMessage 这个类时,会自动运行execute方法
    protected function execute(Input $input, Output $output){
        $output->writeln('退款开始1111'.date("Y-m-d H:i:s",time()) );
        /*** 这里写计划任务列表集 START ***/
       $info= $this->tuikuan();//退款
        /*** 这里写计划任务列表集 END ***/
        $output->writeln('退款结束'.date("Y-m-d H:i:s",time()).'---'.$info);
    }

    public function tuikuan(){

        //移除到期且未付款的
        $gqnopaykdarr = \app\api\model\Kuaidi::where(['delete_at'=>0,'pay'=>0])->whereTime('songdao_time', '<=', time())->select();
        if($gqnopaykdarr) {
            foreach ($gqnopaykdarr as $key => $value) {
                \app\api\model\Kuaidi::where(['id'=>$value['id']])->update(
                    ['delete_at'=>1]
                );
            }
        }
        //查快递的过期且已支付且未被接单成功的的 订单号，退款给用户。
        //status 0未接单 1接单中，待取件 2已取件，待配送 3，配送完成 4接单中，待确认  5已完成 6已取消
        $kdarr = \app\api\model\Kuaidi::where(['delete_at'=>0,'pay'=>1,'status'=>0])->whereTime('songdao_time', '<=', time())->select();

        if($kdarr){
            foreach ($kdarr as $key =>$value){
                $where = ['delete_at' => 0, 'sn' => $value['order_id'], 'status' => 1];
                $kdorder = Orders::where($where)->find();
                $orderNo = $kdorder['sn'];      //商户订单号（商户订单号与微信订单号二选一，至少填一个）
                $wxOrderNo = '';    //微信订单号（商户订单号与微信订单号二选一，至少填一个）
                $totalFee = $kdorder['total'] / 100;       //订单金额，单位:元
                $refundFee = $kdorder['total'] / 100;      //退款金额，单位:元
                $result = $this->doRefund($totalFee, $refundFee, $wxOrderNo, $orderNo);
                if ($result == true) {
                    //业务逻辑处理,改为已退款，并移除
                    $change = Orders::where($where)->update(
                        ['status'=>2,'delete_at'=>1]
                    );
                    \app\api\model\Kuaidi::where(['delete_at'=>0,'order_id'=>$value['order_id'],'pay'=>1])->update(
                        ['pay'=>2,'delete_at'=>1,'status'=>6]
                    );
                    if($change){
                        Utitls::sendJson(200,$result);
                    }
                    Utitls::sendJson(500,$result);
                }
                Utitls::sendJson(500,$result);
            }
        }
        echo 'refund fail';
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
//        echo '参数112==='.implode(',', $unified);

        $unified['sign'] =$this->getSign($unified);
        $responseXml = $this->curl_post_ssl('https://api.mch.weixin.qq.com/secapi/pay/refund', self::arrayToXml($unified));
//        echo 'jieguo===';
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
     * [curl_post_ssl 发送curl_post数据]
     * @param  [type]  $url     [发送地址]
     * @param  [type]  $xmldata [发送文件格式]
     * @param  [type]  $second [设置执行最长秒数]
     * @param  [type]  $aHeader [设置头部]
     * @return [type]           [description]
     */
    function curl_post_ssl($url, $vars, $second = 30, $aHeader = array()){
//        $isdir = "/cert/";//证书位置
        $isdir = $_SERVER['DOCUMENT_ROOT']."/cert/";//证书位置

        $ch = curl_init();//初始化curl
//        dump(__DIR__);
//        dump(dirname(__FILE__));
//        dump(config('api.cert_path'));
//        exit();
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
