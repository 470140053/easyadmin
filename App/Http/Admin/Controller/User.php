<?php

namespace App\Http\Admin\Controller;

use EasySwoole\VerifyCode\Conf;
use App\Http\Admin\Model\AuthUserModel;
use App\Http\Admin\Assemble\AuthUserAssemble;
use App\Http\Admin\Model\SystemConfigModel;

class User  extends Admin
{
    protected $validateName = '\App\Http\Admin\Validate\LoginValidate';

    protected $model = AuthUserModel::class;

    //不需要验证登录
    protected $noNeedLogin = [];

    //不需要验证权限
    protected $noNeedRule = ['getUserInfo'];

    //需要写入后台操作日志记录的方法，对应路由解释
    protected $logActionRoute = [
        'User'   => '管理员',
        'lists'  => '列表',
        'add'    => '添加',
        'edit'   => '编辑',
        'status' => '更新状态',
        'del'    => '删除',
        'setPwd' => '修改管理员密码',
    ];


    /**
     * Undocumented function
     * 用户信息
     * @return void
     */
    public function getUserInfo()
    {
        $user = AuthUserModel::getInfoById($this->data['user']['id'], ['*', 'null as avatar_text']);
        if (!empty($user)) {
            $user['up_info'] = true;
            $user['token'] = (new \App\WebSocket\Model\Chats\ChatsUserModel())->fetchToken('admin',$user['id'],$user);

            $config = SystemConfigModel::getListByGroupName('easyadmin');
            if(!empty($config)) {
                $user['easyadmin_url'] = $config['easyadmin_url'];
                $user['easyadmin_appid'] = $config['easyadmin_appid'];
                $user['easyadmin_appsecret'] = $config['easyadmin_appsecret'];
                $user['easyadmin_version'] = $config['easyadmin_version'];
            }
        }

        return $this->success('ok', [
            'user' => $user
        ]);
    }


    protected function _indexWhere($map)
    {
        $map['id'] = [$this->data['user']['id'], '<>'];
        return $map;
    }

    /**
     * Undocumented function
     * 添加数据前 条件验证数据拼接
     * @param [type] $data
     * @return void
     */
    protected function _addBefore($data)
    {
        $user = AuthUserModel::getInfoByUsername($this->data['username']);
        if (!empty($user)) {
            $this->error('此手机账号已被注册了！');
            return false;
        }

        return $data;
    }



    /**
     * Undocumented function
     * 设置密码
     * @return void
     */
    public function setPwd()
    {
        if ($this->verify($this->validateName, 'setPwd') !== true) {

            $this->writeJson();

            return false;
        }

        $arr = AuthUserAssemble::create()->setPassword($this->data['password']);

        $res = AuthUserModel::updateInfo(['id' => $this->data['id']], $arr);

        if (!$res) {
            return $this->error('修改密码失败！');
        }

        return $this->success('修改密码成功！');
    }



    /**
     * Undocumented function
     * 编辑用户信息
     * @return void
     */
    public function setUserInfo()
    {
        if ($this->verify($this->validateName, 'setUserInfo') !== true) {

            $this->writeJson();

            return false;
        }

        $map['id']   = $this->data['user']['id'];
        $arr['avatar'] = AuthUserAssemble::create()->setAvatar($this->data['avatar']);
        $arr['nickname'] = AuthUserAssemble::create()->setNickname($this->data['nickname']);
        $res = AuthUserModel::updateInfo($map, $arr);
        if (!$res) {
            return $this->error('操作失败！');
        }

        return  $this->success('操作成功！');
    }


    /**
     * Undocumented function
     * 修改密码
     * @return void
     */
    public function updatePwd()
    {
        if ($this->verify($this->validateName, 'updatePwd') !== true) {

            $this->writeJson();

            return false;
        }

        $arr = AuthUserAssemble::create()->setPassword($this->data['password']);

        $res = AuthUserModel::updateInfo(['id' => $this->data['user']['id']], $arr);

        if (!$res) {
            return $this->error('修改密码错误！');
        }

        return $this->success('ok');
    }
}
