<?php

namespace App\Http\Admin\Controller;

use EasySwoole\VerifyCode\Conf;
use App\Http\Admin\Model\AuthUserModel;


class Login  extends Admin
{

    protected $validateName = '\App\Http\Admin\Validate\LoginValidate';

    //不需要验证登录
    protected $noNeedLogin = ['*'];

    //不需要验证权限
    protected $noNeedRule = ['*'];

    //需要写入后台操作日志记录的方法，对应路由解释
    protected $logActionRoute = [
        'login' => '登录',
        'logout' => '退出登录',
    ];

    /**
     * Undocumented function
     * 登录
     * @return void
     */
    public function login()
    {
        if ($this->verify($this->validateName, 'login') !== true) {

            $this->writeJson();

            return false;
        }

        $redis = eRedis();
        $verifyCode = $redis->get(md5($this->data['realIp'] . 'getVerifyCode'));

        if (strtolower($verifyCode) !== strtolower($this->data['verifyCode'])) {
            return $this->error('图形验证码不正确！');
        }


        $user = AuthUserModel::getInfoByMap(['username' => $this->data['username']], ['id', 'password', 'salt', 'status']);

        if (empty($user)) {
            return  $this->error('账号或密码错误！');
        }

        $pwd = AuthUserModel::setPassword($this->data['password'], $user['salt']);
        if ($pwd !== $user['password']) {
            return  $this->error('密码错误！');
        }

        if ($user['status'] != 1) {
            return  $this->error('此账号已经被冻结或禁用！');
        }

        $redis->del(md5($this->data['realIp'] . 'getVerifyCode'));

        return $this->success('登录成功！', [
            'appstr' => AuthUserModel::setLoginMsg($user['id'], $this->data['realIp'])
        ]);
    }



    /**
     * Undocumented function
     * 验证码
     * @return void
     */
    public function getVerifyCode()
    {
        $config = new Conf();
        $config->setCharset('0123456789');
        $code = new \EasySwoole\VerifyCode\VerifyCode($config);
        $reCode = $code->DrawCode();
        $base64 = $reCode->getImageBase64();
        $imgCode = $reCode->getImageCode();
        $redis = eRedis();
        $redis->del(md5($this->data['realIp'] . 'getVerifyCode'));
        $redis->set(md5($this->data['realIp'] . 'getVerifyCode'), $imgCode, 600);

        return $this->success('ok', [
            'verifyCodeImg' => $base64
        ]);
    }


    /**
     * logout
     * 退出登录,参数注解写法
     * @return bool
     * @author Tioncico
     * Time: 10:23
     */
    public function logout()
    {
        AuthUserModel::outLogin($this->data);
        return $this->success('退出登录成功！');
    }
}
