<?php

namespace App\Http\Admin\Controller;

use App\Http\Admin\Model\ConfigModel;
use EasySwoole\Addon\Addons\Service;


class Addons extends Admin
{
    protected $validateName = '\App\Http\Admin\Validate\AddonsValidate';

    //不需要验证权限
    protected $noNeedRule = ['*'];


    /**
     * Undocumented function
     * 插件列表
     * @return void
     */
    public function getAddonsLists()  {
        $addons = get_addon_list();
        foreach ($addons as $k => $v) {
            $config = get_addon_config($v['name']);
            $addons[$k]['config'] = $config ? 1 : 0;
        }
        
        $this->success('ok',[
            'list'  => $addons
        ]);
    }


    /**
     * Undocumented function
     * 安装插件
     * @return void
     */
    public function installAddons() {
        if ($this->verify($this->validateName, 'installAddons') !== true) {
            $this->writeJson();
            return false;
        }

        try {
            Service::install($this->data['name'], $this->data['force'], $this->data);
            return $this->success('安装成功！');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }

    }


    /**
     * Undocumented function
     * 卸载插件
     * @return void
     */
    public function unstallAddons() {
        if ($this->verify($this->validateName, 'unstallAddons') !== true) {
            $this->writeJson();
            return false;
        }

        try {
            Service::uninstall($this->data['name'], $this->data['force']);
            $this->success('卸载成功！');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}