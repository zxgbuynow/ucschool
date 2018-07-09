<?php

namespace app\plug\home;


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
            if (db('plug_question')->where(['phone'=>$param['phone']])->find()) {
               $data = [
                    'info'=>'该店铺已提交过，请不要重复提交',
                    'status'=>'n',
                    'data'=>[]
                ];
                return json($data); 
            }
            //save
            $save['phone'] = $param['phone'];
            $save['shopname'] = $param['shopname'];
            $save['username'] = $param['username'];
            $save['location'] = $param['location'];
            $save['q1'] = $param['q1'];
            $save['q2'] = $param['q2'];
            $save['q3'] = $param['q3'];
            $save['q4'] = $param['q4'];
            $save['q5'] = $param['q5'];
            $save['q6'] = $param['q6'];
            $save['q7'] = $param['q7'];
            $save['q8'] = $param['q8'];
            $save['create_time'] = time();
            db('plug_question')->insert($save);
            $insertid = db('plug_question')->getLastInsID();
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
