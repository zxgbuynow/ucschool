<?php

namespace app\index\controller;

use \think\Request;
use \think\Db;
use think\Model;

/**
 * 前台首页控制器
 * @package app\index\controller
 */
class Index extends Home
{
    public function index()
    {
       return $this->fetch(); // 渲染模板
    }


    public function question()
    {
        return $this->fetch(); // 渲染模板
    }

    public function info()
    {
        if (Request::instance()->isAjax()) {
            $param = Request::instance()->param();
            if (db('question')->where(['phone'=>$param['phone']])->find()) {
               $data = [
                    'info'=>'该店铺已提交过，请不要重复提交',
                    'status'=>'n',
                    'data'=>[]
                ];
                return json($data); 
            }
            //save
            $save['phone'] = $param['phone'];
            $save['username'] = $param['username'];
            $save['shopname'] = $param['shopname'];
            $save['location'] = $param['location'];
            $save['market'] = $param['market'];
            $save['create_time'] = time();

            db('question')->insert($save);
            $insertid = db('question')->getLastInsID();
            $data = [
                'info'=>'提交成功',
                'status'=>'y',
                'data'=>['saveid'=>$insertid]
            ];
            return json($data);
        }
        return $this->fetch(); // 渲染模板
    }   

    public function end()
    {
        return $this->fetch(); // 渲染模板
    } 
}
