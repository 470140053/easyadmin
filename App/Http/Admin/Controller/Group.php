<?php

namespace App\Http\Admin\Controller;

use App\Http\Admin\Model\AuthGroupModel;
use App\Http\Admin\Model\AuthRuleModel;
use App\Http\Admin\Model\AuthGroupAccessModel;

class Group  extends Admin
{
    protected $validateName = '\App\Http\Admin\Validate\AuthGroupValidate';

    protected $model = AuthGroupModel::class;

    //不需要验证登录
    protected $noNeedLogin = [];

    //不需要验证权限
    protected $noNeedRule = ['getMenuList', 'getCategory'];

    //需要写入后台操作日志记录的方法，对应路由解释
    protected $logActionRoute = [
        'Group'   => '权限角色管理',
        'lists'  => '列表',
        'add'    => '添加',
        'edit'   => '编辑',
        'status' => '更新状态',
        'del'    => '删除',
        'setPower' => '用户权限分组',
    ];

    protected function _indexWhere($map)
    {
        if (empty($this->data['user']['super']) || $this->data['user']['super'] <> 1) {
            $map['handle_id'] = $this->data['user']['id'];
        }

        return $map;
    }

    /**
     * Undocumented function
     * 权限规则
     * @return void
     */
    public function getMenuList()
    {

        $map = [];
        if (empty($this->data['user']['super']) || $this->data['user']['super'] <> 1) {
            $ruleArr = AuthGroupAccessModel::getUserGroupListByUid($this->data['user']['id']);
            if (empty($ruleArr)) {
                return $this->error('您没有操作权限！');
            }

            $map['id'] = [$ruleArr, 'in'];
        }

        $list = AuthRuleModel::getListByCommonMap($map, ['id', 'title', 'pid', 'pid_path'], ['sort', 'ASC']);

        return $this->success('ok', [
            'list' => $list
        ]);
    }


    /**
     * Undocumented function
     * 当前用户权限列表
     * @return void
     */
    public function getUserPower()
    {
        if ($this->verify($this->validateName, 'getUserPower') !== true) {
            $this->writeJson();
            return false;
        }

        $list = AuthGroupModel::getUserGroupIds($this->data['id']);
        return $this->success('ok', [
            'info' => [
                'rules' => $list
            ]
        ]);
    }



    /**
     * Undocumented function
     * 配置管理员权限
     * @return void
     */
    public function setPower()
    {
        if ($this->verify($this->validateName, 'setPower') !== true) {
            $this->writeJson();
            return false;
        }

        $res = AuthGroupAccessModel::setPower($this->data);
        if (!$res) {
            $this->error('操作失败！');
        }

        return $this->success('操作成功！');
    }
}
