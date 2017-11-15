<?php
/**
 * Created by PhpStorm.
 * User: ZiMo
 * Date
 * Time: %tim: 2017/11/10e%
 */
namespace app\back\controller;
use app\back\model\Category;
use app\back\model\Product;
use app\back\model\ProductAttribute;
use app\back\validate\ProductValidate;
use app\back\model\Group;
use think\Cache;
use think\Controller;
use think\Db;
use think\Image;
use think\queue\job\Redis;
use think\Session;
class ProductController extends Controller
{

    /**
     * 整合添加和更新动作CU
     */
    public function setAction($id = null){
        #分配产品属性所属组的列表到设置视图4
        $this->assign('attribute_group_list',Db::name('attribute_group')->order('sort')->select());
        #分配传进来的id提交时方便找到是数据库的第几条记录要修改
        $this->assign('id',$id);
        #助手函数实例化请求类
        $request = request();
        #接受get请求展示表单
        if ($request->isGet()){
            #通过传递的session值分配到视图中展示错误信息
            $message = Session::get('message')?:[];

            $this->assign('message',$message);
            //上次错误数据与当前正在编辑的数据进行整合
            $data = Session::get('data')?:(!is_null($id)?Db::name('product')->find($id):[]);
            $this->assign('data',$data);
            ## 分配需要的关联数据
            $this->assign('sku_list', Db::name('sku')->order('sort')->select());
            $this->assign('stock_status_list', Db::name('stock_status')->order('sort')->select());
            $this->assign('length_unit_list', Db::name('length_unit')->order('sort')->select());
            $this->assign('weight_unit_list', Db::name('weight_unit')->order('sort')->select());
            $this->assign('brand_list', Db::name('brand')->order('sort')->select());
            if (!($category_list = Cache::get(CategoryController::CACHE_TREE_KEY)))
            {
                $category_list = (new Category())->getTree();
                Cache::set(CategoryController::CACHE_TREE_KEY,$category_list);
            }
            $this->assign('category_list',$category_list);
            return $this->fetch();
        }elseif ($request->isPost())
        {
            #接收post请求处理数据
            #验证数据
            $validate = new ProductValidate();
            if (!$validate->batch(true)->check($request->post())){
                #这里第四个是一个数组传递的是session值
                return $this->redirect('set',['id'=>$id],302,[
                    'message'  =>  $validate->getError(),
                    'data'  =>  $request->post(),
                ]);

            }

            #入库
            if (is_null($id)){
                $model=new Product();
            }else{
                $model = Product::get($id);
            }

            $model->data($request->post());

            $result = $model->allowField(true)->save();
            if ($result){
                ##更新产品和属性的关联
                $rows = array_map(function ($row) use ($model){
                    $row['product_id']=$model->id;
                    return  $row;
                },input('attributes/a',[]));

                (new ProductAttribute())->allowField(true)->saveAll($rows);
                $this->redirect('index');
            }else{
                $this->redirect('set',['id'=>$id],302,$request->post());
            }
        }
    }
    /**
     * 回收站列表
     */
    protected function productList($type = 'undeleted'){
        #回收站功能
        $this->assign('type',$type);
        if ('undeleted' ==  $type){
            #先获取空的查询构建器
            $builder = Product::where(null);
        }elseif ('deleted'){
            $builder = Product::onlyTrashed()->where(null);
        }

        #这里指定空就是查询全部，后面$bulilder->where指定查询条件


        #条件
        $filter = [];
        ##判断是否具有title条件
        $filter_title = input('filter_title','');
        if ($filter_title !== ''){
            #为where(null)增加条件查询
            $builder->where('title','like','%'.$filter_title.'%');
            $filter['filter_title'] = $filter_title;
        }
        ##判断是否具有upc条件
        $filter_upc = input('filter_upc','');
        if ($filter_upc !== ''){
            #为where(null)增加条件查询
            $builder->where('upc','like','%'.$filter_upc.'%');
            $filter['filter_upc'] = $filter_upc;
        }
        ##判断是否具有image条件
        $filter_image = input('filter_image','');
        if ($filter_image !== ''){
            #为where(null)增加条件查询
            $builder->where('image','like','%'.$filter_image.'%');
            $filter['filter_image'] = $filter_image;
        }
        ##判断是否具有stock_status_id条件
        $filter_stock_status_id = input('filter_stock_status_id','');
        if ($filter_stock_status_id !== ''){
            #为where(null)增加条件查询
            $builder->where('stock_status_id','like','%'.$filter_stock_status_id.'%');
            $filter['filter_stock_status_id'] = $filter_stock_status_id;
        }
        ##判断是否具有is_subtract条件
        $filter_is_subtract = input('filter_is_subtract','');
        if ($filter_is_subtract !== ''){
            #为where(null)增加条件查询
            $builder->where('is_subtract','like','%'.$filter_is_subtract.'%');
            $filter['filter_is_subtract'] = $filter_is_subtract;
        }
        ##判断是否具有price条件
        $filter_price = input('filter_price','');
        if ($filter_price !== ''){
            #为where(null)增加条件查询
            $builder->where('price','like','%'.$filter_price.'%');
            $filter['filter_price'] = $filter_price;
        }
        ##判断是否具有is_sale条件
        $filter_is_sale = input('filter_is_sale','');
        if ($filter_is_sale !== ''){
            #为where(null)增加条件查询
            $builder->where('is_sale','like','%'.$filter_is_sale.'%');
            $filter['filter_is_sale'] = $filter_is_sale;
        }
        ##判断是否具有brand_id条件
        $filter_brand_id = input('filter_brand_id','');
        if ($filter_brand_id !== ''){
            #为where(null)增加条件查询
            $builder->where('brand_id','like','%'.$filter_brand_id.'%');
            $filter['filter_brand_id'] = $filter_brand_id;
        }
        ##判断是否具有category_id条件
        $filter_category_id = input('filter_category_id','');
        if ($filter_category_id !== ''){
            #为where(null)增加条件查询
            $builder->where('category_id','like','%'.$filter_category_id.'%');
            $filter['filter_category_id'] = $filter_category_id;
        }
        ##判断是否具有admin_id条件
        $filter_admin_id = input('filter_admin_id','');
        if ($filter_admin_id !== ''){
            #为where(null)增加条件查询
            $builder->where('admin_id','like','%'.$filter_admin_id.'%');
            $filter['filter_admin_id'] = $filter_admin_id;
        }

        #搜索条件分配到模板
        $this->assign('filter',$filter);
        #排序
        $order_field=input('order_field','group_id');
        $order_type = input('order_type','asc');
        if (''!==$order_field){
            $builder->order([$order_field=>$order_type]);
        }
        $this->assign('order',compact('order_field','order_type'));
        #分页
        $limit = 10;
        #检索数据
        $paginater = $builder->paginate($limit);
        $this->assign('paginater',$paginater);
        //起始记录
        $this->assign('start',$paginater->listRows()*($paginater->currentPage()-1)+1);
        //结束记录
        $this->assign('end',min($paginater->listRows()*$paginater->currentPage(),$paginater->total()));
        #渲染
        return $this->fetch('index');
    }
    /**
     * 回收站
     */
    public function trashAction(){
        return  $this->productList('deleted');
    }
    /**
     * 列表页动作R
     */
    public function indexAction(){
        return  $this->productList();
    }
    /**
     * 批量删除D
     */
    public function multiAction(){

        $selected = input('selected/a',[]);
        if (empty($selected)){
            $this->redirect('index');
        }
        switch (input('operate')){
            case 'delete':
                #软删除
                Product::destroy($selected);
                $this->redirect('index');
            case 'restore':
                #还原
                (new Product())->restore(['id'=>['in',$selected]]);
                $this->redirect('trash');
                #彻底删除
            case 'shiftDelete':
                Product::destroy($selected,true);
                $this->redirect('trash');
            case 'group':
                #组合商品
                ##先查询所选商品组的情况
                $group_ids = Db::name('product')
                    ->where('id','in',$selected)
                    ->where('group_id','neq','0')
                    ->distinct(true)
                    ->column('group_id');
                //dump($group_ids)--->没有的话会返回[]
                if (count($group_ids)==0){
                    ##没有任何组，新建一个组
                    $group = new Group();
                    $group->title=Product::get($selected[0])->title;//以第一个产品的名字为组名
                    $group->save();
                    Db::name('product')
                        ->where('id','in',$selected)
                        ->update(['group_id'=>$group->id,]);
                     $this->redirect('index');
                }elseif (count($group_ids)==1){
                    ## 属于一个组，加入到改组即可
                    Db::name('product')
                        ->where('id', 'in', $selected)
                        ->update([
                            'group_id' => $group_ids[0],
                        ])
                    ;
                    $this->redirect('index');

                }else{
                    ## 属于多个组， 不需要处理
                     $this->redirect('index', [], '302', [
                        'message' => '产品属于不同组，不能组合',
                    ]);

                }
                break;
        }
        Product::destroy($selected);
        return $this->redirect('index');
    }
    /**
     * 获取产品属性列表
     */
    public function attributesAction($product_id=null)
    {
        $attribute_group_id = input('attribute_group_id');
        $attribute_list = Db::name('attribute')->alias('a')
            ->join('__PRODUCT_ATTRIBUTE__ pa',"a.id=pa.attribute_id and pa.product_id='$product_id'",'left')
            ->where('a.attribute_group_id',$attribute_group_id)
            ->field('pa.id,a.id attribute_id,pa.value,a.title')
            ->select();
        return  $attribute_list;
    }
    /**
     * 复制商品
     */
    public function copyAction($id)
    {
        #读取商品全部信息
        $row = Db::name('product')->find($id);
        #去掉多余的字段
        unset($row['id'],$row['upc']);
        unset($row['create_time'],$row['update_time'],$row['delete_time']);
        unset($row['admin_id']);
        $row['title'] .= '-拷贝';
        #插入到数据库，只有在模型里的save方法才能在字段不全的情况下插入
        $product = new Product();
        $product->data($row);
        $product->allowField(true)->save();
        $this->redirect('set',['id'=>$product->id]);

        #别忘了插入数据库的时候同时更新关系表product_attribute
        ##读取旧产品的属性
        $rows = Db::name('product_attribute')
            ->where('product_id',$id)
            ->field('attribute_id,value,is_extend,sort')
            ->select();
        ##增加新商品的属性信息
        $rows = array_map(function ($row) use ($product){
            $row['product_id'] = $product->id;
            return  $row;
        },$rows);
        ##批量插入
        (new ProductAttribute())->saveAll($rows);
         $this->redirect('set',['id'=>$product->id]);

    }
    /**
     * Ajax形式上传图片
     */
    public function uploadAction()
    {
        #TP5内置文件上传验证
        $file = request()->file('file');
        $info = $file->validate(['size'=>1*1024*1024,'ext'=>'jpg,png,gif'])->move(ROOT_PATH.'public/upload/product');
        if ($info){
            //上传成功
            #做缩略图，TP5手册上有，图片处理上缩略图制作
            $image = Image::open(ROOT_PATH.'public/upload/product/'.$info->getSaveName());
            $thumb_file = ROOT_PATH.'public/thumb/product/'.dirname($info->getSaveName()).'/thumb_'.$info->getFilename();
            if (!is_dir(dirname($thumb_file))){
                mkdir(dirname($thumb_file),0775,true);
            }
            $image->thumb(360,360,Image::THUMB_FILLED)->save(ROOT_PATH. 'public/thumb/product/' . dirname($info->getSaveName()) . '/thumb_' . $info->getFilename());
            return  ['image'=>str_replace('\\','/',$info->getSaveName()),
                    'image_thumb'=>dirname($info->getSaveName()).'/thumb_'.$info->getFilename(),];
        }else{
            return['error'=>$file->getError(),];
        }
    }

}