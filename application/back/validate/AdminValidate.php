<?php
/**
 * Created by PhpStorm.
 * User: ZiMo
 * Date: 2017/11/07
 * Time: 20:45:20
 */
namespace app\back\validate;
use think\Validate;
class AdminValidate extends Validate
{
    #定义验证规则
    protected $rule =
        [
            ##令牌校验
            '__token__'     =>      'require|token',
            #自定义规则
            'username'      =>      'require|max:32|unique:admin,username',
            'sort'          =>      'integer',
            'password'      =>      'require|min:5',
            'password_confirm'      =>      'require|confirm:password',
        ];
    #字段名称翻译
    protected $field =
        [
        'id'   =>  'ID',
        'username'   =>  '用户名',
        'password'   =>  '密码',
        'sort'   =>  '排序',
        'create_time'   =>  '创建时间',
        'update_time'   =>  '修改时间',
        'password_confirm'  =>  '确认密码',

        ];
    protected $scene =
        [
            'update'    =>      ['__token__','username'],
            'setPassword'   =>      ['__token__','password','password_confirm'],
        ];
}