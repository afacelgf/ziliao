<?php
/**
 * Created by PhpStorm.
 * User: laiguofeng
 * Date: 2020-10-29
 * Time: 09:20
 */
use think\facade\Route;

//缺失路由
Route::miss('index/Index/miss');

Route::post('/admin/test', 'admin/Index/test');

Route::get('/admin/getTownList' , 'api/Index/getTownList');//乡镇列表
Route::get('/admin/getzhufu', 'api/Index/getzhufu');

//路由
Route::group('/admin/', function () {

    Route::post('login', 'admin/User/login');
    Route::post('user/getUseruthority', 'admin/User/getUseruthority');

    // 歌曲12
    Route::get('musicList/getList', 'admin/Music/getList');
    Route::post('musicList/add', 'admin/Music/add');
    Route::post('musicList/edit', 'admin/Music/edit');
    Route::post('musicList/delete', 'admin/Music/delete');
    Route::post('musicList/update', 'admin/Music/update');
    Route::get('musicList/detail', 'admin/Music/detail');
    // 歌单
    Route::get('songSheet/getList', 'admin/SongSheet/getList');
    Route::post('songSheet/add', 'admin/SongSheet/add');
    Route::post('songSheet/edit', 'admin/SongSheet/edit');
    Route::post('songSheet/delete', 'admin/SongSheet/delete');
    Route::get('songSheet/detail', 'admin/SongSheet/detail');
    //歌手

    Route::get('singer/getList', 'admin/Singer/getList');
    Route::post('singer/add', 'admin/Singer/add');
    Route::post('singer/edit', 'admin/Singer/edit');
    Route::post('singer/delete', 'admin/Singer/delete');
    Route::post('singer/update', 'admin/Singer/update');
});
