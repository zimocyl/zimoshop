--创建数据库
CREATE database shop10;
use shop10;
--创建品牌表
DROP TABLE IF EXISTS kang_brand;
CREATE TABLE kang_brand(
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'ID',
    title VARCHAR (32) NOT NULL DEFAULT '' COMMENT '品牌名称',
    logo VARCHAR (255) NOT NULL DEFAULT '' COMMENT 'LOGO',
    site VARCHAR (255) NOT NULL DEFAULT '' COMMENT '官网',
    sort INT NOT NULL DEFAULT 0 COMMENT '排序',
    create_time INT NOT NULL DEFAULT 0 COMMENT '创建时间',
    update_time INT NOT NULL DEFAULT 0 COMMENT '修改时间',
    KEY (title),
    KEY (sort)
)engine innodb charset utf8 COMMENT '品牌表';

--管理员表
DROP TABLE IF EXISTS kang_admin;
CREATE TABLE kang_admin
(
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY comment 'ID',
  username VARCHAR (32) NOT NULL DEFAULT '' COMMENT '用户名',
  password VARCHAR (64) NOT NULL DEFAULT '' COMMENT '密码',
  sort  INT NOT NULL DEFAULT 0 COMMENT  '排序',
  create_time INT NOT NULL DEFAULT 0  COMMENT '创建时间',
  update_time INT NOT NULL DEFAULT 0  COMMENT '修改时间',
  KEY (username),
  KEY (password),
  KEY (sort)
)engine innodb charset utf8 comment'管理员';

-- 角色-管理员
drop table if exists kang_role_admin;
create table kang_role_admin
(
  id int unsigned auto_increment primary key comment 'ID',
  role_id int unsigned not null default 0 comment '角色',
  admin_id int unsigned not null default 0 comment '管理员',
  create_time int not null default 0 comment '创建时间',
  update_time int not null default 0 comment '修改时间',
  unique key (role_id, admin_id),
  key (admin_id)
) engine innodb charset utf8 comment '角色-管理员';

-- 角色
drop table if exists kang_role;
create table kang_role
(
  id int unsigned auto_increment primary key comment 'ID',
  title varchar(32) not null default '' comment '角色',
  description varchar(255) not null default '' comment '描述',
  is_super tinyint not null default 0 comment '是否为超管',
  sort int not null default 0 COMMENT '排序',
  create_time int not null default 0 comment '创建时间',
  update_time int not null default 0 comment '修改时间',
  key (title),
  key (sort)
) engine innodb charset utf8 comment '角色';

-- 角色-动作
drop table if exists kang_role_action;
create table kang_role_action
(
  id int unsigned auto_increment primary key comment 'ID',
  role_id int unsigned not null default 0 comment '角色',
  action_id int unsigned not null default 0 comment '动作',
  create_time int not null default 0 comment '创建时间',
  update_time int not null default 0 comment '修改时间',
  unique key (role_id, action_id),
  key (action_id)
) engine innodb charset utf8 comment '角色-动作';

-- 动作
drop table if exists kang_action;
create table kang_action
(
  id int unsigned auto_increment primary key comment 'ID',
  title varchar(32) not null default '' comment '权限',
  rule varchar(255) not null default '' comment '规则', -- back/brand/set, brand-delete
  description varchar(255) not null default '' comment '描述',
  sort int not null default 0 COMMENT '排序',
  create_time int not null default 0 comment '创建时间',
  update_time int not null default 0 comment '修改时间',
  key (title),
  key (rule),
  key (sort)
) engine innodb charset utf8 comment '动作';


-- 商品分类表
drop table if exists kang_category;
create table kang_category (
  id int unsigned auto_increment primary key comment 'ID',
  title varchar(32) not null default '' comment '分类',
  parent_id int unsigned not null default 0 comment '上级分类',
  sort int not null default 0 COMMENT '排序',
  is_used boolean not null default 0 comment '启用', -- tinyint(1)
  -- SEO优化
  meta_title varchar(255) not null default '' comment 'SEO标题',
  meta_keywords varchar(255) not null default '' comment 'SEO关键字',
  meta_description varchar(1024) not null default '' comment 'SEO描述',
  create_time int not null default 0 comment '创建时间',
  update_time int not null default 0 comment '修改时间',
  index (title),
  index (parent_id),
  index (sort)
) engine innodb charset utf8 comment '分类';
insert into kang_category values (1, '未分类', 0, -1, 0, '', '', '', unix_timestamp(), unix_timestamp());

-- 商品分类表
drop table if exists kang_category;
create table kang_category (
  id int unsigned auto_increment primary key comment 'ID',
  title varchar(32) not null default '' comment '分类',
  parent_id int unsigned not null default 0 comment '上级分类',
  sort int not null default 0 COMMENT '排序',
  is_used boolean not null default 0 comment '启用', -- tinyint(1)
  -- SEO优化
  meta_title varchar(255) not null default '' comment 'SEO标题',
  meta_keywords varchar(255) not null default '' comment 'SEO关键字',
  meta_description varchar(1024) not null default '' comment 'SEO描述',
  create_time int not null default 0 comment '创建时间',
  update_time int not null default 0 comment '修改时间',
  index (title),
  index (parent_id),
  index (sort)
) engine innodb charset utf8 comment '分类';
-- 初始化了一条数据
insert into kang_category values (1, '未分类', 0, -1, 0, '', '', '', unix_timestamp(), unix_timestamp());

-- 产品表
drop table if exists kang_product;
create table kang_product (
  id int unsigned auto_increment primary key comment 'ID',
  title varchar(255) not null default '' comment '名称',
  upc varchar(255) not null default '' comment '通用代码', -- 通用商品代码
  image varchar(255) not null default '' comment '图像',
  image_thumb varchar(255) not null default '' comment '缩略图',
  quantity int unsigned not null default 0 comment '库存', -- 库存
  sku_id int unsigned not null default 0 comment '库存单位', -- 库存单位
  stock_status_id int unsigned not null default 0 comment '库存状态', -- 库存状态ID
  is_subtract tinyint not null default 0 comment '扣减库存', -- 是否减少库存
  minimum int unsigned not null default 0 comment '最少起售', -- 最小起订数量
  price decimal(10, 2) not null default 0.0 comment '售价',
  price_origin decimal(10, 2) not null default 0.0 comment '原价',
  is_shipping tinyint not null default 0 comment '配送支持', -- 是否允许配送
  date_available timestamp not null default current_timestamp comment '起售时间', -- 供货日期
  length int unsigned not null default 0 comment '长',
  width int unsigned not null default 0 comment '宽',
  height int unsigned not null default 0 comment '高',
  length_unit_id int unsigned not null default 0 comment '长度单位', -- 长度单位
  weight int unsigned not null default 0 comment '重量',
  weight_unit_id int unsigned not null default 0 comment '重量单位', -- 重量的单位
  is_sale tinyint not null default 0 comment '上架', -- 是否可用
  description text comment '描述', -- 商品描述
  brand_id int unsigned not null default 0 comment '品牌', -- 所属品牌ID
  category_id int unsigned not null default 0 comment '分类', -- 所属主分类ID
  attribute_group_id int unsigned not null default 0 comment '属性组',
  group_id int unsigned not null default 0 comment '所属组',
  static_url varchar(255) not null default '' comment '静态URL',
  admin_id int unsigned not null default 0 comment '创建管理员id',
  -- SEO优化
  meta_title varchar(255) not null default '' comment 'SEO标题',
  meta_keywords varchar(255) not null default '' comment 'SEO关键字',
  meta_description varchar(1024) not null default '' comment 'SEO描述',
  sort int not null default 0 comment '排序', -- 排序
  delete_time int comment '删除时间', -- 用于支持软删除
  create_time int not null default 0 comment '创建时间',
  update_time int not null default 0 comment '修改时间',
  index (title),
  unique key (upc),
  index (brand_id),
  index (category_id),
  index (sku_id),
  index (stock_status_id),
  index (length_unit_id),
  index (weight_unit_id),
  index (sort),
  index (price),
  index (quantity),
  index (delete_time)
) engine innodb charset utf8 comment '产品';

-- 库存单位
drop table if exists kang_sku;
create table kang_sku (
  id int unsigned auto_increment primary key comment 'ID',
  title varchar(32) not null default '' comment '库存单位',
  sort int not null default 0 comment '排序',
  create_time int not null default 0 comment '创建时间',
  update_time int not null default 0 comment '修改时间',
  key (title),
  key (sort)
) ENGINE innodb charset utf8 comment '库存单位';
-- 参考测试数据
insert into kang_sku values (1, '部', 0, unix_timestamp(), unix_timestamp());
insert into kang_sku values (2, '台', 0, unix_timestamp(), unix_timestamp());
insert into kang_sku values (3, '只', 0, unix_timestamp(), unix_timestamp());
insert into kang_sku values (4, '条', 0, unix_timestamp(), unix_timestamp());
insert into kang_sku values (5, '头', 0, unix_timestamp(), unix_timestamp());

-- 库存状态
drop table if exists kang_stock_status;
create table kang_stock_status (
  id int unsigned auto_increment primary key comment 'ID',
  title varchar(32) not null default '' comment '库存状态',
  sort int not null default 0 comment '排序',
  create_time int not null default 0 comment '创建时间',
  update_time int not null default 0 comment '修改时间',
  index (title),
  index (sort)
) engine innodb charset utf8 comment  '库存状态';
-- 参考测试数据
insert into kang_stock_status values (1, '库存充足', 0, unix_timestamp(), unix_timestamp());
insert into kang_stock_status values (2, '脱销', 0, unix_timestamp(), unix_timestamp());
insert into kang_stock_status values (3, '预定', 0, unix_timestamp(), unix_timestamp());
insert into kang_stock_status values (4, '1至3周销售', 0, unix_timestamp(), unix_timestamp());
insert into kang_stock_status values (5, '1至3天销售', 0, unix_timestamp(),unix_timestamp());

-- 长度单位
drop table if exists kang_length_unit;
create table kang_length_unit (
  id int unsigned auto_increment primary key comment 'ID',
  title varchar(32) not null default '' comment '长度单位',
  sort int not null default 0 comment '排序',
  create_time int not null default 0 comment '创建时间',
  update_time int not null default 0 comment '修改时间',
  index (title),
  index (sort)
) engine=innodb charset=utf8 comment='长度单位';
-- 参考测试数据
insert into kang_length_unit values (1, '厘米', 0, unix_timestamp(), unix_timestamp());
insert into kang_length_unit values (2, '毫米', 0, unix_timestamp(), unix_timestamp());
insert into kang_length_unit values (3, '米', 0, unix_timestamp(), unix_timestamp());
insert into kang_length_unit values (4, '千米', 0, unix_timestamp(), unix_timestamp());
insert into kang_length_unit values (5, '英寸', 0, unix_timestamp(), unix_timestamp());


-- 重量单位
drop table if exists kang_weight_unit;
create table kang_weight_unit (
  id int unsigned auto_increment primary key comment 'ID',
  title varchar(32) not null default '' comment '重量单位',
  sort int not null default 0 comment '排序',
  create_time int not null default 0 comment '创建时间',
  update_time int not null default 0 comment '修改时间',
  index (title),
  index (sort)
) engine=innodb charset=utf8 comment='重量单位';
-- 参考测试数据
insert into kang_weight_unit values (1, '克', 0, unix_timestamp(), unix_timestamp());
insert into kang_weight_unit values (2, '千克', 0, unix_timestamp(), unix_timestamp());
insert into kang_weight_unit values (3, '克拉', 0, unix_timestamp(), unix_timestamp());
insert into kang_weight_unit values (4, '市斤', 0, unix_timestamp(), unix_timestamp());
insert into kang_weight_unit values (5, '吨', 0, unix_timestamp(), unix_timestamp());
insert into kang_weight_unit values (6, '磅', 0, unix_timestamp(), unix_timestamp());

-- 属性组
drop table if exists kang_attribute_group;
create table kang_attribute_group
(
  id int unsigned AUTO_INCREMENT primary key comment 'ID',
  title varchar(32) not null default '' comment '属性组',
  sort int not null default 0 comment '排序',
  create_time int comment '创建时间',
  update_time int comment '修改时间',
  index (title),
  index (sort)
) ENGINE innodb CHARSET utf8 comment '属性组';

-- 属性
drop table if exists kang_attribute;
create table kang_attribute
(
  id int unsigned AUTO_INCREMENT primary key comment 'ID',
  title varchar(32) not null default '' comment '属性',
  attribute_group_id int unsigned not null default 0 comment '属性组',
  sort int not null default 0 comment '排序',
  create_time int comment '创建时间',
  update_time int comment '修改时间',
  index (title),
  index (sort),
  index (attribute_group_id)
) ENGINE innodb CHARSET utf8 comment '属性';


-- 商品属性关联
drop table if exists kang_product_attribute;
create table kang_product_attribute
(
  id int unsigned AUTO_INCREMENT primary key comment 'ID',
  product_id int UNSIGNED not null default 0 comment '商品',
  attribute_id int UNSIGNED not null default 0 comment '属性',
  value varchar(255) not null default '' comment '属性值',
  is_extend tinyint not null default 0 comment '型号属性',
  sort int not null default 0 comment '排序',
  create_time int comment '创建时间',
  update_time int comment '修改时间',
  unique index (product_id, attribute_id),
  index (sort),
  index (value)
) ENGINE innodb CHARSET utf8 comment '商品属性值';


-- 产品组
drop table if exists kang_group;
create table kang_group
(
  id int unsigned AUTO_INCREMENT primary key comment 'ID',
  title varchar(32) not null default '' comment '产品组',
  sort int not null default 0 comment '排序',
  create_time int comment '创建时间',
  update_time int comment '修改时间',
  index (title),
  index (sort)
) ENGINE innodb CHARSET utf8 comment '产品组';

drop table if exists kang_gallery;
create table kang_gallery
(
  id int unsigned AUTO_INCREMENT primary key comment 'ID',
  product_id int unsigned not null default 0 comment '所属产品',
  image varchar(255) not null default '' comment '原图',
  image_big varchar(255) not null default '' comment '大图',
  image_small varchar(255) not null default '' comment '小图',
  description varchar(255) not null default '' comment '描述',
  sort int not null default 0 comment '排序',
  create_time int comment '创建时间',
  update_time int comment '修改时间',
  index (sort)
) ENGINE innodb CHARSET utf8 comment '产品相册';