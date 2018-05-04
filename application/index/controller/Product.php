<?php


namespace app\index\controller;

/**
 * 产品控制器
 * @package app\index\controller
 */
class Product extends Home
{
    public function index()
    {
       return $this->fetch(); // 渲染模板
       
    }
    public function detail()
    {
       return $this->fetch(); // 渲染模板
       
    }
}
