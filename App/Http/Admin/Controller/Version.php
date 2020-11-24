<?php

namespace App\Http\Admin\Controller;

use App\Http\Admin\Model\VersionModel;

class Version  extends Admin
{
    protected $validateName = '\App\Http\Admin\Validate\VersionValidate';

    //不需要验证权限
    protected $noNeedRule = ['getCategory'];

    protected $model = VersionModel::class;

    //需要写入后台操作日志记录的方法，对应路由解释
    protected $logActionRoute = [
        'version' => '版本控制',
        'lists'  => '列表',
        'add'    => '添加',
        'edit'   => '编辑',
        'status' => '更新状态',
        'del'    => '删除',
    ];
}
