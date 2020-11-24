<?php

namespace Extend\Base;

use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\Http\AbstractInterface\Controller;

abstract class BaseController extends Controller
{
    /**
     * 数据主体
     * @var array
     */
    protected $data                = [];

    /**
     * 签名
     * @var string
     */
    protected $sig                 = '';

    /**
     * 错误码
     * @var int
     */
    protected $code                = 0;

    /**
     * 错误信息
     * @var string
     */
    protected $msg                 = '';

    /**
     * Undocumented variable
     * 跳转链接地址
     * @var string
     */
    protected $url                 = '';

    /**
     * 返回数据体
     * @var array
     */
    protected $return_data         = [];

    /**
     * Undocumented variable
     * 每页条数
     * @var integer
     */
    protected $total_number        =  15;

    /**
     * Undocumented variable
     * 是否验证Ip
     * @var boolean
     */
    protected $isIp = true;

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


    protected $userModel = '';


    protected $authModel = '';




    /**
     * 无需登录的方法,同时也就不需要鉴权了
     * @var array
     */
    protected $noNeedLogin = [];


    protected $noNeedRule = [];



    /**
     * 获取用户的真实IP
     * @param string $headerName 代理服务器传递的标头名称
     * @return string
     */
    protected function clientRealIP($headerName = 'x-real-ip')
    {
        $server = ServerManager::getInstance()->getSwooleServer();
        $client = $server->getClientInfo($this->request()->getSwooleRequest()->fd);
        $clientAddress = $client['remote_ip'];
        $xri = $this->request()->getHeader($headerName);
        $xff = $this->request()->getHeader('x-forwarded-for');
        if ($clientAddress === '127.0.0.1') {
            if (!empty($xri)) {  // 如果有xri 则判定为前端有NGINX等代理
                $clientAddress = $xri[0];
            } elseif (!empty($xff)) {  // 如果不存在xri 则继续判断xff
                $list = explode(',', $xff[0]);
                if (isset($list[0])) $clientAddress = $list[0];
            }
        }

        return $clientAddress;
    }


    /**
     * Undocumented function
     * 接收请求值
     * @param string|null $action
     * @return boolean|null
     */
    public function onRequest(?string $action): ?bool
    {
        $this->response()->withHeader('Content-type', 'application/json;charset=utf-8');

        if (!parent::onRequest($action)) {
            $this->error('请求错误！', 105);
            return false;
        }




        $post = json_decode($this->request()->getBody()->__toString(), true);

        if (empty($post) || empty($post['sig']) || empty($post['data'])) {
            $this->error('请求参数错误！', 106);
            return false;
        }

        if (empty($data['realIp'])) {
            $data['realIp'] = $this->clientRealIP();
        }

        if (method_exists($this, '__validateIpRoster')) {
            if ($this->__validateIpRoster($data['realIp']) === false) {
                $this->error('此IP已禁止访问！', 403);
                return false;
            }
        }

        $data = $this->decrypt($post['data'], $post['sig']);
        if (empty($data)) {
            $this->error('解密错误!', 107);
            return false;
        }


        $data = json_decode($data, true);
        if (empty($data)) {
            $this->error('解密错误,原因：解密出的参数带有特殊符号!', 108);
            return false;
        }

        if (!isset($data['ts']) || (time() - ($data['ts'] / 1000)) > 60) {
            $this->error('请求超时了！', 109);
            return false;
        }

        $sign = $this->getSign($data);
        
        if (!isset($post['sig']) || $sign != $post['sig']) {
            $this->error('签名错误！', 103);
            return false;
        }


        $get = $this->request()->getQueryParams();

        $this->data = array_merge($data, $get);

        //检测是否需要验证登录
        if (!$this->match($this->noNeedLogin, $action)) {

            //检测是否登录
            if (!$this->isLogin()) {
                $this->error('请登录账号！', 101);
                return false;
            }

            // 判断是否需要验证权限
            if (!$this->match($this->noNeedRule, $action)) {

                // $path = $this->request()->getUri()->getPath();
                $path = $this->request()->getServerParams()['path_info'];
                // 权限策略判断
                if (!$this->vifPolicy($path, $get)) {
                    $this->error('您没有访问权限！', 102);
                    return false;
                }
            }
        }


        if (method_exists($this, '__setLog')) {
            $this->__setLog();
        }

        return true;
    }


    /**
     * Undocumented function
     * 返回信息
     * @param integer $statusCode
     * @param [type] $result
     * @param [type] $msg
     * @return void
     */
    public function writeJson($statusCode = 200, $result = NULL, $msg = NULL)
    {

        if (!$this->response()->isEndResponse()) {

            $this->return_data['ts'] = time();

            $sig = $this->getSign($this->return_data);

            $data = array(

                'code' => $this->code,

                'msg' => $this->msg,

                'sig' => $sig,

                'data' => $sig ? $this->encrypt($this->return_data, $sig) : '',

            );

            $this->response()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            $this->response()->withHeader('Content-type', 'application/json;charset=utf-8');

            $this->response()->withStatus(200);

            return true;
        } else {

            return false;
        }
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





    /**
     * 检测当前控制器和方法是否匹配传递的数组
     *
     * @param array $arr 需要验证权限的数组
     */
    public function match(array $arr, string $action): ?bool
    {

        if (empty($arr)) {

            return false;
        }

        // 是否存在
        if (in_array($action, $arr) || in_array('*', $arr)) {

            return true;
        }


        // 没找到匹配
        return false;
    }

    /**
     * Undocumented function
     * 检查是否登录
     * @return boolean|null
     */
    public function isLogin(): ?bool
    {
        if (empty($this->data['app_str'])) {
            return false;
        }
        $redis = eRedis('redis');

        $userInfo =  $redis->get($this->userModel::$redisPrefix . $this->data['app_str']);

        if (empty($userInfo)) {
            return false;
        }

        $this->data['user'] = json_decode($userInfo, true);

        return true;
    }



    /**
     * 验证数据的合法性
     * @param  string   $validator  要使用的验证器名称
     * @param  string   $scene      验证场景
     * @param  array    $data       待验证的数据
     * @return boolean              验证通过返回true,失败返回false
     */
    protected function verify($validator, string $scene = '', ?array $data = [])
    {

        //如果未传递数据,默认验证自身的data属性
        if (empty($data)) {
            $data = $this->data;
        }

        $v = new $validator();

        //如果定义了验证场景,则设置验证场景
        if (!empty($scene)) {
            $v->scene($scene);
        }

        $result = $v->check($data);

        if (!$result) {
            list($this->msg, $this->code) = explode('|', $v->getError());
            return false;
        }

        return true;
    }

    /**
     * 返回错误
     * @param null $msg
     * @param int $code
     * @param array $returnData
     * @return bool|void
     */
    public function error($msg = null, $code = 500, $returnData = [])
    {
        if (is_null($msg)) {
            $returnUtil = returnUtil();
            $this->code = $returnUtil->getCode();
            $this->msg = $returnUtil->getError();
        } else {
            $this->code = $code;
            $this->msg = $msg;
        }

        $this->return_data = $returnData;
        return $this->writeJson();
    }

    /**
     * 返回成功
     * @param string $msg
     * @param array $returnData
     * @param int $code
     * @return bool|void
     */
    public function success($msg = 'ok', $returnData = [], $code = 200)
    {
        $this->code = $code;
        $this->msg = $msg;
        $this->return_data = $returnData;
        return $this->writeJson();
    }



    /**
     * 验证权限策略
     * @param $u_id
     * @param string $path
     * @return bool
     */
    protected function vifPolicy(string $path, array $query)
    {

        if (!empty($this->data['user']['super']) && $this->data['user']['super'] == 1) {
            return true;
        }

        if (empty($path)) {
            return false;
        }

        $result = (new $this->authModel())->check($path, $query, $this->data['user']['id']);

        if ($result) {

            return true;
        }

        return false;
    }
}
