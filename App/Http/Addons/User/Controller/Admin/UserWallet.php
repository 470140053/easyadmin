<?php

namespace App\Http\Addons\User\Controller\Admin;


use App\Http\Admin\Controller\Admin as AdminBase;

use App\Http\Addons\User\Model\UserWalletModel;

class UserWallet extends AdminBase
{
    protected $validateName = '\App\Http\Addons\User\Validate\UserValidate';

    protected $model = UserWalletModel::class;

    //不需要验证登录
    protected $noNeedLogin = [];

    //不需要验证权限
    protected $noNeedRule = [];

    //需要写入后台操作日志记录的方法，对应路由解释
    protected $logActionRoute = [
        'UserWallet'   => '会员管理',
        'setBalance'  => '设置余额',
    ];


    /**
     * Undocumented function
     * 设置余额
     * @return void
     */
    public function setBalance() {
        if ($this->verify($this->validateName, 'setBalance') !== true) {
            $this->writeJson();
            return false;
        }

        $res = UserWalletModel::setBalance($this->data);
        if(!$res) {
            return $this->error('操作失败！');
        }

        return $this->success('操作成功！');
    }

}