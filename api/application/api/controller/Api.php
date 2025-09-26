<?php

namespace app\api\controller;

use app\api\model\Orders;
use app\api\model\Pays;
use think\Db;
use think\facade\Request;
use app\Utitls;

class Api
{
    //购买 先调购买，生成订单后再调支付 参数 openID type money payTip
    public function buy()
    {
        $params = Request::param();
        $uuid = \app\api\model\User::getUuid($params['openid']);
        $info = \app\api\model\User::where(['uuid' => $uuid])->find();

//        if (($info['vip'] == 1) && ($info['times'] > time())) {
//            Utitls::sendJson(501);
//        }

        $res = Orders::createOrder($params);
        if ($res) {
            Utitls::sendJson(200, ['order_id' => $res]);
        }
        Utitls::sendJson(500);
    }

//参数 payTip openID  order_id buy上面返的order_id
    public function pay()
    {
        $params = Request::param();
        $order_info = Orders::where(['id' => $params['order_id']])->find();
        $appid            =    config('api.app_id');         //小程序appid
        $body             =    $order_info['payTip'];       //支付说明
        $mch_id           =     config('api.mch_id');      // 商户号    公众平台商户号需绑定到小程序上
        $nonce_str        =    $this->nonce_str();    //  随机字符串
        $notify_url       =    config('api.notify_url');                   //回调地址
        $openid           =    $params['openid'];         //用户openid
        $spbill_create_ip =    $_SERVER['REMOTE_ADDR'];
        $total_fee        =    (int)$order_info['total'];            //支付金额需要乘以100
        $trade_type       =    'JSAPI';

        $uuid = \app\api\model\User::getUuid($params['openid']);
        $datas['type'] = $order_info['type'];
        $datas['uuid'] = $uuid;
        $datas['order_id'] = $params['order_id'];
        $datas['sn'] = $out_trade_no = $order_info['sn'];
        $datas['total'] = $order_info['total'];
        if (!empty($params['money'])){
            $datas['money'] = $params['money'];
        }
        Pays::createOrder($datas);

        //按照顺序生成签名
        $post1['appid']               =      $appid;
        $post1['body']                =      $body;
        $post1['mch_id']              =      $mch_id;
        $post1['nonce_str']          =      $nonce_str;
        $post1['notify_url']         =      $notify_url;
        $post1['openid']             =       $openid;
        $post1['out_trade_no']       =      $out_trade_no;
        $post1['spbill_create_ip']  =      $spbill_create_ip;
        $post1['total_fee']          =     $total_fee;
        $post1['trade_type']         =      $trade_type;

        $sign             =    $this->sign($post1);         //签名

        //生成xml数据
        $post_xml =
            '<xml>
	       <appid>'.$appid.'</appid>
	       <body>'.$body.'</body>
	       <mch_id>'.$mch_id.'</mch_id>
	       <nonce_str>'.$nonce_str.'</nonce_str>
	       <notify_url>'.$notify_url.'</notify_url>
	       <openid>'.$openid.'</openid>
	       <out_trade_no>'.$out_trade_no.'</out_trade_no>
	       <spbill_create_ip>'.$spbill_create_ip.'</spbill_create_ip>
	       <total_fee>'.$total_fee.'</total_fee>
	       <trade_type>'.$trade_type.'</trade_type>
	       <sign>'.$sign.'</sign>
	       </xml> ';

        //调用微信接口下单  得到prepay_id
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        //请求成功后微信会返回一个xml 里面包含prepay_id
        $xml = $this->http_request($url,$post_xml);
        $array = $this->xmlToArray($xml);//全要大写

        //结果分析
        if( $array['return_code'] ==  'SUCCESS' && $array['result_code'] == 'SUCCESS'){
            $tmp                   =    [];          //临时数组用于签名
            $tmp['appId']          =    $appid;
            $tmp['nonceStr']       =    $nonce_str;
            $tmp['package']        =    'prepay_id='.$array['prepay_id'];
            $tmp['signType']       =    'MD5';
            $tmp['timeStamp']      =    strval(time());

            $data['prepay_id']     =    $array['prepay_id'];
            $data['timeStamp']     =    strval(time());         //时间戳  此处转化为字符串模式返回
            $data['nonceStr']      =    $nonce_str;           //随机字符串
            $data['signType']      =    'MD5';               //签名算法，暂支持 MD5
            $data['package']       =    'prepay_id='.$array['prepay_id'];// 接口返回的 prepay_id 参数值，
            $data['paySign']       =    $this->sign($tmp);   //签名
            $data['out_trade_no']  =    $out_trade_no;

        }else{
            $data['state']         =   0;
            $data['text']          =   "错误";
            $data['return_code']  =   $array['return_code'];
            $data['result_msg']   =   $array['result_msg'];
        }
        if ($data){
            Utitls::sendJson(200,$data);
        }
        Utitls::sendJson(500);
    }

    public function nonce_str(){
        $result = '';
        $str = 'QWERTYUIOPASDFGHJKLZXVBNMqwertyuioplkjhgfdsamnbvcxz';
        for ($i=0;$i<32;$i++){
            $result .= $str[rand(0,48)];
        }
        return $result;
    }

    public function sign($data){
        $stringA = '';
        foreach ($data as $key=>$value){
            if(!$value) continue;
            if($stringA){
                $stringA .= '&'.$key."=".$value;
            } else{
                $stringA  = $key."=".$value;
            }
        }
        $wx_key  = config('api.key');    //支付密钥
        $stringSignTemp = $stringA.'&key='.$wx_key;
        return strtoupper(md5($stringSignTemp));
    }

    function http_request($url,$data = null,$headers=array())
    {
        $curl = curl_init();
        if( count($headers) >= 1 ){
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }


    //xml转换成数组
    private function xmlToArray($xml) {

        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring), true);
        return $val;
    }

    public function xml($xml){
        $p = xml_parser_create();
        xml_parse_into_struct($p, $xml, $vals, $index);
        xml_parser_free($p);
        $data = "";
        foreach ($index as $key=>$value) {
            if($key == 'xml' || $key == 'XML') continue;
            $tag = $vals[$value[0]]['tag'];
            $value = $vals[$value[0]]['value'];
            $data[$tag] = $value;
        }
        return $data;
    }


}
