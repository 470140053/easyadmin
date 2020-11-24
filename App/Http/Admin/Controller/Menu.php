<?php

namespace App\Http\Admin\Controller;

use EasySwoole\VerifyCode\Conf;
use App\Http\Admin\Model\AuthRuleModel;
use App\Http\Admin\Model\AuthGroupAccessModel;
use App\Http\Admin\Model\TemplatesModel;

class Menu  extends Admin
{
    protected $validateName = '\App\Http\Admin\Validate\AuthRuleValidate';

    protected $model = AuthRuleModel::class;

    //不需要验证登录
    protected $noNeedLogin = [];

    //不需要验证权限
    protected $noNeedRule = ['getMenuRouter'];

    //需要写入后台操作日志记录的方法，对应路由解释
    protected $logActionRoute = [
        'Menu'   => '系统菜单',
        'lists'  => '列表',
        'add'    => '添加',
        'edit'   => '编辑',
        'status' => '更新状态',
        'del'    => '删除',
    ];

    protected function _getListWhere($map)
    {
        if (!isset($map['status'])) {
            $map['status'] = [[0, 1], 'in'];
        }
        $this->data['isTree'] = 1;
        return $map;
    }


    /**
     * Undocumented function
     * 左边菜单列表数据
     * @return void
     */
    public function getMenuRouter()
    {
        $map = [];

        $sql = ' SELECT `id` FROM ' . TemplatesModel::create()->getTableName() . ' WHERE `alias` = ' . AuthRuleModel::create()->getTableName() . '.`tpl_alias` limit 1';

        $field = ['id', 'pid', 'pid_path', 'name', 'title', 'icon', 'ismenu', '(' . $sql . ') as tpl_id', 'tpl'];

        $order = ['sort', 'ASC'];


        if ($this->data['user']['super'] != 1) {

            $groupIds = AuthGroupAccessModel::getUserGroupListByUid($this->data['user']['id']);

            if (empty($groupIds)) {
                return $this->error('您的权限不足！');
            }

            $map['id'] = [$groupIds, 'in'];
        }

        $map['status'] = 1;

        $rules =  AuthRuleModel::getListByCommonMap($map, $field, $order);

        $this->success('ok', [
            'list' => $rules,
            'tree' => makeTree($rules)
        ]);
    }
}
