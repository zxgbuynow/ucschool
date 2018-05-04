<?php


namespace app\index\controller;

/**
 * 会员控制器
 * @package app\index\controller
 */
class Member extends Home
{
    public function index()
    {
       exit('Member');
       
    }
    /*
    *登录
    */
    public function login()
    {
        return $this->fetch(); // 渲染模板
    }

    /*
    *注册
    */
   public function register()
   {
        return $this->fetch(); // 渲染模板
   }
   /*
    *忘记密码
    */
   public function modifyPwdFirst()
   {
        return $this->fetch(); // 渲染模板
   }
}
