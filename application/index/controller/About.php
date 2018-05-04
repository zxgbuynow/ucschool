<?php


namespace app\index\controller;

/**
 * About控制器
 * @package app\index\controller
 */
class About extends Home
{
    public function index()
    {
       return $this->fetch(); // 渲染模板
       
    }
}
