<?php

namespace App\Http\Addons\User;

use EasySwoole\Addon\Addons;
use App\Http\Admin\Model\AuthRuleModel;

/**
 * CMS插件
 */
class User extends Addons
{

    protected  $identifying = 'User';

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        $map['identifying'] = $this->identifying;
        $result = AuthRuleModel::getCount($map);
        if (!empty($result)) {
            return false;
        }

        $menu = [
            [
                'name'    => '',
                'title'   => '会员管理',
                'icon'    => 'fast-icon-bussiness-man-fill',
                'ismenu'  => 1,
                'status'  => 1,
                'identifying' => $this->identifying,
                'sublist' => [
                    [
                        'name'    => '/admin/member/lists',
                        'title'   => '会员列表',
                        'icon'    => '',
                        'ismenu'  => 1,
                        'status'  => 1,
                        'tpl_type' => 0,
                        'tpl_alias' => 'HuiYuanGuanLiLieBiao',
                        'identifying' => $this->identifying,
                        'sublist' => [
                            [
                                'name'    => '/admin/member/add',
                                'title'   => '添加',
                                'ismenu'  => 0,
                                'status'  => 1,
                                'tpl_type' => 0,
                                'tpl_alias' => 'HuiYuanTianJia',
                                'tpl' => 'base/form/index',
                                'identifying' => $this->identifying,
                            ],
                            [
                                'name'    => '/admin/member/edit',
                                'title'   => '编辑',
                                'ismenu'  => 0,
                                'status'  => 1,
                                'tpl_type' => 0,
                                'tpl_alias' => 'HuiYuanBianJi',
                                'tpl' => 'base/form/index',
                                'identifying' => $this->identifying,
                            ],
                            [
                                'name'    => '/admin/member/getInfo',
                                'title'   => '信息',
                                'ismenu'  => 0,
                                'status'  => 1,
                                'identifying' => $this->identifying,
                            ],
                            [
                                'name'    => '/admin/member/del',
                                'title'   => '删除',
                                'ismenu'  => 0,
                                'status'  => 1,
                                'identifying' => $this->identifying,
                            ],
                            [
                                'name'    => '/admin/member/status',
                                'title'   => '启用/禁用',
                                'ismenu'  => 0,
                                'status'  => 1,
                                'identifying' => $this->identifying,
                            ],
                            [
                                'name'    => '/admin/member/setPwd',
                                'title'   => '修改密码',
                                'ismenu'  => 0,
                                'status'  => 1,
                                'identifying' => $this->identifying,
                            ],
                            [
                                'name'    => '/admin/member/setFrozen',
                                'title'   => '冻结账号',
                                'ismenu'  => 0,
                                'status'  => 1,
                                'identifying' => $this->identifying,
                            ],
                            [
                                'name'    => '/admin/memberWallet/setBalance',
                                'title'   => '设置余额',
                                'ismenu'  => 0,
                                'status'  => 1,
                                'identifying' => $this->identifying,
                            ],
                            [
                                'name'    => '/admin/memberWallet/getInfo',
                                'title'   => '钱包信息',
                                'ismenu'  => 0,
                                'status'  => 1,
                                'identifying' => $this->identifying,
                            ]

                        ]
                    ]
                ]
            ]
        ];

        AuthRuleModel::createMenu($menu);
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        AuthRuleModel::delMenu($this->identifying);
        return true;
    }

    /**
     * 插件启用方法
     */
    public function enable()
    {
        AuthRuleModel::enableMenu($this->identifying);
        return true;
    }

    /**
     * 插件禁用方法
     */
    public function disable()
    {
        AuthRuleModel::disableMenu($this->identifying);
        return true;
    }
}
