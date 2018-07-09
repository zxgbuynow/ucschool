<?php


namespace app\common\behavior;

use app\admin\model\Config as ConfigModel;
use app\admin\model\Module as ModuleModel;

use app\shop\model\Config as SConfigModel;
use app\shop\model\Module as SModuleModel;

/**
 * 初始化配置信息行为
 * 将系统配置信息合并到本地配置
 * @package app\common\behavior
 * @author CaiWeiMing <314013107@qq.com>
 */
class Config
{
    /**
     * 执行行为 run方法是Behavior唯一的接口
     * @access public
     * @param mixed $params  行为参数
     * @return void
     */
    public function run(&$params)
    {
        // 如果是安装操作，直接返回
        if(defined('BIND_MODULE') && BIND_MODULE === 'install') return;

        // 获取当前模块名称
        $module = '';
        $dispatch = request()->dispatch();
        if (isset($dispatch['module'])) {
            $module = $dispatch['module'][0];
        }

        // 获取入口目录
        $base_file = request()->baseFile();
        $base_dir  = substr($base_file, 0, strripos($base_file, '/') + 1);
        define('PUBLIC_PATH', $base_dir);
        // 视图输出字符串内容替换
        $view_replace_str = [
            // 静态资源目录
            '__STATIC__'    => PUBLIC_PATH. 'static',
            // 文件上传目录
            '__UPLOADS__'   => PUBLIC_PATH. 'uploads',
            // JS插件目录
            '__LIBS__'      => PUBLIC_PATH. 'static/libs',
            // 后台CSS目录
            '__ADMIN_CSS__' => PUBLIC_PATH. 'static/admin/css',
            // 后台JS目录
            '__ADMIN_JS__'  => PUBLIC_PATH. 'static/admin/js',
            // 后台IMG目录
            '__ADMIN_IMG__' => PUBLIC_PATH. 'static/admin/img',
            // 后台CSS目录
            '__SHOP_CSS__' => PUBLIC_PATH. 'static/shop/css',
            // 后台JS目录
            '__SHOP_JS__'  => PUBLIC_PATH. 'static/shop/js',
            // 后台IMG目录
            '__SHOP_IMG__' => PUBLIC_PATH. 'static/shop/img',
            // 前台CSS目录
            '__HOME_CSS__'  => PUBLIC_PATH. 'static/home/css',
            // 前台JS目录
            '__HOME_JS__'   => PUBLIC_PATH. 'static/home/js',
            // 前台IMG目录
            '__HOME_IMG__'  => PUBLIC_PATH. 'static/home/img',

            // 前台CSS目录
            '__WALL_CSS__'  => PUBLIC_PATH. 'static/wall/css',
            // 前台JS目录
            '__WALL_JS__'   => PUBLIC_PATH. 'static/wall/js',
            // 前台IMG目录
            '__WALL_IMG__'  => PUBLIC_PATH. 'static/wall/img',

            // 前台CSS目录
            '__PLUG_CSS__'  => PUBLIC_PATH. 'static/plug/css',
            // 前台JS目录
            '__PLUG_JS__'   => PUBLIC_PATH. 'static/plug/js',
            // 前台IMG目录
            '__PLUG_IMG__'  => PUBLIC_PATH. 'static/plug/img',

            // mobile CSS目录
            '__M_CSS__'  => PUBLIC_PATH. 'static/mobile/css',
            // mobile JS目录
            '__M_JS__'   => PUBLIC_PATH. 'static/mobile/js',
            // mobile IMG目录
            '__M_IMG__'  => PUBLIC_PATH. 'static/mobile/img',
            // mobile fonts目录
            '__M_FONT__'  => PUBLIC_PATH. 'static/mobile/fonts',

            // counsellor CSS目录
            '__C_CSS__'  => PUBLIC_PATH. 'static/counsellor/css',
            // mobile JS目录
            '__C_JS__'   => PUBLIC_PATH. 'static/counsellor/js',
            // mobile IMG目录
            '__C_IMG__'  => PUBLIC_PATH. 'static/counsellor/img',
            // mobile fonts目录
            '__C_FONT__'  => PUBLIC_PATH. 'static/counsellor/fonts',

            // 表单项扩展目录
            '__EXTEND_FORM__' => $base_dir.'extend/form'
        ];
        config('view_replace_str', $view_replace_str);

        // 如果定义了入口为admin，则修改默认的访问控制器层
        if(defined('ENTRANCE') && ENTRANCE == 'admin') {
            define('ADMIN_FILE', substr($base_file, strripos($base_file, '/') + 1));

            if ($dispatch['type'] == 'module' && $module == '') {
                header("Location: ".$base_file.'/admin', true, 302);exit();
            }

            if ($module != '' && !in_array($module, config('module.default_controller_layer'))) {
                // 修改默认访问控制器层
                config('url_controller_layer', 'admin');
                // 修改视图模板路径
                config('template.view_path', APP_PATH. $module. '/view/admin/');
            }

            // 插件静态资源目录
            config('view_replace_str.__PLUGINS__', '/plugins');
        } else {
            if ($dispatch['type'] == 'module' && $module == 'admin') {
                header("Location: ".$base_dir.ADMIN_FILE.'/admin', true, 302);exit();
            }

            if ($module != '' && !in_array($module, config('module.default_controller_layer'))) {
                // 修改默认访问控制器层
                config('url_controller_layer', 'home');
            }
        }

        // 定义模块资源目录
        config('view_replace_str.__MODULE_CSS__', PUBLIC_PATH. 'static/'. $module .'/css');
        config('view_replace_str.__MODULE_JS__', PUBLIC_PATH. 'static/'. $module .'/js');
        config('view_replace_str.__MODULE_IMG__', PUBLIC_PATH. 'static/'. $module .'/img');
        config('view_replace_str.__MODULE_LIBS__', PUBLIC_PATH. 'static/'. $module .'/libs');
        // 静态文件目录
        config('public_static_path', PUBLIC_PATH. 'static/');

        // 读取系统配置
        $system_config = cache('system_config');
        if (!$system_config) {
            $ConfigModel   = new ConfigModel();
            $system_config = $ConfigModel->getConfig();
            // 所有模型配置
            $module_config = ModuleModel::where('config', 'neq', '')->column('config', 'name');
            foreach ($module_config as $module_name => $config) {
                $system_config[strtolower($module_name).'_config'] = json_decode($config, true);
            }
            // 非开发模式，缓存系统配置
            if ($system_config['develop_mode'] == 0) {
                cache('system_config', $system_config);
            }
        }
        // 设置配置信息
        config($system_config);
    }
}
