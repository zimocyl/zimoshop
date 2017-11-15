<?php
/**
 * Created by PhpStorm.
 * User: cyl
 * Date: 2017/11/4
 * Time: 18:47
 */

namespace app\back\controller;


use app\back\model\Brand;
use app\back\validate\BrandValidate;
use think\Config;
use think\Controller;
use think\Db;
use think\Session;
use think\Validate;

class BrandController extends Controller
{
    /**
     * 整合添加和更新动作CU
     */

    public function setAction($id = null){
        ##自定义权限校验
        if (is_null($id)){
           checkPrivRedirect('brand-create');
        }else{
            checkPrivRedirect('brand-edit');
        }
        $this->assign('id',$id);
        #助手函数实例化请求类
        $request = request();
        #接受get请求展示表单
        if ($request->isGet()){
            #通过传递的session值分配到视图中展示错误信息
            $message = Session::get('message')?:[];
            $this->assign('message',$message);
            //上次错误数据与当前正在编辑的数据进行整合
            $data = Session::get('data')?:(!is_null($id)?Db::name('brand')->find($id):[]);

            $this->assign('data',$data);
            return $this->fetch();
        }elseif ($request->isPost())
        {
            #接收post请求处理数据
            #验证数据
            $brand_validate = new BrandValidate();
            $data = $request->post();

            $data['logo'] = $request->file('logo')?:'';

            if (!$brand_validate->batch(true)->check($data)){
                #这里第四个是一个数组传递的是session值
                return $this->redirect('set',['id'=>$id],302,[

                    'data'  =>  $request->post(),
                    'message'  =>  $brand_validate->getError(),
                ]);

            }

            #入库
            if (is_null($id)){
                $model=new Brand();
            }else{
                $model = Brand::get($id);
            }
            if ($id && input('logo_delete',false)){
                @unlink(ROOT_PATH.'public/upload/brand/'.$model->logo);
            }
            #存在logo文件
            if ($data['logo']){
                $info = $data['logo']->move(ROOT_PATH.'public/upload/brand');
                $data['logo'] = $info->getSaveName();
            }
            $model->data($data);
            $result = $model->allowField(true)->save();

            if ($result){
                $this->redirect('index');
            }else{
                $this->redirect('set',['id'=>$id],302,$request->post());
            }


        }
    }
    /**
     * 创建添加表动作C
     */
    public function createAction(){
        #助手函数实例化请求类
        $request = request();
        #接受get请求展示表单
        if ($request->isGet()){
            #通过传递的session值分配到视图中展示错误信息
            $message = Session::get('message')?:[];
            $data = Session::get('data')?:[];
            $this->assign('message',$message);
            $this->assign('data',$data);
            return $this->fetch();
        }elseif ($request->isPost())
        {
            #接收post请求处理数据
            #验证数据
            $brand_validate = new BrandValidate();
            if (!$brand_validate->batch(true)->check($request->post())){
                #这里第四个是一个数组传递的是session值
                return $this->redirect('create',[],302,[
                 'message'  =>  $brand_validate->getError(),
                    'data'  =>  $request->post(),
                ]);

            }

            #入库
            #创建一个品牌对象
            $brandmodel = new Brand();
            $brandmodel->data($request->post());
            $result = $brandmodel->save();
            if ($result){
                $this->redirect('index');
            }else{
                $this->redirect('create',[],302,$request->post());
            }


        }
    }



    /**
     * 列表页动作R
     */
    public function indexAction(){
        #构建查询构造器
        #这里指定空就是查询全部，后面$bulilder->where指定查询条件
        $builder = Brand::where(null);


        #条件
        $filter = [];
        ##判断是否具有title条件
        $filter_title = input('filter_title','');
        if ($filter_title !== ''){
            #呼应上面查询构造器
            $builder->where('title','like','%'.$filter_title.'%');
            $filter['filter_title'] = $filter_title;
        }
        ##判断是否具有site条件
        $filter_site = input('filter_site','');
        if ($filter_site !== ''){
            $builder->where('site','like','%'.$filter_site.'%');
            $filter['filter_site'] = $filter_site;
        }
        #搜索条件分配到模板
        $this->assign('filter',$filter);
        #排序
        $order_field=input('order_field','');
        $order_type = input('order_type','asc');
        if (''!==$order_field){
            $builder->order([$order_field=>$order_type]);
        }
        $this->assign('order',compact('order_field','order_type'));
        #分页
        $limit = 3;

        #检索数据

        $paginater = $builder->paginate($limit);
        $this->assign('paginater',$paginater);
        //起始记录
        $this->assign('start',$paginater->listRows()*($paginater->currentPage()-1)+1);
        //结束记录
        $this->assign('end',min($paginater->listRows()*$paginater->currentPage(),$paginater->total()));
        #渲染
        return $this->fetch();
    }
    /**
     * 批量删除D
     */
    public function multiAction(){

        $selected = input('selected/a',[]);
        $model = Brand::get($selected);
        if (isset($model->logo)){
            @unlink(ROOT_PATH.'public/upload/brand/'.$model->logo);
        }
        if (empty($selected)){
            return $this->redirect('index');
        }
        Brand::destroy($selected);
        return $this->redirect('index');
    }
    /**
     * 添加页AJAX唯一性验证调用方法
     */
    public function titleUniqueCheckAction(){
        Config::set('default_ajax_return','html');
        return Validate::unique(null,'brand,title',input(),'title')?'true':'false';
    }
}