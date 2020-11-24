<?php


namespace App\Http;


use EasySwoole\Http\AbstractInterface\Controller;

class EmptyController extends Controller
{

    /**
     * Undocumented function
     * 此方法为路由找不到对应方法时的展示页面，可自行修改编辑。不要删除此方法
     * @param string|null $action
     * @return void
     */
    protected function actionNotFound(?string $action)
    {

        $this->response()->withStatus(404);
        $file = EASYSWOOLE_ROOT . '/vendor/easyswoole/easyswoole/src/Resource/Http/404.html';
        if (!is_file($file)) {
            $file = EASYSWOOLE_ROOT . '/src/Resource/Http/404.html';
        }
        $this->response()->write(file_get_contents($file));
    }
}
