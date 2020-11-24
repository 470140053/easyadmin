<?php


namespace App\Http;


use EasySwoole\Http\AbstractInterface\AbstractRouter;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use FastRoute\RouteCollector;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class Router extends AbstractRouter
{

    /**
     * Undocumented function
     * 路由设置
     * @param RouteCollector $routeCollector
     * @return void
     */
    function initialize(RouteCollector $routeCollector)
    {
        $source = EASYSWOOLE_ROOT . '/App/Http/Router/';
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($iterator as $item) {
            if (strpos($item->getFileName(), '.php') !== false) {
                $class = '\\App\\Http\\Router\\' . str_replace('.php', '', $item->getFileName());
                (new $class())->initialize($routeCollector);
            }
        }
    }
}
