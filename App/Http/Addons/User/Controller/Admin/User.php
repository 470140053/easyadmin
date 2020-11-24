<?php

namespace App\Http\Addons\User\Controller\Admin;


use App\Http\Admin\Controller\Admin as AdminBase;

use App\Http\Addons\User\Model\UserModel;
use App\Http\Addons\User\Assemble\UserAssemble;

class User extends AdminBase
{
    protected $validateName = '\App\Http\Addons\User\Validate\UserValidate';

    protected $model = UserModel::class;

    //不需要验证登录
    protected $noNeedLogin = [];

    //不需要验证权限
    protected $noNeedRule = [];

    //需要写入后台操作日志记录的方法，对应路由解释
    protected $logActionRoute = [
        'User'   => '会员管理',
        'lists'  => '列表',
        'add'    => '添加',
        'edit'   => '编辑',
        'status' => '更新状态',
        'del'    => '删除',
    ];

    protected function _indexWhere($map){
        $map['id'] = [array_unique([1,$this->data['user']['id']]),'NOT IN'];
        return $map; 
    }

    protected function _addBefore($data) {
        $user = UserModel::getCount(['username' => $this->data['username']]);
        if (!empty($user)) {
            $this->error('此账号已经存在了！');
            return  false;
        }

        return $data;
    }

    protected function _saveData($data,$type) {
        if($type == 'add') {
            $referee = $this->model::getInfoByMap(['id'=>1],['id','username','nickname','pid','pid_username','ppid','ppid_username']);
            if(empty($referee)) {
                return $this->error('没有找到此账号的推广专员！');
            }
            
            return $this->model::register($data, $referee);
        }else{
            return $this->model::saveData($data, $type);
        }
        
    }

    protected function _status($data) {
        return $this->model::updateUserStatus($data[$this->model::create()->primaryKey], $data['status']);
    }


    protected function _del($id) {
        return $this->model::deleteUser($id);
    }


    /**
     * Undocumented function
     * 设置密码
     * @return void
     */
    public function setPwd() {
        if ($this->verify($this->validateName, 'setPwd') !== true) {
            $this->writeJson();
            return false;
        }
        $map['id'] = $this->data['id'];
        $arr = UserAssemble::create()->setPassword($this->data['password']);

        $res = $this->model::updateInfo($map,$arr);

        if (!$res) {
            return $this->error('修改密码失败！');
        }

        return $this->success('修改密码成功！');
    }


    /**
     * Undocumented function
     * 冻结账号
     * @return void
     */
    public function setFrozen()
    {
        if ($this->verify($this->validateName, 'frozen') !== true) {
            $this->writeJson();
            return false;
        }

        $map['id'] = $this->data['id'];

        $map['status'] = 2;
        $info =  UserModel::getInfoByMap($map, ['id']);

        if (!empty($info)) {
            return $this->error('此用户已被冻结，无需重复操作');
        }

        $res = UserModel::frozen($this->data);
        if (!$res) {
            return $this->error('操作冻结用户失败！');
        }

        return $this->success('操作冻结用户成功！');
    }
}