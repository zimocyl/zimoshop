<?php
/**
 * Created by PhpStorm.
 * User: cyl
 * Date: 2017/11/4
 * Time: 21:40
 */

namespace app\back\validate;


use think\Validate;

class BrandValidate extends Validate
{
    #定义验证规则
    protected $rule =
        [
            'title'=>'token|require|max:32|unique:brand,title',
            'site'=>'url|max:255',
            'sort'=>'require|integer',
            'logo'=>'image|max:1048576',

        ];
    #字段名称翻译
    protected $field =
        [
            'title' => '品牌',
            'site'  => '官网',
            'sort'  => '排序',
            'logo'  =>  'Logo',
        ];
}