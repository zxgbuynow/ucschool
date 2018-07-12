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
            $save['q1'] = $param['q1'];
            $save['q2'] = $param['q2'];
            // $save['market'] = $param['market'];
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

    public function news()
    {
        if ($this->request->isAjax()) {
            $post = Request::instance()->param();
            $params['longitude'] = $post['longitude'];
            $params['latitude'] = $post['latitude'];
            $url = "http://api.map.baidu.com/geoconv/v1/?coords=".$params['longitude'].",".$params['latitude']."&from=1&to=5&ak=lDlfRKWEt1xLRyxgFE5RLONTx9ox42GI&mcode=BA:AD:09:3A:82:82:9F:B4:32:A7:B2:8C:B4:CC:F0:E9:F3:7D:AE:58;com.wangu.www";
            $url = "http://api.map.baidu.com/geocoder/v2/?location=".$params['latitude'].",".$params['longitude']."&output=json&ak=lDlfRKWEt1xLRyxgFE5RLONTx9ox42GI&mcode=BA:AD:09:3A:82:82:9F:B4:32:A7:B2:8C:B4:CC:F0:E9:F3:7D:AE:58;com.wangu.www";
            $resp = curlRequest($url,[]);
            $resp = json_decode($resp,true);
            if ($resp['status']==0) {
                //
                $s['address'] = $resp['result']['formatted_address'];
                // $s['create_time'] = time();
                db('postion')->insert($s);
                echo json_decode($resp['result']['formatted_address']);exit;
            }
            
        }
        return $this->fetch(); // 渲染模板
    }
}
