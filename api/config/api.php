<?php
/**
 * Created by PhpStorm.
 * User: Xs___
 * Date: 2019/08/25
 * Time: 09:52
 * Desc:
 */

return [
    //域名
    'base_url' => 'https://hjb.jxpyq666.com/',
    //接口秘钥
    'api_key' => 'acfwDzajc2GBaHazOe',
    //支付设置
    'app_id' => 'wx895d0c0979c8c8f4',
    'secret' => 'e8219ac65e77552aad460af4f87c1899',
    // 微信获取access_token的url地址
    'access_token_url' => "https://api.weixin.qq.com/cgi-bin/token?" . "grant_type=client_credential&appid=%s&secret=%s",
    //发送模板消息url地址
    'sendTemplateMessage_url' => "https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token=%s",

    'response_type' => 'array',
    'log' => [
        'level' => 'debug11',
        'file' => app()->getRuntimePath() . 'wechat.log',
    ],

    'mch_id' => '1574171031',
    'key' => 's4SD8f4a8f4aSDFAS7as7sda4a558sas',
    'cert_path' => app()->getRootPath() . 'cert/apiclient_cert.pem',
    'key_path' => app()->getRootPath() . 'cert/apiclient_key.pem',
    'rootca_path' => app()->getRootPath() . 'cert/rootca.pem',
    'notify_url' => 'http://www.haojiabang520.com/notify',
    'laiguofengopenid' => 'oIJO55dxK351_dvsS6S9sKQhUo-g',
    'managesAccountOpenid' => ['oIJO55dxK351_dvsS6S9sKQhUo-g', 'oIJO55eGYDMlQl5GXbC4vNVmOAQU'], //管理员的账户
    'shopRuzhuTip' => '好消息，平台补贴期间，限时免费入驻了，赶紧来申请吧',//店铺入驻页面的口号
    'kouhaotip' => '关心家乡每日事，就在家乡朋友圈',//我的页面的口号
    'tixianCount' => 182,//已提现成功人数
    'zanjifen' => 10,//赞得积分
    'invitationjifen' => 10, //邀请好友得积分
    'readjifen' => 1,//阅读得积分
    'commentjifen' => 2,//评论得积分
    'kefuwx' => 15778064843,//客服微信号
    'zsphone' => 15778064843,//招商合作电话
    'getjifentip' => '积分可以直接兑换为现金，获取途径：邀请新用户10积分/人，发布新闻后，新闻的浏览量1积分/次、点赞10积分/赞、评论2积分/个，快去邀请用户和发布新闻吧，分享到微信群将获得更多的阅读和点赞喔。',//招商合作电话
    'chatUrl' => "https://laiopenai.openai.azure.com/openai/deployments/laigpt35/chat/completions/?api-version=2023-05-15",
    'chatKey' => 'b3e51b36f90f455ab8ebb2c7dc5d4f69',
    'chatNormalUseCountLimit' => 3,//chat每天次数-普通用户
    'chatVipUseCountLimit' => 50,//chat每天次数-vip
    'chatNormalTextCountLimit' => 50, //chat问题字数限制-普通用户
    'chatVipTextCountLimit' => 1000,//chat问题字数限制-vip
    'chatAnswerShowSpeed' => 70,//chat答案显示的速度
    'chatContextNavTitle' => 'ChatGPT Pro',//chat导航栏
    'chatSingleNavTitle' => 'AI 聊天',//chat导航栏


];
