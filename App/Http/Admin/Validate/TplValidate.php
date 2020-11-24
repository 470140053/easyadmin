<?php


namespace App\Http\Admin\Validate;

use think\Validate;

/**
 * 设置模型
 */
class TplValidate  extends Validate
{

    protected $rule = [
        //登录账户
        'title'                            => 'require',
        'alias'                            => 'require',
        'template_type'                    => 'require',
        'index_url'                        => 'require',
        'template_content'                 => 'require',
        'status'                           => 'require|is:number|in:0,1,2,3',
        'id'                               => 'require|is:number',
        'page'                             => 'is:number',
        'total'                            => 'is:number',
    ];

    protected $message = [
        'title.require'                  => '模板名称不能为空|20001',
        'alias.require'                  => '模板别名不能为空|20002',
        'template_type.require'          => '模板类型不能为空|20003',
        'index_url.require'              => '数据请求地址不能为空|20004',
        'template_content.require'       => '模板内容不能为空|20005',
        'id.require'                     => 'ID不能为空|20006',
        'id.is'                          => 'ID格式不正确|20007',
        'status.require'                 => '数据状态参数不能为空|20008',
        'status.is'                      => '数据状态参数格式不对|20009',
        'status.in'                      => '数据状态参数只能是0-3之间|20010',
        'page.is'                        => '页码不能为空|20011',
        'total.is'                         => '每页条数不能为空|20012',
    ];


    protected $scene = [
        'lists'                  => ['page', 'total'],
        'add'                    => ['title', 'alias', 'template_type', 'template_content'],
        'edit'                   => ['id', 'title', 'alias', 'template_type', 'template_content'],
        'copy'                   => ['id'],
        'status'                 => ['id' => 'require', 'status'],
        'del'                    => ['id' => 'require']
    ];
}
