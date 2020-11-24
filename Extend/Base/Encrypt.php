<?php

namespace Extend\Base;

class Encrypt
{

    /**
     * appkey
     * @var string
     */
    protected $appkey       = '';

    /**
     * appsecret
     * @var string
     */
    protected $appsecret    = '';


    public function __construct(string $appkey, string $appsecret) {
            $this->appkey = $appkey;
            $this->appsecret = $appsecret;
    }


    /**
     * 获取签名串
     * @param array $data
     * @return type
     */
    public  function getSign(array $arr)
    {
        if (empty($arr)) {
            return null;
        }

        $data = $this->filterArray($arr);
        $data['appkey']       = $this->appkey;
        $data['appsecret']    = $this->appsecret;
        ksort($data);
        //组装加密串
        $str = json_encode($data, JSON_UNESCAPED_UNICODE) . $data['appsecret'];
        
        return md5(md5($str) . $data['appkey']);
    }

    /**
     * Undocumented function
     * 过滤数组
     * @param array $data
     * @return void
     */
    public function filterArray(array $data)
    {
        $arr = [];
        foreach ($data as $k => $v) {
            if (!is_array($v)) {
                $arr[$k] = urlencode($v);
            }
        }

        return $arr;
    }


    public function encrypt($data = array(), $sig = "")
    {
        //AES, 128 ECB模式加密数据
        if (empty($data) || empty($sig)) {
            return null;
        }
        $input = json_encode($data, JSON_UNESCAPED_UNICODE);


        //0~8 + -16~-8
        $key = substr($sig, 0, 8) . substr($sig, 16, 8);
        //8~8 + -8~16
        $iv    = substr($sig, 8, 8) . substr($sig, 24, 8);

        $data = openssl_encrypt($input, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
        $data = rawurlencode(base64_encode($data));

        return $data;
    }



    public function decrypt($input = array(), $sig = "")
    {
        if (empty($input) || empty($sig)) {
            return null;
        }



        //0~8 + -16~-8
        $key = substr($sig, 0, 8) . substr($sig, 16, 8);
        //8~8 + -8~16
        $iv    = substr($sig, 8, 8) . substr($sig, 24, 8);

        $decrypted = openssl_decrypt(base64_decode(rawurldecode($input)), "AES-128-CBC", $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);

        return preg_replace('/[\x00-\x1F]/', '', $decrypted);
    }


}