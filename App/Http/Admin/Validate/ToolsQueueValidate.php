<?php

namespace App\Http\Admin\Validate;

use think\Validate;

/**
 * 设置系统菜单
 */
class ToolsQueueValidate  extends Validate
{

    protected $rule = [
        'app_id'                           => 'require',
        'title'                            => 'require',
        'class'                            => 'require',
        'args'                             => 'require',
        'time'                             => 'require',
        'status'                           => 'require|is:number|in:0,1,2,3',
        'queue_id'                         => 'require|is:number',
        'page'                             => 'is:number',
        'total'                            => 'is:number'
    ];

    protected $message = [
        'app_id.require'                 => '项目ID不能为空|20001',
        'title.require'                  => '友情链接名称不能为空|20006',
        'class.require'                  => '执行类不能为空|20006',
        'args.require'                   => '执行参数不能为空|20006',
        'time.require'                   => '执行时间不能为空|20006',
        'queue_id.require'               => 'ID不能为空|20006',
        'queue_id.is'                    => 'ID格式不正确|20007',
        'status.require'                 => '数据状态参数不能为空|20008',
        'status.is'                      => '数据状态参数格式不对|20009',
        'status.in'                      => '数据状态参数只能是0-3之间|20010',
        'page.is'                        => '页码不能为空|20011',
        'total.is'                       => '每页条数不能为空|20012',
    ];


    protected $scene = [
        'add'                       => ['class', 'args', 'time', 'status'],
        'edit'                      => ['queue_id', 'class', 'args', 'time', 'status'],
        'lists'                     => ['page', 'total'],
        'status'                    => ['queue_id' => 'require', 'status'],
        'del'                       => ['queue_id' => 'require']
    ];
}
