<?php


namespace App\Http;


use App\Http\EmptyController;

class Index  extends EmptyController
{

    /**
     * Undocumented function
     * 默认路由页面
     * @return void
     */
    public function index()
    {
        //如果是接口服务器，把此处打开，不能直接访问index
        // return $this->response()->withStatus(404);

        $file = EASYSWOOLE_ROOT . '/vendor/easyswoole/easyswoole/src/Resource/Http/welcome.html';
        if (!is_file($file)) {
            $file = EASYSWOOLE_ROOT . '/src/Resource/Http/welcome.html';
        }
        $this->response()->write(file_get_contents($file));
    }
}
