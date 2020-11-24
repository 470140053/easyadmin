<?php

namespace App\Http\Admin\Validate;

use think\Validate;

/**
 * 设置系统菜单
 */
class VersionValidate  extends Validate
{

    protected $rule = [
        'newversion'                       => 'require',
        'download_id'                      => 'require|is:number',
        'status'                           => 'require|is:number|in:0,1,2,3',
        'id'                               => 'require|is:number',
        'page'                             => 'is:number',
        'total'                            => 'is:number',
        'field'                            => 'require',
        'value'                            => 'require',
    ];

    protected $message = [
        'newversion.require'             => '新版本号不能为空|20001',
        'id.require'                     => 'ID不能为空|20006',
        'id.is'                          => 'ID格式不正确|20007',
        'download_id.require'            => '下载地址不能为空|20006',
        'download_id.is'                 => '下载地址格式不正确|20007',
        'status.require'                 => '数据状态参数不能为空|20008',
        'status.is'                      => '数据状态参数格式不对|20009',
        'status.in'                      => '数据状态参数只能是0-3之间|20010',
        'page.is'                        => '页码不能为空|20011',
        'total.is'                       => '每页条数不能为空|20012',
        'field'                          => 'require',
        'value'                          => 'require',
    ];


    protected $scene = [
        'add'                       => ['download_id', 'newversion', 'status'],
        'edit'                      => ['id', 'download_id', 'newversion', 'status'],
        'lists'                     => ['page', 'total'],
        'status'                    => ['id' => 'require', 'status'],
        'del'                       => ['id' => 'require'],
        'appointField'              => ['id', 'field', 'value']

    ];
}
