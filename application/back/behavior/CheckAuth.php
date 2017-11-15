<?php
/**
 * Created by PhpStorm.
 * User: cyl
 * Date: 2017/11/7
 * Time: 23:48
 */

namespace app\back\behavior;


use priv\Privilege;
use think\Session;

class CheckAuth
{
    public function run(& $params){
        $request = request();
        #特例跳过
        $except = [
            'admin' =>  ['login','captcha','...'],
            'othercontroller'   =>  ['']
        ];
        #先通过controller()获得控制器名看是否在特例except中通过下标判断
        if(isset($except[strtolower($request->controller())])){
            ##再判断是否在特例定义的数组里
            if (in_array($request->action(),$except[strtolower($request->controller())])){
                #在这里面说明是特例不用校验session值
                return;
            }
        }
        #走到这说明不是特例需要登录验证
        if (!Session::has('admin')){
            #说明未登录
            ##记录之前输入的URL
            Session::set('route',$request->path());
            redirect('back/admin/login')->send();
            ##################重点##################
            ##这里要是不die的话后面所做的动作的消息头header会将header(Location:url)重定向覆盖掉，导致重定向失败
            die;
        }
        #自动化校验授权
#$request->path()===========back/brand/index
        if (!\priv\Privilege::checkPriv($request->path())){
            #说明未授权
            redirect('back/admin/login',[],302,[
                'message'   =>  '没有权限',
            ])->send();
            die;

        }

    }
}