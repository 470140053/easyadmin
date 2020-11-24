<?php


namespace App\Http\Router;

use EasySwoole\Http\AbstractInterface\AbstractRouter;

class MemberRouter extends AbstractRouter
{

    protected $filePath = '/Addons/User/Controller/Admin';
    
    function initialize($routeCollector)
    {
        $routeCollector->addGroup('/admin', function ($routeCollector) {
            //User控制器
            $routeCollector->addGroup('/member', function ($routeCollector) {
                $routeCollector->post('/lists', $this->filePath . '/User/index');
                $routeCollector->post('/add', $this->filePath . '/User/add');
                $routeCollector->post('/edit', $this->filePath . '/User/edit');
                $routeCollector->post('/getInfo', $this->filePath . '/User/getInfo');
                $routeCollector->post('/del', $this->filePath . '/User/del');
                $routeCollector->post('/status', $this->filePath . '/User/status');
                $routeCollector->post('/setPwd', $this->filePath . '/User/setPwd');
                $routeCollector->post('/setFrozen', $this->filePath . '/User/setFrozen');
            });

            $routeCollector->addGroup('/memberWallet', function ($routeCollector) {
                $routeCollector->post('/getInfo', $this->filePath . '/UserWallet/getInfo');
                $routeCollector->post('/setBalance', $this->filePath . '/UserWallet/setBalance');
            });
            
        });
    }
}