<?php
namespace app\api\model;

use think\Model;

class SongSheet extends Model
{
    public static function createSongSheet($params)
    {
        $map['name'] = $params['name'];
        $map['fengmianUrl'] = $params['fengmianUrl'];
        $map['uid_c'] = $params['uid'];
        $map['hot'] = 1;
        $map['status'] = 1;
        $map['desc'] = $params['desc'];
        $map['delete_at'] = 0;
        $map['create_at'] = time();
        return self::insertGetId($map);
    }

}