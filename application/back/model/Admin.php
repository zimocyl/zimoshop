<?php
/**
 * Created by PhpStorm.
 * User: ZiMo
 * Date: 2017/11/07
 * Time: 20:45:20
 */
namespace app\back\model;
use think\Model;
class Admin extends Model
{
    //密码自动加密
    #先找到字段
    protected $insert = ['password'];
    /**
     * 自动加密固定格式
     */
    protected function setPasswordAttr($value)
    {
        return  md5($value);
    }
}
