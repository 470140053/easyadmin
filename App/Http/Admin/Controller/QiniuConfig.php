<?php


namespace App\Http\Admin\Controller;


use App\Http\Admin\Model\QiniuConfigModel;

class QiniuConfig extends Admin
{
    protected $validateName = '\App\Http\Admin\Validate\QiniuConfigValidate';

    //不需要验证权限
    protected $noNeedRule = [];

    protected $model = QiniuConfigModel::class;

    //需要写入后台操作日志记录的方法，对应路由解释
    protected $logActionRoute = [
        'QiniuConfig'   => '七牛云配置',
        'lists'  => '列表',
        'add'    => '添加',
        'edit'   => '编辑',
        'status' => '更新状态',
        'del'    => '删除',
    ];
}
