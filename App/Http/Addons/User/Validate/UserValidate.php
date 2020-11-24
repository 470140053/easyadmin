<?php

namespace App\Http\Addons\User\Validate;

use think\Validate;

/**
 * 设置模型
 */
class UserValidate  extends Validate
{

    protected $rule = [
        //登录账户
        'username'                       => 'require|is:alphaDash|length:5,20',
        'phone'                          => 'require|is:mobile',
        'verifyCode'                     => 'require',
        'realIp'                         => 'require',
        'nickname'                       => 'require',
        'status'                         => 'require|is:number|in:0,1,2,3',
        'auth'                           => 'require|is:number|in:0,1,2,3',
        //登陆密码
        'password'                       => 'require|is:alphaDash|length:6,20',
        'pwd'                            => 'confirm:password',
        //ID
        'id'                             => 'require|is:number',
        'diff_type'                      => 'require|is:number|in:1,2',
        'diff'                           => 'require|is:float',
        'note'                           => 'require',
    ];

    protected $message = [
        'note.require'                   => '备注说明不能为空|10000',
        'realIp.require'                 => 'IP不能为空|10000',
        'nickname.require'               => '昵称不能为空|10000',
        'verifyCode.require'             => '图形验证码不能为空|10000',
        'password.require'               => '密码不能为空|10000',
        'password.is'                    => '密码格式不对|10001',
        'password.length'                => '密码长度必须为6~20位之间|10002',
        'username.require'               => '用户名不能为空|10003',
        'username.is'                    => '用户名格式不对|10004',
        'username.length'                => '用户名长度必须为5~20位之间|10005',
        'id.require'                     => 'ID不能为空|10006',
        'id.is'                          => 'ID格式不正确|10007',
        'pwd.confirm'                    => '确认密码与新密码不一样|10013',
        'status.require'                 => '数据状态参数不能为空|20008',
        'status.is'                      => '数据状态参数格式不对|20009',
        'status.in'                      => '数据状态参数只能是0-3之间|20010',
        'auth.require'                   => '审核状态参数不能为空|20008',
        'auth.is'                        => '审核状态参数格式不对|20009',
        'auth.in'                        => '审核状态参数只能是0-3之间|20010',
        'diff_type.require'              => '设置类型参数不能为空|20008',
        'diff_type.is'                   => '设置类型参数格式不对|20009',
        'diff_type.in'                   => '设置类型参数只能是1-2之间|20010',
        'diff.require'                   => '设置数量参数不能为空|20008',
        'diff.is'                        => '设置数量参数格式不对|20009',
        'diff.in'                        => '设置数量参数只能是0-3之间|20010',
    ];


    protected $scene = [
        'add'                       => ['username','password', 'nickname', 'status','auth'],
        'edit'                      => ['id', 'nickname',  'status','auth'],
        'lists'                     => ['page', 'total'],
        'status'                    => ['id' => 'require', 'status'],
        'del'                       => ['id' => 'require'],
        'setBalance'                => ['id','diff_type','diff','note'],
        'setPwd'                    => ['id','password','pwd'],
        'frozen'                    => ['id','note'],
    ];
}
