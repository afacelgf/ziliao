<?php
namespace app\admin\model;

use think\Model;

class Singer extends Model
{
    public function createSinger($params)
    {
        $map['name'] = $params['name'];
        $map['fengmianUrl'] = $params['fengmianUrl'];
        $map['albumIDSinger.phps'] = $params['albumIDs'];
        $map['hot'] = $params['hot'];
        $map['status'] = 1;
        $map['desc'] = $params['desc'];
        $map['songNum'] = $params['songNum']>0?$params['songNum']:0;
        $map['delete'] = 0;
        $map['create_at'] = time();
        return self::insertGetId($map);
    }

}