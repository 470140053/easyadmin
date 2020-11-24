<?php

namespace App\Http\Admin\Validate;

use think\Validate;

/**
 * 设置系统菜单
 */
class AuthRuleValidate  extends Validate
{

    protected $rule = [
        //登录账户
        'title'                            => 'require',
        'sort'                             => 'require|is:number',
        'status'                           => 'require|is:number|in:0,1,2,3',
        'id'                               => 'require|is:number',
        'page'                             => 'is:number',
        'total'                            => 'is:number',
        'field'                            => 'require',
        'value'                            => 'require',
    ];

    protected $message = [
        'title.require'                  => '菜单名称不能为空|20001',
        'field.require'                  => '需要更改的字段名称不能为空|20001',
        'value.require'                  => '需要更改的字段值不能为空|20001',
        'id.require'                     => 'ID不能为空|20006',
        'id.is'                          => 'ID格式不正确|20007',
        'sort.require'                   => '排序号不能为空|20006',
        'sort.is'                        => '排序号格式不正确|20007',
        'status.require'                 => '数据状态参数不能为空|20008',
        'status.is'                      => '数据状态参数格式不对|20009',
        'status.in'                      => '数据状态参数只能是0-3之间|20010',
        'page.is'                        => '页码不能为空|20011',
        'total.is'                         => '每页条数不能为空|20012',
    ];


    protected $scene = [
        'add'                    => ['title', 'sort', 'status'],
        'edit'                   => ['id', 'title', 'sort', 'status'],
        'status'                 => ['id' => 'require', 'status'],
        'lists'                  => ['page', 'total'],
        'appointField'           => ['id', 'field', 'value'],
        'del'                    => ['id']
    ];
}
