<?php
/**
 * Created by PhpStorm.
 * User: ZiMo
 * Date: 2017/11/11
 * Time: 21:39:05
 */
namespace app\back\model;
use think\Model;
class Attribute extends Model
{
    /**
     * 获取对应的属性分组
     */
    public function attributeGroup()
    {
        //多个属性属于一个属性分组，多对一的关系，使用关联模型，new的时候自动把对应关联模型的值也查到了
        return  $this->belongsTo('AttributeGroup');
    }
}
