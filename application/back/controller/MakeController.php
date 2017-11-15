<?php
/**
 * Created by PhpStorm.
 * User: cyl
 * Date: 2017/11/6
 * Time: 18:58
 */
namespace app\back\controller;
use think\Config;
use think\Db;
use think\Controller;
class MakeController extends Controller
{
    protected $input = [];
    protected $label = [];
    /**
     * 表的相关配置
     */
    public function tableAction()
    {

        return $this->fetch();
    }
    /**
     * 表信息
     */
    public function infoAction()
    {
        $table = input('table');
        # 获取表的commet
        $sql = 'select table_comment from information_schema.tables where table_schema=? and table_name=?';
        $table_schema = Config::get('database.database');
        $table_name = Config::get('database.prefix') . $table;
        $rows_table = Db::query($sql, [$table_schema, $table_name]);
        //$rows 二维数组

        # 获取表的字段
        $sql = 'select column_name,column_type, column_comment from information_schema.columns where table_schema=? and table_name=?';
        $rows_field = Db::query($sql, [$table_schema, $table_name]);

        // 响应数据
        return [
            'table_schema' => $table_schema,
            'table' => $table,
            'table_name' => $table_name,
            'comment' => $rows_table[0]['table_comment'],
            'fields' => $rows_field,
        ];
    }
    /**
     * 生成控制器、模型、验证器、视图
     */
    public function generateAction(){
        if (!request()->isPost()){
            return $this->redirect('table');
        }
        #获取请求数据
        ##$fields是二维数组
        $this->input['table'] = input('table');
        $this->input['comment'] = input('comment');
        $this->input['fields'] = input('fields/a');
        ##因为代码太多，所以分开写
        # 生成控制器
        $this->createController();
        # 生成模型
        $this->createModel();
        # 生成验证器
        $this->createValidate();
        # 生成index视图
        $this->createViewIndex();
        # 生成set视图
        $this->createViewSet();
        #生成权限
        $this->createAction();
    }
    /**
     * 生成控制器
     */
    protected function createController()
    {
        # 整理需要的数据
        ## 模型名
        $model = $this->mkModel();
        ## 控制器名
        $controller = $this->mkController();
        ## 验证器名
        $validate = $this->mkValidate();

        ## 字段搜索
        $where_list = '';
        ### 读取字段搜索子模板
        $template = file_get_contents(APP_PATH . 'back/code/indexWhere.php');
        ### 遍历字段，找到需要搜索的，使用子模板生成
        foreach($this->input['fields'] as $field) {
            ### 不需要搜索
            if (! isset($field['search'])) continue;
            ### 需要搜索的字段
            $search = ['%field%'];
            $replace = [$field['name']];
            ### 执行替换
            $where_list .= str_replace($search, $replace, $template);
        }

        # 执行替换
        $template = file_get_contents(APP_PATH . 'back/code/controller.php');
        $search = ['%model%', '%controller%', '%validate%', '%where_list%', '%table%', '%date%', '%time%'];

        $replace = [$model, $controller, $validate, $where_list, $this->input['table'], date('Y/m/d'), date('H:i')];
        $content = str_replace($search, $replace, $template);

        # 写入控制器文件
        $file = APP_PATH . 'back/controller/' . $controller . '.php';
        file_put_contents($file, $content);

        echo $file.'生成完毕！','<br>';
    }
    /**
     * 生成模型
     *
     */
    protected function createModel(){
        #模型名
        $model = $this->mkModel();
        #模板替换
        #执行替换
        $template = APP_PATH . 'back/code/model.php';
        $search = ['%model%','%date%','%time%'];
        $replace = [$model, date('Y/m/d'), date('H:i:s')];
        # 写入控制器文件
        $file = APP_PATH . 'back/model/' . $model . '.php';
        $this->replace($template,$search,$replace,$file);
        echo '模型'.$file.'生成完毕！','<br>';


    }
    /**
     * 生成验证器
     */
    protected function createValidate()
    {
        #需要的数据
        $validate = $this->mkValidate();
        ##字段标签对应code/vilidate.php模板的%label_list%
        $label_list = '';
        ###字段标签子模板
        $template=file_get_contents(APP_PATH.'back/code/validateField.php');
        ###处理每个字段--code/validateField里的循环写入
        foreach ($this->input['fields'] as $field)
        {
            $search = ['%field%','%label%'];
            $replace = [$field['name'],$field['comment']];
            $label_list.=str_replace($search,$replace,$template);
        }

        #替换，写入
        $template = APP_PATH.'back/code/validate.php';
        $search = ['%validate%','%label_list%', '%date%', '%time%'];
        $replace = [$validate, $label_list, date('Y/m/d'), date('H:i:s')];
        $file = APP_PATH . 'back/validate/' . $validate . '.php';
        $this->replace($template, $search, $replace, $file);
        echo '验证器: ' , $file , ' 生成完毕!','<br>';
    }

    /**
     * 生成index视图
     */
    protected function createViewIndex()
    {
        # 整理数据
        ## 搜索字段初始化
        $search_field_list = '';
        $template_search = file_get_contents(APP_PATH . 'back/code/viewIndexSearchField.php');
        ## 数据表头字段初始化
        $table_head_list = '';
        $template_head = file_get_contents(APP_PATH . 'back/code/viewIndexTableHead.php');
        $template_head_order = file_get_contents(APP_PATH . 'back/code/viewIndexTableHeadOrder.php');
        ## 数据字段内容初始化
        $table_data_list = '';
        $template_data = file_get_contents(APP_PATH . 'back/code/viewIndexTableData.php');
        ## 列数统计
        $column_number = 2;
        foreach($this->input['fields'] as $field) {
            ## 搜索字段
            if (isset($field['search'])) {
                $search = ['%field%', '%label%'];
                $replace = [$field['name'], $field['comment']];
                $search_field_list .= str_replace($search, $replace, $template_search);
            }

            if (isset($field['index'])) {
                ## 列表排序字段
                if (isset($field['order'])) {
                    ### 排序
                    $template_table_head = $template_head_order;
                } else {
                    ### 不需要排序
                    $template_table_head = $template_head;
                }
                $search = ['%field%', '%label%'];
                $replace = [$field['name'], $field['comment']];
                $table_head_list .= str_replace($search, $replace, $template_table_head);

                ## 数据内容
                $search = ['%field%'];
                $replace = [$field['name']];
                $table_data_list .= str_replace($search, $replace, $template_data);

                ## 累加列计数器
                ++$column_number;
            }
        }

        # 替换，写入
        $template = APP_PATH . 'back/code/viewIndex.php';
        $search = ['%table_title%', '%search_field_list%', '%table_head_list%', '%table_data_list%', '%column_number%'];
        $replace = [$this->input['comment'], $search_field_list, $table_head_list, $table_data_list, $column_number];
        $sub = $this->input['table'];
        $file = APP_PATH . 'back/view/' . $sub . '/index.html';
        $this->replace($template, $search, $replace, $file);

        echo '视图index: ' , $file , ' 生成完毕', '<br>';


    }
    /**
     * 生成set视图
     */
    protected function createViewSet()
    {
        # 整理数据
        $form_field_list = '';
        $template_field = file_get_contents(APP_PATH . 'back/code/viewSetFormField.php');
        foreach($this->input['fields'] as $field) {
            if (!isset($field['set'])) continue;

            $search = ['%field%', '%label%', '%required_class%'];
            $replace = [$field['name'], $field['comment'], isset($field['require'])?'required':''];
            $form_field_list .= str_replace($search, $replace, $template_field);
        }

        # 替换，写入
        $template = APP_PATH . 'back/code/viewSet.php';
        $search = ['%table_title%', '%form_field_list%'];
        $replace = [$this->input['comment'], $form_field_list];
        $sub = $this->input['table'];
        $file = APP_PATH . 'back/view/' . $sub . '/set.html';
        $this->replace($template, $search, $replace, $file);

        echo '视图set: ' , $file , ' 生成完毕', '<br>';
    }
    /**
     * 封装替换方法
     * @param  $template--模板文件
     * @param $search--搜索的需要替换的字符
     * @param $replace--替换成的字符
     * @param $file--要写入的模型位置
     * @param bool|int
     */
    public function replace($template,$search,$replace,$file)
    {
        $template_content = file_get_contents($template);
        $content = str_replace($search, $replace, $template_content);
        # 如果目录不存在，则创建
        // 从文件地址提取目录部分
        $path = dirname($file);
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        #写入文件
        file_put_contents($file, $content);

    }

    /**
     * 生成模型名称 user---User  user_log---UserLog
     */
    protected function mkModel()
    {
        // table => model
        // user =>  User
        // user_log => UserLog

        // 重复调用，仅仅生成一次即可
        if (!isset($this->label['model'])) {
            // 下划线分割表名，单词首字母大写，连接

            $this->label['model'] = implode(array_map('ucfirst', explode('_', $this->input['table'])));

        }
        return $this->label['model'];
    }

    /**
     * 生成控制器名称 kang_brand---BrandController
     */
    protected function mkController()
    {
        // 重复调用，仅仅生成一次即可
        if (!isset($this->label['controller'])) {
            // 下划线分割表名，单词首字母大写，连接
            $this->label['controller'] = implode(array_map('ucfirst', explode('_', $this->input['table']))) . 'Controller';

        }
        return $this->label['controller'];
    }
    /**
     * 生成权限不带controller的权限名
     */
    protected function mkActionName(){
        // 重复调用，仅仅生成一次即可
        if (!isset($this->label['controller'])) {
            // 下划线分割表名，单词首字母大写，连接
            $this->label['controller'] = implode(array_map('ucfirst', explode('_', $this->input['table'])));

        }
        return $this->label['controller'];
    }
    /**
     * 生成验证器名称 brand---BrandValidate
     */
    protected function mkValidate()
    {
        // 重复调用，仅仅生成一次即可
        if (!isset($this->label['validate'])) {
            // 下划线分割表名，单词首字母大写，连接
            $this->label['validate'] = implode(array_map('ucfirst', explode('_', $this->input['table']))) . 'Validate';

        }
        return $this->label['validate'];
    }
    /**
     * 生成对应的权限
     */
    protected function createAction(){
        $data = [
            ['id'=>null,'title'=>$this->input['comment'].'列表','rule'=>'back/'.substr(strtolower($this->mkController()),0,-10).'/index','description'=>'','sort'=>0,'create_time'=>time(),'update_time'=>time()],
            ['id'=>null,'title'=>$this->input['comment'].'设置','rule'=>'back/'.substr(strtolower($this->mkController()),0,-10).'/set','description'=>'','sort'=>0,'create_time'=>time(),'update_time'=>time()],
            ['id'=>null,'title'=>$this->input['comment'].'批量操作','rule'=>'back/'.substr(strtolower($this->mkController()),0,-10).'/multi','description'=>'','sort'=>0,'create_time'=>time(),'update_time'=>time()],

            ];
        $result = Db::name('action')->insertAll($data);
        if ($result){
            echo '权限生成成功！';
        }

    }
}