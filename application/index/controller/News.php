<?php


namespace app\index\controller;

/**
 * News控制器
 * @package app\index\controller
 */
class News extends Home
{
    public function index()
    {
       return $this->fetch(); // 渲染模板
       
    }
}
