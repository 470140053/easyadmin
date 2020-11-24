<?php


namespace App\Http\Admin\Validate;

use think\Validate;

/**
 * 设置系统菜单
 */
class QiniuConfigValidate  extends Validate
{

    protected $rule = [
        'access_key'                       => 'require',
        'secret_key'                       => 'require',
        'status'                           => 'require|is:number|in:0,1,2,3',
        'id'                               => 'require|is:number',
        'page'                             => 'is:number',
        'total'                            => 'is:number',
    ];

    protected $message = [
        'access_key.require'             => 'AccessKey不能为空|20001',
        'secret_key.require'             => 'SecretKey不能为空|20006',
        'id.require'                     => 'ID不能为空|20006',
        'id.is'                          => 'ID格式不正确|20007',
        'status.require'                 => '数据状态参数不能为空|20008',
        'status.is'                      => '数据状态参数格式不对|20009',
        'status.in'                      => '数据状态参数只能是0-3之间|20010',
        'page.is'                        => '页码不能为空|20011',
        'total.is'                         => '每页条数不能为空|20012',
    ];


    protected $scene = [
        'add'                       => ['access_key', 'secret_key'],
        'edit'                      => ['id', 'access_key', 'secret_key', 'status'],
        'lists'                     => ['page', 'total'],
        'status'                    => ['id' => 'require', 'status'],
        'del'                       => ['id' => 'require'],
    ];
}
