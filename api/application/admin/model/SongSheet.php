<?php
namespace app\admin\model;

use think\Model;

class SongSheet extends Model
{
    public static function createSongSheet($params)
    {
        $map['name'] = $params['name'];
        $map['fengmianUrl'] = $params['fengmianUrl'];
        $map['uid_c'] = $params['uid_c'];
        $map['hot'] = $params['hot'];
        $map['status'] = $params['status'];
        $map['desc'] = $params['desc'];
        $map['delete_at'] = 0;
        $map['create_at'] = time();
        return self::insertGetId($map);
    }

}