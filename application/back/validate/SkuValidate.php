<?php
/**
 * Created by PhpStorm.
 * User: ZiMo
 * Date: 2017/11/10
 * Time: 12:59:30
 */
namespace app\back\validate;
use think\Validate;
class SkuValidate extends Validate
{
    #定义验证规则
    protected $rule =
        [
            ##令牌校验
            '__token__'     =>      'require|token',
            #自定义规则

        ];
    #字段名称翻译
    protected $field =
        [
        'id'   =>  'ID',
        'title'   =>  '库存单位',
        'sort'   =>  '排序',
        'create_time'   =>  '创建时间',
        'update_time'   =>  '修改时间',

        ];
}