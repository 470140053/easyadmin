<?php
namespace App\Common;

use EasySwoole\EasySwoole\Config;

/**
 * 帮助类
 */
class Help {

    use \EasySwoole\Component\Singleton;

    /**
     * di
     * @return \EasySwoole\Component\Di
     */
    public function di(){
        return \EasySwoole\Component\Di::getInstance();
    }

    /**
     * 模块调用
     * @param object|string $class
     * @return callable|string|null $class
     * @throws \Throwable
     */
    public function target($class) {

        if (!class_exists($class)) {
            throw new \Exception("Class '{$class}' not found", 500);
        }

        $obj = $this->di()->get($class);

        if (is_null($obj)) {
            $this->di()->set($class, $class);
        }

        return $this->di()->get($class);
    }

    /**
     * JWT编码
     * @param $data
     * @param int $exp
     * @param string $iss
     * @param string $aud
     * @return string
     */
    public function jwtEncode($data, int $exp = 3600, ?string $iss = null, ?string $aud = null) {
        $time = time();
        $conf = Config::getInstance()->getConf();
        $token = [
            "iss"  => !empty($iss) ? $iss : $conf['MAIN_SERVER']['LISTEN_ADDRESS'],
            "aud"  => !empty($aud) ? $aud : $conf['MAIN_SERVER']['LISTEN_ADDRESS'],
            "iat"  => $time,
            "nbf"  => $time,
            'data' => $data
        ];
        if ($exp) {
            $token['exp'] = $time + $exp;
        }
        $key = $conf['USE']['safe_key'];
        return \Firebase\JWT\JWT::encode($token, $key);
    }

    /**
     * JWT解码
     * @param string $jwt
     * @return array
     */
    public function jwtDecode(string $jwt) {
        $conf = Config::getInstance()->getConf('USE');
        $key = $conf['safe_key'];
        return (array)\Firebase\JWT\JWT::decode($jwt, $key, ['HS256']);
    }

}