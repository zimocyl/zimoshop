<?php
/**
 * Created by PhpStorm.
 * User: ZiMo
 * Date: 2017/11/10
 * Time: 13:08:34
 */
namespace app\back\validate;
use think\Validate;
class ProductValidate extends Validate
{
    #定义验证规则
    protected $rule =
        [
            ##令牌校验
            '__token__'     =>      'require|token',
            #自定义规则
            'title'         =>      'require',
            'upc'           =>      'unique:product,upc',
            'quantity'      =>      'integer',
        ];
    #字段名称翻译
    protected $field =
        [
        'id'   =>  'ID',
        'title'   =>  '名称',
        'upc'   =>  '通用代码',
        'image'   =>  '图像',
        'image_thumb'   =>  '缩略图',
        'quantity'   =>  '库存',
        'sku_id'   =>  '库存单位',
        'stock_status_id'   =>  '库存状态',
        'is_subtract'   =>  '扣减库存',
        'minimum'   =>  '最少起售',
        'price'   =>  '售价',
        'price_origin'   =>  '原价',
        'is_shipping'   =>  '配送支持',
        'date_available'   =>  '起售时间',
        'length'   =>  '长',
        'width'   =>  '宽',
        'height'   =>  '高',
        'length_unit_id'   =>  '长度单位',
        'weight'   =>  '重量',
        'weight_unit_id'   =>  '重量单位',
        'is_sale'   =>  '上架',
        'description'   =>  '描述',
        'brand_id'   =>  '品牌',
        'category_id'   =>  '分类',
        'type_id'   =>  '属性组',
        'group_id'   =>  '所属组',
        'static_url'   =>  '静态URL',
        'admin_id'   =>  '创建管理员id',
        'meta_title'   =>  'SEO标题',
        'meta_keywords'   =>  'SEO关键字',
        'meta_description'   =>  'SEO描述',
        'sort'   =>  '排序',
        'delete_time'   =>  '删除时间',
        'create_time'   =>  '创建时间',
        'update_time'   =>  '修改时间',

        ];
}