<?php
/**
 * Created by PhpStorm.
 * User: ZiMo
 * Date: 2017/11/10
 * Time: 13:08:34
 */
namespace app\back\model;
use think\Model;
use think\Session;
use traits\model\SoftDelete;

class Product extends Model
{
    use SoftDelete;
    //
    protected $auto = [
        'upc','admin_id',
    ];
    protected function setUpcAttr($value){
        return  $value  ?:uniqid();
    }
    protected function setAdminIdAttr($value){
        return  Session::get('admin.id');
    }
    /**
     * 关联组查询
     */
    public function group()
    {
        return  $this->belongsTo('Group');
    }
}
