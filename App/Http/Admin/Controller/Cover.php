<?php


namespace App\Http\Admin\Controller;


use App\Http\Admin\Model\CoverModel;

class Cover extends Admin
{
    protected $validateName = '\App\Http\Admin\Validate\CoverValidate';

    //不需要验证权限
    protected $noNeedRule = [];

    protected $model = CoverModel::class;

    //需要写入后台操作日志记录的方法，对应路由解释
    protected $logActionRoute = [
        'Cover'   => '附件资源',
        'lists'  => '列表',
        'del'    => '删除',
    ];


    protected function _del($id)
    {

        (new \App\Common\QiNiuUpload())->deleteUrl($id);

        $result = $this->model::deleteInfoById($id);

        return $result;
    }
}
