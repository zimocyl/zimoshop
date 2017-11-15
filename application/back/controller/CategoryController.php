<?php
/**
 * Created by PhpStorm.
 * User: ZiMo
 * Date
 * Time: %tim: 2017/11/09e%
 */
namespace app\back\controller;
use app\back\model\Category;
use app\back\validate\CategoryValidate;
use think\Cache;
use think\Controller;
use think\Db;
use think\Session;
class CategoryController extends Controller
{
    //分类树，缓存key
    const CACHE_TREE_KEY = 'category_tree';
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
            ## 全部分类
            $this->assign('category_list', (new Category())->getTree());
            $this->assign('message',$message);
            //上次错误数据与当前正在编辑的数据进行整合
            $data = Session::get('data')?:(!is_null($id)?Db::name('category')->find($id):[]);
            $this->assign('data',$data);
            return $this->fetch();
        }elseif ($request->isPost())
        {
            #接收post请求处理数据
            #验证数据
            $validate = new CategoryValidate();
            if (!$validate->batch(true)->check($request->post())){
                #这里第四个是一个数组传递的是session值
                return $this->redirect('set',['id'=>$id],302,[
                    'message'  =>  $validate->getError(),
                    'data'  =>  $request->post(),
                ]);

            }

            #入库
            if (is_null($id)){
                $model=new Category();
            }else{
                $model = Category::get($id);
            }

            $model->data($request->post());
            $result = $model->allowField(true)->save();
            if ($result){
                ##删除缓存
                $this->deleteCache();
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
        #获取树状数据
        #用到递归的地方使用Redis缓存
        #获取数据
        ##判断缓存中是否存在数据

        if (!($rows = Cache::get(self::CACHE_TREE_KEY))) {
            ### 没有缓存
            ### 由模型从数据表获取
            $rows = (new Category())->getTree();
            Cache::set(self::CACHE_TREE_KEY, $rows);
        }
        $this->assign('rows', $rows);

        # 渲染模板
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
        Category::destroy($selected);
        #删除缓存
        $this->deleteCache();
        return $this->redirect('index');
    }
    /**
     * 删除缓存
     */
    protected function deleteCache(){
        Cache::rm(self::CACHE_TREE_KEY);
        return  $this;
    }
}