<?php
require_once __DIR__ . '/../autoloader.php';
use phpspider\core\phpspider;
use phpspider\core\requests;
use phpspider\core\db;
use phpspider\core\selector;
use phpspider\core\log;

/* Do NOT delete this comment */
/* 不要删除这段注释 */

$configs = array(
    'name' => 'gushi365',
    //'tasknum' => 8,
    'log_show' => false,
    'save_running_state' => false,
    'domains' => array(
        'www.gushi365.com'
    ),
    'scan_urls' => array(
        "http://www.gushi365.com/ertonggushi",
    ),
    'list_url_regexes' => array(
    ),
    'content_url_regexes' => array(
        "http://www.gushi365.com/info/\d+.html",
    ),
    'export' => array(
        'type' => 'db', 
        'table' => 'dp_story_temp',
    ),
    'db_config' => array(
        'host'  => '127.0.0.1',
        'port'  => 3306,
        'user'  => 'root',
        'pass'  => '',
        'name'  => 'bullqt',
    ),
    'fields' => array(
        // 标题
        array(
            'name' => "title",
            'selector' => "//*[@id='main']/article/header/h1",
            'required' => true,
        ),
        // 分类
        array(
            'name' => "cate",
            'selector' => "//*[@id='page']/nav/a[2]",
            'required' => true,
        ),
        // 发布时间
        array(
            'name' => "create_time",
            'selector' => "",
            //'required' => true,
        ),
        // 转入平台
        array(
            'name' => "source",
            'selector' => "",
            //'required' => true,
        ),
        // 图片
        array(
            'name' => "pic",
            'selector' => "//*[@id='main']/article/div/div[1]/p[2]/img",
            // 'required' => true,
        ),
        // content
        array(
            'name' => "content",
            'selector' => "//*[@id='main']/article/div/div[1]",
            'required' => true,
        ),
    ),
);

$spider = new phpspider($configs);

$spider->on_start = function($phpspider)
{
    $db_config = $phpspider->get_config("db_config");
    // 数据库连接
    db::set_connect('default', $db_config);
    db::init_mysql();

    for ($i = 2; $i <= 3; $i++) 
    {
        $url = "http://www.gushi365.com/ertonggushi/index_{$i}.html";
        $phpspider->add_scan_url($url);
    }
};

$spider->on_extract_field = function($fieldname, $data, $page) 
{
    if ($fieldname == 'source') 
    {
        $data = '故事365';
    }
    elseif ($fieldname == 'create_time') 
    {
        $data = time();
    }
    // elseif ($fieldname == 'content') 
    // {
    //     $data = selector::remove($data, "//*[@id='main']/article/div/div[1]/div/span");
    // }
    return $data;
};

$spider->on_extract_page = function($page, $data)
{
    if (!$data['title']) {
        return false;
    }

    $data['content'] = trim($data['content']);
    $data['content'] = str_replace("/d/js/acmsd/thea5.js","",$data['content']);
    $sql = "Select Count(*) As `count` From `dp_story_temp` Where `title`= '{$data['title']}'";
    $row = db::get_one($sql);
    // log::warn($sql);
    if ($row['count']==0) 
    {
        db::insert("dp_story_temp", $data);
    }

    return $data;
};

$spider->start();
