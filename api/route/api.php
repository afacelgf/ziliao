<?php
/**
 * Created by PhpStorm.
 * User: laiguofeng
 * Date: 2019-12-28
 * Time: 06:32
 */
use think\facade\Route;

//缺失路由
Route::miss('index/Index/miss');
Route::any('notify', 'api/Notify/index');
Route::post('/test', 'api/Index/test');

Route::post('/common/uploads', 'api/Index/uploads');
Route::post('/common/video_add', 'api/Index/video_add');
Route::post('/common/testTextToAudio', 'api/Tta/textToAudio');
Route::get('/tta/getppyList', 'api/Tta/getppyList');
Route::get('/tta/getToken', 'api/Tta/getToken');
Route::get('/tta/test', 'api/Tta/test');
Route::get('/tta/dealLastDayFile', 'api/Tta/dealLastDayFile');
Route::post('/tta/useLoginByWechat', 'api/TtaUser/useLoginByWechat');
Route::get('/tta/getUserInfo', 'api/TtaUser/getUserInfo');
Route::post('/tta/tapParent', 'api/TtaUser/tapParent');
Route::get('/tta/getConfigData', 'api/Tta/getConfigData');
Route::get('/tta/getTodaySay', 'api/Tta/getTodaySay');
Route::post('/tta/duihuan', 'api/TtaUser/duihuan');//d兑换
Route::post('/tta/inviteExchangevip', 'api/TtaUser/inviteExchangevip');//邀请人数兑换
Route::get('/tta/inviteExchangevipList', 'api/TtaUser/inviteExchangevipList');//兑换记录
Route::get('/tta/inviteList', 'api/TtaUser/inviteList');//邀请记录
Route::get('/tta/getUserQrCodeInfo', 'api/TtaUser/getUserQrCodeInfo');//获取二维码
Route::post('/tta/addExchangeData', 'api/TtaUser/addExchangeData');//插入卡密
Route::post('/tta/downttaFile', 'api/TtaUser/downttaFile');//下载音频文件

Route::get('/openai/getHomeList', 'api/Openai/getHomeList');
Route::post('/openai/singleChat', 'api/Openai/singleChat');
Route::post('/openai/contextChat', 'api/Openai/contextChat');
//路由
Route::group('/api/', function () {
    Route::get('ziliao/grades', 'ziliao/Grade/list');//年级列表
    Route::get('ziliao/getSubjectList', 'ziliao/Subject/getSubjectList');//科目列表
    Route::get('ziliao/getTypeList', 'ziliao/Material/getTypeList');//类别列表
    Route::get('ziliao/getMaterialList', 'ziliao/Material/list');//资料列表
    Route::get('ziliao/search', 'ziliao/Material/search');//搜索资料-根据关键词搜索资料名称
    Route::post('ziliao/requestMaterial', 'ziliao/Userrequest/add');//用户提交资料申请


    Route::get('music/gedanList', 'api/Music/gedanList');//歌单列表
    Route::get('music/songListBySingerId', 'api/Music/songListBySingerId');//歌单列表
    Route::get('music/songList', 'api/Music/songList');//歌单-歌曲列表
    Route::get('music/getSongInfo', 'api/Music/getSongInfo');//歌单-歌曲列表-歌曲信息
    Route::get('music/collectList', 'api/Music/collectList');//歌单-收藏列表
    Route::post('music/collectSong', 'api/Music/collectSong');//歌单-收藏
    Route::post('music/add', 'api/Music/add');//add歌单
    Route::get('singer/list', 'api/Singer/index');//歌手列表
    Route::post('music/addSingerName', 'api/Music/addSingerName');

    // 用户
    Route::post('user/register', 'api/User/register');//注册新用户
    Route::post('user/info', 'api/User/info');//修改用户信息
    Route::post('user/base', 'api/User/base');//查询某个用户信息
    Route::get('user/getWallet', 'api/User/getWallet');//查询某个用户信息
    Route::post('user/login', 'api/User/login');//登录

    // 歌单
    Route::get('songSheet/getMyList', 'api/SongSheet/getMyList');//我的歌单
    Route::get('songSheet/getTuiJianList', 'api/SongSheet/getTuiJianList');//首页推荐歌单,10个
    Route::get('songSheet/getHotList', 'api/SongSheet/getHotList');//热门歌单
    Route::post('songSheet/add', 'api/SongSheet/add');
    Route::post('songSheet/edit', 'api/SongSheet/edit');
    Route::post('songSheet/delete', 'api/SongSheet/delete');
    Route::get('songSheet/detail', 'api/SongSheet/detail');
    Route::get('songSheet/getListById', 'api/SongSheet/getListById');



})->middleware(\app\middleware\Auth::class);
