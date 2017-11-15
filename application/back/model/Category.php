<?php
/**
 * Created by PhpStorm.
 * User: ZiMo
 * Date: 2017/11/09
 * Time: 23:07:41
 */
namespace app\back\model;
use think\Model;
class Category extends Model
{
    #获取带有缩进层级数的数组。先获取全部分类，递归检索子分类
    public function getTree()
    {
        #获取全部分类
        $rows = $this->order('sort')->select();
        #递归树状排序
        $tree = $this->tree($rows,0,0);

        return  $tree;
    }

    protected function tree($rows,$id=0,$level=0){
        static $tree = [];
        foreach ($rows  as  $row){
            if ($id == $row['parent_id']){
                $row['level'] = $level;
                $tree[] = $row;

                $this->tree($rows,$row['id'],$level+1);

            }
        }
        return  $tree;
    }
}
