<?php
/**
 * Created by PhpStorm.
 * User: ZiMo
 * Date
 * Time: %tim: 2017/11/07e%
 */
namespace app\back\controller;
use app\back\model\Admin;
use app\back\validate\AdminValidate;
use think\Controller;
use think\Db;
use think\Session;
class AdminController extends Controller
{
    /**
     * 整合添加和更新动作CU
     */
    public function setAction($id = null){
        $this->assign('id',$id);
        #助手函数实例化请求类
        $request = request();
        #接受get请求展示表单
        #通过admin_id获得role_admin关系表中所对应的角色
        $checked_roles = Db::name('role_admin')->where('admin_id',$id)->column('role_id');
        if ($request->isGet()){
            #通过传递的session值分配到视图中展示错误信息
            $message = Session::get('message')?:[];

            $this->assign('message',$message);
            //上次错误数据与当前正在编辑的数据进行整合
            $data = Session::get('data')?:(!is_null($id)?Db::name('admin')->find($id):[]);
            $this->assign('data',$data);
            ##展示全部角色
            $this->assign('role_list',Db::name('role')->select());
            ##当前管理员所属的角色
            ###传递当前管理员所属角色的id值
            $this->assign('checked_roles',Db::name('role_admin')->where('admin_id',$id)->column('role_id'));
            return $this->fetch();
        }elseif ($request->isPost())
        {
            #接收post请求处理数据
            #验证数据
            $validate = new AdminValidate();
            $validate->batch(true);
            if (!is_null($id)){
                $validate->scene('update');
            }
            if (!$validate->check($request->post())){
                #这里第四个是一个数组传递的是session值
                $this->redirect('set',['id'=>$id],302,[
                    'message'  =>  $validate->getError(),
                    'data'  =>  $request->post(),
                ]);

            }

            #入库
            if (is_null($id)){
                $model=new Admin();
            }else{
                $model = Admin::get($id);
            }

            $model->data($request->post());
            $result = $model->allowField(true)->save();
            if ($result){
                ##管理员更新成功紧接着更新关系表role_admin
                    #更新所属角色
                    $roles = input('roles/a',[]);
                    #删除没有选中之前有的角色
                    $delete = array_diff($checked_roles,$roles);
                    Db::name('role_admin')->where([
                        'admin_id'      =>      $model->id,
                        'role_id'       =>      ['in',$delete],

                    ])->delete();
                    #插入新创建的角色
                    $insert = array_diff($roles,$checked_roles);
                    ##因为插入多个数据时，要求是二维数组，所以要转换
                    $rows = array_map(function($role_id) use ($model){
                        return  [
                            'role_id'   =>      $role_id,
                            'admin_id'  =>      $model->id,
                            'create_time' =>      time(),
                            'update_time' =>      time()
                        ];
                    },$insert);
                    Db::name('role_admin')->insertAll($rows);
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
        $builder = Admin::where(null);

        #条件
        $filter = [];
                ##判断是否具有username条件
        $filter_username = input('filter_username','');
        if ($filter_username !== ''){
        #为where(null)增加条件查询
        $builder->where('username','like','%'.$filter_username.'%');
        $filter['filter_username'] = $filter_username;
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
        if (empty($selected)){
            return $this->redirect('index');
        }
        Admin::destroy($selected);
        return $this->redirect('index');
    }
    /**
     * 修改密码
     */
    public function setPasswordAction($id)
    {
        #通过传递的session值分配到视图中展示错误信息
        $message = Session::get('message')?:[];

        $this->assign('message',$message);
        #分配id到修改视图
       $this->assign('id',$id);
       #查询所要修改管理员的信息
       $admin = Admin::get($id);
       #分配到模板展示
       $this->assign('admin',$admin);

       #如果是通过地址栏get请求
        $request = request();
        if ($request->isGet()){
            return   $this->fetch();
        }elseif($request->isPost()){
            #验证
            $validate = new AdminValidate();
            $validate->batch(true);
            if (!is_null($id)){
                $validate->scene('setPassword');
            }
            #验证失败
            if (!$validate->check($request->post())){

               $this->redirect('setPassword',['id'=>$id],302,[
                   'message'    =>  $validate->getError(),
                    'data'      =>  $request->post(),
                ]);
            }
            #更新数据
            $admin->data($request->post());
            $result = $admin->allowField(true)->save();
            if ($result){
                  $this->redirect('index');
            }else{
              $this->redirect('setPassword',['id'=>$id]);
            }
        }

    }
    /**
     * 管理员登录界面
     */
    public function loginAction(){
        $request = request();
        if ($request->isGet()){
            $this->assign('message',Session::get('message')?:'');
            $this->assign('data',Session::get('data')?:[]);
            return  $this->fetch();
        }elseif ($request->isPost()){
            $username = input('username');
            $password = input('password');
            $condition = [
                'username'  =>  $username,
                'password'  =>  md5($password)
            ];
            $admin = Admin::where($condition)->find();
            if ($admin){
                Session::set('admin',$admin);
                ###在行为拓展里验证session存在时给session加入的route
                $route = Session::has('route')?Session::pull('route'):'back/brand/index';
                $this->redirect($route);
            }else{
                $this->redirect('login',[],302,[
                   'message'    =>  '管理员信息错误',
                    'data'      =>  $request->post(),
                ]);
            }
        }
    }
    /**
     * 后台退出
     */
    public function loginoutAction(){
        Session::delete('admin');
        $this->redirect('login');
    }

}