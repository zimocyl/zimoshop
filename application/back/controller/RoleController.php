<?php
/**
 * Created by PhpStorm.
 * User: ZiMo
 * Date
 * Time: %tim: 2017/11/08e%
 */
namespace app\back\controller;
use app\back\model\Action;
use app\back\model\Role;
use app\back\validate\RoleValidate;
use think\Controller;
use think\Db;
use think\Session;
class RoleController extends Controller
{
    /**
     * 整合添加和更新动作CU
     */
    public function setAction($id = null){
        $this->assign('id',$id);
        #助手函数实例化请求类
        $request = request();
        #接受get请求展示表单
        if ($request->isGet()){
            #通过传递的session值分配到视图中展示错误信息
            $message = Session::get('message')?:[];

            $this->assign('message',$message);
            //上次错误数据与当前正在编辑的数据进行整合
            $data = Session::get('data')?:(!is_null($id)?Db::name('role')->find($id):[]);
            $this->assign('data',$data);
            return $this->fetch();
        }elseif ($request->isPost())
        {
            #接收post请求处理数据
            #验证数据
            $validate = new RoleValidate();
            if (!$validate->batch(true)->check($request->post())){
                #这里第四个是一个数组传递的是session值
                return $this->redirect('set',['id'=>$id],302,[
                    'message'  =>  $validate->getError(),
                    'data'  =>  $request->post(),
                ]);

            }

            #入库
            if (is_null($id)){
                $model=new Role();
            }else{
                $model = Role::get($id);
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
        $builder = Role::where(null);

        #条件
        $filter = [];
                ##判断是否具有title条件
        $filter_title = input('filter_title','');
        if ($filter_title !== ''){
        #为where(null)增加条件查询
        $builder->where('title','like','%'.$filter_title.'%');
        $filter['filter_title'] = $filter_title;
        }
        ##判断是否具有description条件
        $filter_description = input('filter_description','');
        if ($filter_description !== ''){
        #为where(null)增加条件查询
        $builder->where('description','like','%'.$filter_description.'%');
        $filter['filter_description'] = $filter_description;
        }
        ##判断是否具有is_super条件
        $filter_is_super = input('filter_is_super','');
        if ($filter_is_super !== ''){
        #为where(null)增加条件查询
        $builder->where('is_super','like','%'.$filter_is_super.'%');
        $filter['filter_is_super'] = $filter_is_super;
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
        Role::destroy($selected);
        return $this->redirect('index');
    }
    /**
     * 授权管理
     */
    public function setActionAction($id){
        $request = request();
        $role = Role::get($id);
        $this->assign('role',$role);
        $this->assign('id',$id);
        #查询到角色已有的权限id并分配到视图
        $owner_actions = Db::name('role_action')->where('role_id',$id)->column('action_id');
        $this->assign('checked_list',$owner_actions);

        #展示当前具备的权限
        if ($request->isGet()){
            ##全部权限动作
            $this->assign('action_list',Action::order('id')->select());
            ##当前角色具备的权限动作
            return  $this->fetch();
        }elseif($request->isPost()){
            #更新授权
            ##本次提交的权限id(role_action表中存储着role_id对应的action_id)
            $actions = input('actions/a',[]);
            #更新role_action表
            ###确定需要删除的
            $deletes = array_diff($owner_actions,$actions);
            Db::name('role_action')->where([
                'role_id'   =>  $id,
                ##判定是否是要删除的id，放在数组里细节注意
                'action_id'     =>      ['in',$deletes],
            ])->delete();
            ###确定需要插入的
            $insert = array_diff($actions,$owner_actions);
            ###因为插入多个数组时必须是二维数组
            $rows = array_map(function ($action_id) use ($id){
                return  [
                    'action_id'     =>      $action_id,
                    'role_id'       =>      $id,
                    'create_time'       =>      time(),
                    'update_time'       =>      time(),
                ];
            },$insert);
            Db::name('role_action')->insertAll($rows);
            $this->redirect('setAction',['id'=>$id]);

        }
    }
}