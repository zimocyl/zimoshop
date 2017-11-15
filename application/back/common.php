<?php
use think\Config;
/**
 * 返回都有排序参数的URL
 * array $order当前排序参数
 * string $field 需要排序的字段
 */
function urlOrder($route,$params=[],$order=[],$field=null)
{
    #将参数变为普通参数？这种，应为数组型参数pathinfo不支持解析

    #计算排序参数
    $params['order_field'] = $field;
    //已经按照该字段进行升序排序，那么形成降序，反之亦然
    $params['order_type'] = (isset($order['order_field']) && isset($order['order_type'])&& $order['order_field']==$field && 'asc'==$order['order_type']) ? 'desc' : 'asc';

    #利用url生成连接
    return url($route,$params);
}

/**
 * 生成一个排序类生成的方法
 */
function classOrder($order=[],$field=null)
{
    if (!isset($order['order_field'])||!isset($order['order_type']))
    {
        return '';
    }
    if ($order['order_field']==$field)
    {
        return 'asc'==$order['order_type']?'desc':'asc';
    }else{
        return '';
    }
}

/**
 * @param $rules
 * @param null $admin_id
 * @param string $logic
 * 授权认证助手函数
 */
function checkPrivRedirect($rules, $admin_id=null, $logic='and')
{
    if (!\priv\Privilege::checkPriv($rules, $admin_id, $logic)) {
        redirect('back/admin/login', [], '302', [
            'message' => '没有权限执行'
        ])->send();
        die;
    }
}

/**
 * 封装多次调用函数，这里封装模块里都能用
 */
function checkPriv($rules, $admin_id=null, $logic='and')
{
    return \priv\Privilege::checkPriv($rules, $admin_id, $logic);
}