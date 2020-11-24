<?php

namespace App\Http\Admin\Validate;

use think\Validate;

/**
 * 设置模型
 */
class LoginValidate  extends Validate
{

    protected $rule = [
        //登录账户
        'username'                       => 'require|is:alphaDash|length:5,20',
        'phone'                          => 'require|is:mobile',
        'verifyCode'                     => 'require',
        'phoneCode'                      => 'require',
        'nickname'                       => 'require',
        'avatar'                         => 'require|is:number',
        'note'                           => 'require',
        //登陆密码
        'password'                       => 'require|is:alphaDash|length:6,20',
        'pwd'                            => 'confirm:password',
        //ID
        'id'                             => 'require|is:number',
        //手机号
        'phone'                          => 'is:mobile',
        //状态
        'status'                          => 'in:0,1,2,3',
        //是否为管理员状态
        'super'                          => 'is:number|in:0,1',
        'page'                             => 'is:number',
        'total'                            => 'is:number',
    ];

    protected $message = [
        'nickname.require'               => '姓名不能为空|10000',
        'avatar.require'                 => '头像不能为空|10000',
        'avatar.is'                      => '头像格式不正确|10000',
        'note.require'                   => '备注信息不能为空|10000',
        'verifyCode.require'             => '图形验证码不能为空|10000',
        'phoneCode.require'              => '手机验证码不能为空|10000',
        'password.require'               => '密码不能为空|10000',
        'password.is'                    => '密码格式不对|10001',
        'password.length'                => '密码长度必须为6~20位之间|10002',
        'username.require'               => '用户名不能为空|10003',
        'username.is'                    => '用户名格式不对|10004',
        'username.length'                => '用户名长度必须为5~20位之间|10005',
        'phone.require'                  => '手机号码不能为空|10003',
        'phone.is'                       => '手机号码格式不对|10004',
        'id.require'                     => 'ID不能为空|10006',
        'id.is'                          => 'ID格式不正确|10007',
        'status.is'                      => '数据状态参数格式不对|10008',
        'status.in'                      => '数据状态参数只能是0-3之间|10009',
        'super.is'                       => '是否为管理员状态参数格式不对|10010',
        'super.in'                       => '是否为管理员状态参数只能是0或者1|10011',
        'phone.in'                       => '手机号格式不正确|10012',
        'pwd.confirm'                    => '确认密码与新密码不一样|10013',
        'page.is'                        => '页码不能为空|10001',
        'total.is'                         => '每页条数不能为空|10001',
    ];


    protected $scene = [
        //登录
        'login'                    => ['username', 'password'],
        'add'                      => ['username', 'password', 'status', 'super', 'phone'],
        'edit'                     => ['username', 'status', 'super', 'phone', 'id'],
        'status'                   => ['status', 'id'],
        //重置密码
        'resetPwd'                 => ['id'],
        'grant'                    => ['id'],
        'updateGrant'              => ['id'],
        'getUserApi'               => ['id'],
        //修改密码
        'updatePwd'                => ['password', 'pwd'],
        'setPwd'                   => ['id', 'password', 'pwd'],
        'del'                      => ['id' => 'require'],
        'lists'                    => ['page', 'total'],
        'setUserInfo'              => ['nickname', 'avatar']
    ];
}
