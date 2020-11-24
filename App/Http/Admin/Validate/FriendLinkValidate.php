<?php

namespace App\Http\Admin\Validate;

use think\Validate;

/**
 * 设置系统菜单
 */
class FriendLinkValidate  extends Validate
{

    protected $rule = [
        'title'                            => 'require',
        'url'                              => 'require',
        'status'                           => 'require|is:number|in:0,1,2,3',
        'id'                               => 'require|is:number',
        'page'                             => 'is:number',
        'total'                            => 'is:number',
        'field'                            => 'require',
        'value'                            => 'require',
    ];

    protected $message = [
        'title.require'                  => '友情链接名称不能为空|20006',
        'url.require'                    => '链接不能为空|20006',
        'id.require'                     => 'ID不能为空|20006',
        'id.is'                          => 'ID格式不正确|20007',
        'status.require'                 => '数据状态参数不能为空|20008',
        'status.is'                      => '数据状态参数格式不对|20009',
        'status.in'                      => '数据状态参数只能是0-3之间|20010',
        'page.is'                        => '页码不能为空|20011',
        'total.is'                       => '每页条数不能为空|20012',
        'field.require'                  => '需要更改的字段名称不能为空|20001',
        'value.require'                  => '需要更改的字段值不能为空|20001',
    ];


    protected $scene = [
        'add'                       => ['title', 'url', 'status'],
        'edit'                      => ['id', 'title', 'url', 'status'],
        'lists'                     => ['page', 'total'],
        'status'                    => ['id' => 'require', 'status'],
        'del'                       => ['id' => 'require'],
        'appointField'              => ['id', 'field', 'value']
    ];
}
