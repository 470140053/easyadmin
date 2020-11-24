<?php

namespace App\Http\Admin\Controller;

use App\Http\Admin\Model\ConfigModel;


class Config extends Admin
{
    //不需要验证权限
    protected $noNeedRule = ['*'];

    /**
     * Undocumented function
     * 订单状态
     * @return void
     */
    public function userType() {
        return $this->success('ok',[
            'list' => ConfigModel::$userType
        ]);
    }



    /**
     * Undocumented function
     * 订单状态
     * @return void
     */
    public function platformTypeList() {
        return $this->success('ok',[
            'list' => ConfigModel::$platformTypeList
        ]);
    }
}