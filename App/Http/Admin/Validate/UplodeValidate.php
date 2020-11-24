<?php

namespace App\Http\Admin\Validate;

use think\Validate;

/**
 * 设置系统菜单
 */
class UplodeValidate  extends Validate
{

    protected $rule = [
        'ids'                                 => 'require',
        'filename'                            => 'require',
        'is_private'                          => 'require|is:number',
        'img_path'                            => 'require',
        'file'                                => 'require',
    ];

    protected $message = [
        'file.require'                           => '文件不能为空|20001',
        'ids.require'                            => '文件ID不能为空|20001',
        'filename.require'                       => '文件名称不能为空|20001',
        'img_path.require'                       => '文件路径不能为空|20001',
        'is_private.require'                     => '空间类型ID不能为空|20006',
        'is_private.is'                          => '空间类型ID格式不正确|20007',
    ];


    protected $scene = [
        'getPrivateImagePath'                  => ['ids'],
        'getImagePath'                         => ['img_path'],
        'upload'                         => ['file'],
        'getUplodeToken'                       => ['filename', 'is_private'],

    ];
}
