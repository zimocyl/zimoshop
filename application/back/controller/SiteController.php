<?php
/**
 * Created by PhpStorm.
 * User: cyl
 * Date: 2017/11/7
 * Time: 23:16
 */

namespace app\back\controller;


use think\Controller;

class SiteController extends Controller
{

    public function indexAction(){
        return  $this->fetch();
    }
}