<?php


namespace App\Http\Admin\Validate;

use think\Validate;

/**
 * 设置系统菜单
 */
class AddonsValidate  extends Validate
{

    protected $rule = [
        //登录账户
        'token'                            => 'require',
        'name'                             => 'require',
        'force'                            => 'require',
        'addons_str'                       => 'require',
        'version'                          => 'require',
        'id'                               => 'require|is:number',
        'page'                             => 'is:number',
        'total'                            => 'is:number',
    ];

    protected $message = [
        'name.require'                   => '插件别名不能为空|20001',
        'force.require'                  => '覆盖标识不能为空|20001',
        'token.require'                  => 'token不能为空|20001',
        'addons_str.require'             => 'addons_str不能为空|20001',
        'version.require'                => '版本号不能为空|20001',
        'id.require'                     => 'ID不能为空|20006',
        'id.is'                          => 'ID格式不正确|20007',
        'page.is'                        => '页码不能为空|20011',
        'total.is'                         => '每页条数不能为空|20012',
    ];


    protected $scene = [
        'installAddons'                  => ['id', 'name','force','token','addons_str'],
        'unstallAddons'                  => ['name','force'],
    ];
}
