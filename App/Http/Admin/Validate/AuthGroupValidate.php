<?php


namespace App\Http\Admin\Validate;

use think\Validate;

/**
 * 设置系统菜单
 */
class AuthGroupValidate  extends Validate
{

    protected $rule = [
        //登录账户
        'title'                            => 'require',
        'sort'                             => 'require|is:number',
        'status'                           => 'require|is:number|in:0,1,2,3',
        'id'                               => 'require|is:number',
        'page'                             => 'is:number',
        'total'                            => 'is:number',
        'rules'                            => 'require|is:array',
    ];

    protected $message = [
        'title.require'                  => '权限名称不能为空|20001',
        'id.require'                     => 'ID不能为空|20006',
        'id.is'                          => 'ID格式不正确|20007',
        'sort.require'                   => '排序号不能为空|20006',
        'sort.is'                        => '排序号格式不正确|20007',
        'status.require'                 => '数据状态参数不能为空|20008',
        'status.is'                      => '数据状态参数格式不对|20009',
        'status.in'                      => '数据状态参数只能是0-3之间|20010',
        'rules.require'                  => '权限组不能为空|20001',
        'rules.is'                       => '权限组格式不正确|20001',
        'page.is'                        => '页码不能为空|20011',
        'total.is'                         => '每页条数不能为空|20012',
    ];


    protected $scene = [
        'add'                       => ['title', 'status'],
        'edit'                      => ['id', 'title', 'status'],
        'lists'                     => ['page', 'total'],
        'status'                    => ['id' => 'require', 'status'],
        'del'                       => ['id' => 'require'],
        'setPower'                  => ['id', 'rules'],
        'getUserPower'              => ['id'],


    ];
}
