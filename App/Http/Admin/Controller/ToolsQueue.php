<?php


namespace App\Http\Admin\Controller;


use App\Http\Admin\Model\ToolsQueueModel;

class ToolsQueue extends Admin
{
    protected $validateName = '\App\Http\Admin\Validate\ToolsQueueValidate';

    //不需要验证权限
    protected $noNeedRule = [];

    protected $model = ToolsQueueModel::class;
}
