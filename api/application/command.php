<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

return [
    'app\api\controller\SendMessage',
    'app\api\controller\SureKuaidiOrderInNight', //结清配送完成，用户未点确认的快递单，晚上12点
    'app\api\controller\clearOutOfDateShops',  //清除过期店铺，每30分钟
];
