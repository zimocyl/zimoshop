<?php
/**
 * Created by PhpStorm.
 * User: ZiMo
 * Date
 * Time: %tim: 2017/11/11e%
 */
namespace app\back\controller;
use app\back\model\Attribute;
use app\back\validate\AttributeValidate;
use think\Controller;
use think\Db;
use think\Session;
class AttributeController extends Controller
{
    /**
     * 整合添加和更新动作CU
     */
    public function setAction($id = null){
        ##分配额外的数据属性组到视图，目的是在添加时属性组是下拉菜单能看到所有属性组
        $this->assign('attribute_group_list',Db::name('attribute_group')->order('sort')->select());
        $this->assign('id',$id);
        #助手函数实例化请求类
        $request = request();
        #接受get请求展示表单
        if ($request->isGet()){
            #通过传递的session值分配到视图中展示错误信息
            $message = Session::get('message')?:[];

            $this->assign('message',$message);
            //上次错误数据与当前正在编辑的数据进行整合
            $data = Session::get('data')?:(!is_null($id)?Db::name('attribute')->find($id):[]);
            $this->assign('data',$data);
            return $this->fetch();
        }elseif ($request->isPost())
        {
            #接收post请求处理数据
            #验证数据
            $validate = new AttributeValidate();
            if (!$validate->batch(true)->check($request->post())){
                #这里第四个是一个数组传递的是session值
                return $this->redirect('set',['id'=>$id],302,[
                    'message'  =>  $validate->getError(),
                    'data'  =>  $request->post(),
                ]);

            }

            #入库
            if (is_null($id)){
                $model=new Attribute();
            }else{
                $model = Attribute::get($id);
            }

            $model->data($request->post());
            $result = $model->allowField(true)->save();
            if ($result){
                $this->redirect('index');
            }else{
                $this->redirect('set',['id'=>$id],302,$request->post());
            }
        }
    }
    /**
     * 列表页动作R
     */
    public function indexAction(){
        #构建查询构造器
        #这里指定空就是查询全部，后面$bulilder->where指定查询条件
        $builder = Attribute::where(null);

        #条件
        $filter = [];
                ##判断是否具有title条件
        $filter_title = input('filter_title','');
        if ($filter_title !== ''){
        #为where(null)增加条件查询
        $builder->where('title','like','%'.$filter_title.'%');
        $filter['filter_title'] = $filter_title;
        }
        ##判断是否具有attribute_group_id条件
        $filter_attribute_group_id = input('filter_attribute_group_id','');
        if ($filter_attribute_group_id !== ''){
        #为where(null)增加条件查询
        $builder->where('attribute_group_id','like','%'.$filter_attribute_group_id.'%');
        $filter['filter_attribute_group_id'] = $filter_attribute_group_id;
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
        $limit = 10;
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
        if (empty($selected)){
            return $this->redirect('index');
        }
        Attribute::destroy($selected);
        return $this->redirect('index');
    }
}