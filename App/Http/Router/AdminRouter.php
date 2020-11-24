<?php


namespace App\Http\Router;

use EasySwoole\Http\AbstractInterface\AbstractRouter;

class AdminRouter extends AbstractRouter
{

    protected $filePath = '/Admin/Controller';

    function initialize($routeCollector)
    {
        //定义后台入口
        $routeCollector->get('/admin', $this->filePath . '/Index/login');
        
        $routeCollector->addGroup('/admin', function ($routeCollector) {
            //Index控制器
            $routeCollector->addGroup('/index', function ($routeCollector) {
                $routeCollector->get('/login', $this->filePath . '/Index/login');
                $routeCollector->get('/queue/{id:[0-9]+}/{secret:[a-z_]+}', $this->filePath . '/Index/queue');
            });

            //Login控制器
            $routeCollector->addGroup('/login', function ($routeCollector) {
                $routeCollector->post('/login', $this->filePath . '/Login/login');
                $routeCollector->post('/getVerifyCode', $this->filePath . '/Login/getVerifyCode');
                $routeCollector->post('/logout', $this->filePath . '/Login/logout');
            });

            //User控制器
            $routeCollector->addGroup('/user', function ($routeCollector) {
                $routeCollector->post('/getUserInfo', $this->filePath . '/User/getUserInfo');
                $routeCollector->post('/lists', $this->filePath . '/User/index');
                $routeCollector->post('/add', $this->filePath . '/User/add');
                $routeCollector->post('/edit', $this->filePath . '/User/edit');
                $routeCollector->post('/getInfo', $this->filePath . '/User/getInfo');
                $routeCollector->post('/status', $this->filePath . '/User/status');
                $routeCollector->post('/del', $this->filePath . '/User/del');
                $routeCollector->post('/setPwd', $this->filePath . '/User/setPwd');
                $routeCollector->post('/setUserInfo', $this->filePath . '/User/setUserInfo');
                $routeCollector->post('/updatePwd', $this->filePath . '/User/updatePwd');
            });

            //Menu控制器
            $routeCollector->addGroup('/menu', function ($routeCollector) {
                $routeCollector->post('/getMenuRouter', $this->filePath . '/Menu/getMenuRouter');
                $routeCollector->post('/getCategory', $this->filePath . '/Menu/getCategory');
                $routeCollector->post('/getList', $this->filePath . '/Menu/getList');
                $routeCollector->post('/getInfo', $this->filePath . '/Menu/getInfo');
                $routeCollector->post('/add', $this->filePath . '/Menu/add');
                $routeCollector->post('/edit', $this->filePath . '/Menu/edit');
                $routeCollector->post('/status', $this->filePath . '/Menu/status');
                $routeCollector->post('/appointField', $this->filePath . '/Menu/appointField');
                $routeCollector->post('/del', $this->filePath . '/Menu/del');
            });

            //Tpl控制器
            $routeCollector->addGroup('/tpl', function ($routeCollector) {
                $routeCollector->post('/lists', $this->filePath . '/Tpl/index');
                $routeCollector->post('/getCategory', $this->filePath . '/Tpl/getCategory');
                $routeCollector->post('/add', $this->filePath . '/Tpl/add');
                $routeCollector->post('/edit', $this->filePath . '/Tpl/edit');
                $routeCollector->post('/getInfo', $this->filePath . '/Tpl/getInfo');
                $routeCollector->post('/status', $this->filePath . '/Tpl/status');
                $routeCollector->post('/del', $this->filePath . '/Tpl/del');
                $routeCollector->post('/copy', $this->filePath . '/Tpl/copy');
                $routeCollector->post('/getAlias', $this->filePath . '/Tpl/getAlias');
            });


            //QiniuConfig控制器
            $routeCollector->addGroup('/qiniuConfig', function ($routeCollector) {
                $routeCollector->post('/lists', $this->filePath . '/QiniuConfig/index');
                $routeCollector->post('/add', $this->filePath . '/QiniuConfig/add');
                $routeCollector->post('/edit', $this->filePath . '/QiniuConfig/edit');
                $routeCollector->post('/getInfo', $this->filePath . '/QiniuConfig/getInfo');
                $routeCollector->post('/status', $this->filePath . '/QiniuConfig/status');
                $routeCollector->post('/del', $this->filePath . '/QiniuConfig/del');
            });

            //Group控制器
            $routeCollector->addGroup('/group', function ($routeCollector) {
                $routeCollector->post('/lists', $this->filePath . '/Group/index');
                $routeCollector->post('/getMenuList', $this->filePath . '/Group/getMenuList');
                $routeCollector->post('/add', $this->filePath . '/Group/add');
                $routeCollector->post('/edit', $this->filePath . '/Group/edit');
                $routeCollector->post('/getInfo', $this->filePath . '/Group/getInfo');
                $routeCollector->post('/status', $this->filePath . '/Group/status');
                $routeCollector->post('/del', $this->filePath . '/Group/del');
                $routeCollector->post('/getCategory', $this->filePath . '/Group/getCategory');
                $routeCollector->post('/getUserPower', $this->filePath . '/Group/getUserPower');
                $routeCollector->post('/setPower', $this->filePath . '/Group/setPower');
            });


            //ToolsQueue控制器
            $routeCollector->addGroup('/toolsQueue', function ($routeCollector) {
                $routeCollector->post('/lists', $this->filePath . '/ToolsQueue/index');
                $routeCollector->post('/add', $this->filePath . '/ToolsQueue/add');
                $routeCollector->post('/edit', $this->filePath . '/ToolsQueue/edit');
                $routeCollector->post('/getInfo', $this->filePath . '/ToolsQueue/getInfo');
                $routeCollector->post('/del', $this->filePath . '/ToolsQueue/del');
            });


            //FriendLink控制器
            $routeCollector->addGroup('/friendLink', function ($routeCollector) {
                $routeCollector->post('/lists', $this->filePath . '/FriendLink/index');
                $routeCollector->post('/add', $this->filePath . '/FriendLink/add');
                $routeCollector->post('/edit', $this->filePath . '/FriendLink/edit');
                $routeCollector->post('/getInfo', $this->filePath . '/FriendLink/getInfo');
                $routeCollector->post('/del', $this->filePath . '/FriendLink/del');
                $routeCollector->post('/status', $this->filePath . '/FriendLink/status');
                $routeCollector->post('/appointField', $this->filePath . '/FriendLink/appointField');
            });


            //Version控制器
            $routeCollector->addGroup('/version', function ($routeCollector) {
                $routeCollector->post('/lists', $this->filePath . '/Version/index');
                $routeCollector->post('/getCategory', $this->filePath . '/Version/getCategory');
                $routeCollector->post('/add', $this->filePath . '/Version/add');
                $routeCollector->post('/edit', $this->filePath . '/Version/edit');
                $routeCollector->post('/getInfo', $this->filePath . '/Version/getInfo');
                $routeCollector->post('/del', $this->filePath . '/Version/del');
                $routeCollector->post('/status', $this->filePath . '/Version/status');
                $routeCollector->post('/appointField', $this->filePath . '/Version/appointField');
            });


            //Upload控制器
            $routeCollector->addGroup('/upload', function ($routeCollector) {
                $routeCollector->post('/getUplodeToken', $this->filePath . '/Upload/getUplodeToken');
                $routeCollector->post('/getImagePath', $this->filePath . '/Upload/getImagePath');
                $routeCollector->post('/getPrivateImagePath', $this->filePath . '/Upload/getPrivateImagePath');
            });


            //Cover控制器
            $routeCollector->addGroup('/cover', function ($routeCollector) {
                $routeCollector->post('/lists', $this->filePath . '/Cover/index');
                $routeCollector->post('/del', $this->filePath . '/Cover/del');
            });


            //Log控制器
            $routeCollector->addGroup('/log', function ($routeCollector) {
                $routeCollector->post('/lists', $this->filePath . '/Log/index');
                $routeCollector->post('/getInfo', $this->filePath . '/Log/getInfo');
                $routeCollector->post('/del', $this->filePath . '/Log/del');
                $routeCollector->post('/clearAll', $this->filePath . '/Log/clearAll');
            });

            //PlugCategory控制器
            $routeCollector->addGroup('/plugCategory', function ($routeCollector) {
                $routeCollector->post('/getList', $this->filePath . '/PlugCategory/getList');
                $routeCollector->post('/getCategory', $this->filePath . '/PlugCategory/getCategory');
                $routeCollector->post('/add', $this->filePath . '/PlugCategory/add');
                $routeCollector->post('/edit', $this->filePath . '/PlugCategory/edit');
                $routeCollector->post('/getInfo', $this->filePath . '/PlugCategory/getInfo');
                $routeCollector->post('/del', $this->filePath . '/PlugCategory/del');
                $routeCollector->post('/status', $this->filePath . '/PlugCategory/status');
                $routeCollector->post('/appointField', $this->filePath . '/PlugCategory/appointField');
            });

            //Plug控制器
            $routeCollector->addGroup('/plug', function ($routeCollector) {
                $routeCollector->post('/lists', $this->filePath . '/Plug/index');
                $routeCollector->post('/getCategory', $this->filePath . '/Plug/getCategory');
                $routeCollector->post('/add', $this->filePath . '/Plug/add');
                $routeCollector->post('/edit', $this->filePath . '/Plug/edit');
                $routeCollector->post('/getInfo', $this->filePath . '/Plug/getInfo');
                $routeCollector->post('/del', $this->filePath . '/Plug/del');
                $routeCollector->post('/status', $this->filePath . '/Plug/status');
                $routeCollector->post('/appointField', $this->filePath . '/Plug/appointField');
            });

            //PlugVersion控制器
            $routeCollector->addGroup('/plugVersion', function ($routeCollector) {
                $routeCollector->post('/lists', $this->filePath . '/PlugVersion/index');
                $routeCollector->post('/add', $this->filePath . '/PlugVersion/add');
                $routeCollector->post('/edit', $this->filePath . '/PlugVersion/edit');
                $routeCollector->post('/getInfo', $this->filePath . '/PlugVersion/getInfo');
                $routeCollector->post('/del', $this->filePath . '/PlugVersion/del');
            });

            $routeCollector->addGroup('/config', function ($routeCollector) {
                $routeCollector->post('/userType', $this->filePath . '/Config/userType');
                $routeCollector->post('/platformTypeList', $this->filePath . '/Config/platformTypeList');
            });

            //Addons控制器
            $routeCollector->addGroup('/addons', function ($routeCollector) {
                $routeCollector->post('/getAddonsLists', $this->filePath . '/Addons/getAddonsLists');
                $routeCollector->post('/installAddons', $this->filePath . '/Addons/installAddons');
                $routeCollector->post('/unstallAddons', $this->filePath . '/Addons/unstallAddons');
            });
        });
    }
}
