<?php

/**
 * 通讯用户管理
 */

namespace App\WebSocket\Model\Chats;

use Extend\Base\BaseModel;
use EasySwoole\ORM\Utility\Schema\Table;

class ChatsUserModel extends BaseModel {

    use \EasySwoole\Component\Singleton;

    //对应的表名
    protected $tableName = 'tp_chats_user';

    //主键字段
    public $primaryKey = 'uid';

    /**
     * 关联标签符号
     * @var string
     */
    protected $_hasSymbol = '@';

    /**
     * token到期时间
     * @var float|int
     */
    protected $_tokenExpire = 2 * 60 * 60;

    /**
     * 解析模块标签
     * @param string $has
     * @return array|string
     */
    protected  function getParsingHasAttr($value,$data) {
        if(!empty($data['has'])) {
            if(substr_count($data['has'],$this->_hasSymbol) == 0){
                return $data['has'];
            }

            return explode($this->_hasSymbol,$data['has'],2);
        }

        return null;
    }


    /**
     * 表的获取
     * 此处需要返回一个 EasySwoole\ORM\Utility\Schema\Table
     * @return Table
     */
    public function schemaInfo(bool $isCache = true): Table
    {
        $table = new Table($this->tableName);
        $table->colInt('uid')->setIsPrimaryKey(true);
        $table->colVarChar('has',50);
        $table->colInt('has_id',10);
        $table->colInt('avatar',10);
        $table->colInt('create_time',10);
        $table->colInt('update_time',10);
        $table->colInt('login_time',10);
        $table->colVarChar('nickname',50);
        $table->colVarChar('parsing_has',50);
        $table->colVarChar('sign',250);
        $table->colVarChar('avatar_text',255);
        return $table;
    }

   


    /**
     * 获取token
     * @param string $has
     * @param int $hasId
     * @return bool|string
     * @throws \Exception
     */
    public function getToken(string $has,int $hasId){

        //定义获取方法
        $method = 'chatsUserInfo';

        //钩取数据
        $info = hookClear('chats',$has,$method,[$hasId]);

        if(is_null($info)){
            return $this->error($has . '模块或方法不存在!');
        }

        if(empty($info)){
            return $this->error('token获取失败!');
        }

        //强制更新
        $info['up_info'] = true;

        return $this->fetchToken($has,$hasId,$info);
    }

    /**
     * 获取token
     * @param string $has
     * @param int $hasId
     * @param array &$data
     * @return bool|string
     * @throws \Exception
     */
    public function fetchToken(string $has,int $hasId,array $data = []){

        if(empty($has)){
            return $this->error('错误的请求!');
        }

        $upInfo = isset($data['up_info']) ? $data['up_info'] : false;

        $time = time();

        //查询用户
        $info = self::create()->where(['has' => $has,'has_id' => $hasId])
            ->field(['uid'])->get();

        $info = !empty($info) ? $info->toArray() : [];

        $uid = isset($info['uid']) ? $info['uid'] : 0;

        if($upInfo){
            if(empty($info)){

                if(!$this->verifyHas($has)){
                    return false;
                }

                //添加数据
                $addData = [
                    'has'         => $has,
                    'has_id'      => $hasId,
                    'nickname'    => isset($data['nickname']) ? $data['nickname'] : '',
                    'sign'        => isset($data['sign']) ? $data['sign'] : '',
                    'avatar'      => isset($data['avatar']) ? $data['avatar'] : '',
                    'create_time' => $time,
                    'update_time' => $time
                ];

                $uid = self::create()->data($addData)->save();

                if($uid === false){
                    return false;
                }

            }else{
                $upData = [];

                if(isset($data['nickname'])){
                    $upData['nickname'] = $data['nickname'];
                }

                if(isset($data['sign'])){
                    $upData['sign'] = $data['sign'];
                }

                if(isset($data['avatar'])){
                    $upData['avatar'] = $data['avatar'];
                }

                if(!empty($upData)){
                    $upData['uid'] = $uid;
                    $upData['update_time'] = $time;
                    //更新数据
                    self::create()->where([$this->primaryKey => $uid])->update($upData);
                }
            }
        }

        $data['uid'] = $uid;

        $token = $this->token($uid);

        if(empty($token)){
            return $this->error('无效用户,token获取失败!');
        }

        return $token;
    }

    /**
     * 生成token
     * @param string $uid
     * @param array $extData
     * @return bool|string
     */
    public function token(string $uid,array $extData = []){

        if (!isset($uid))
            return $this->error('参数错误!');

        $data = [
            'uid'       => $uid
        ];

        if(!empty($extData)){
            $data = array_merge($data,$extData);
        }

        return help()->jwtEncode($data,$this->_tokenExpire);
    }

    /**
     * token数据
     * @param string $token
     * @return array|bool
     */
    public function tokenData(string $token){
       
        if (empty($token))
            return $this->error('无效的token');
            
        try{
            $jwtInfo = help()->jwtDecode($token);
           
        }catch (\Firebase\JWT\SignatureInvalidException $e){
            // Provided JWT was invalid
            return $this->error('无效的token!');
        }catch (\Firebase\JWT\BeforeValidException $e){
            //Provided JWT is trying to be used before it's eligible as defined by 'nbf'
            //Provided JWT is trying to be used before it's been created as defined by 'iat'
            return $this->error('非法的token!');

        }catch (\Firebase\JWT\ExpiredException $e){
            // Provided JWT has since expired, as defined by the 'exp' claim
            return $this->error('token已过期!');
        }catch (\Exception $e){
            //其他异常处理
            return $this->error($e->getMessage());
        }
   
        $data = (array)$jwtInfo['data'];

        if(empty($data['uid'])){
            return $this->error('无效的token');
        }

        $newToken = '';

        //接近两小时过期重新更新token
        if($jwtInfo['exp'] < time() + ($this->_tokenExpire)){

            $extData = $data;

            $uid = $data['uid'];

            unset($extData['uid']);

            //保存新的token
            $newToken = $this->token($uid,$extData);
        }

        return [
            'data'      => $data,
            'token'     => $newToken
        ];
    }

    /**
     * 验证token
     * @param string $token
     * @param bool $isLogin 是否登录验证
     * @return array|bool
     * @throws \Exception
     */
    public function verifyToken(string $token, bool $isLogin = false){

        if (empty($token)){
            return $this->error('无效的token');
        }
        
        $returnData = $this->tokenData($token);
        
        if ($returnData === false){
            return false;
        }

        $info = self::create()->where([$this->primaryKey => $returnData['data']['uid']])->get();

        if (empty($info)){
            return $this->error('无效的token!');
        }

        $info = $info->toArray();

        $returnData['data'][$this->primaryKey] = $info[$this->primaryKey];
        $returnData['data']['nickname'] = $info['nickname'];

        $time = time();

        $upData = [];

        if ($isLogin) {
            $upData['login_time'] = $time;
        }

        if (!empty($upData)) {

            //更新登录时间
            if (!$this->where([$this->primaryKey => $info[$this->primaryKey]])->update($upData)){
                return $this->error('登录信息更新失败!');
            }
        }

        //缓存用户信息
        socketHelp()->userInfo($info[$this->primaryKey],true);

        return $returnData;
    }

    /**
     * 获取头像
     * @param $avatar
     * @return string
     */
    public function getAvatar($avatar): string {
        if (empty($avatar)) {
            return domain() . '/public/chats/images/avatar.png';
        }
        return '';//\App\Common\QiNiuUpload::StaticReturnAuthPath($avatar);;
    }

    /**
     * 更新签名
     * @param string $uid 用户ID
     * @param string $sign 签名
     * @param $uid
     * @param string $sign
     * @return bool
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function upSign($uid, string $sign){

        if (empty($uid)) {
            return $this->error('参数错误!');
        }

        return $this->where([$this->primaryKey => $uid])->update(['sign' => $sign]);
    }

    /**
     * 获取用户列表
     * @param array $where
     * @param array|string $order
     * @param int $limit
     * @return array
     */
    public function getUserList(array $where, $order = '', $limit = 0){

        $fields = [
            'uid',
            'has',
            'has_id',
            'nickname',
            'avatar',
            'null as avatar_text',
            'sign',
            'create_time',
            'update_time',
            'login_time',
            'null as parsing_has'
        ];

        if(!is_array($limit)){
            $limit = [$limit];
        }

        if(empty($order)){
            $order = ['uid','asc'];
        }

        if(!is_array($order)){
            $order = explode(',',$order);
        }

        $list = self::create()->where($where)->field($fields)->order(...$order)->limit(...$limit)->all();

        // foreach ($list as $k=>$user) {
        //     $list[$k]['parsing_has'] = $this->parsingHas($user['has']);
        //     // $user['avatar'] = $this->getAvatar($user['avatar']);
        // }

        // unset($user);

        return $list;
    }

    /**
     * 获取单个用户信息
     * @param array $where
     * @return array|mixed
     */
    public function getUserInfo(array $where){

        $list = $this->getUserList($where, '', 1);
    
        if (empty($list)) {
            return [];
        }

        return $list[0];
    }

    // /**
    //  * 解析模块标签
    //  * @param string $has
    //  * @return array|string
    //  */
    // public function parsingHas(string $has){

    //     if(substr_count($has,$this->_hasSymbol) == 0){
    //         return $has;
    //     }

    //     return explode($this->_hasSymbol,$has,2);
    // }

    /**
     * 验证模块名称
     * @param string $has
     * @return bool
     */
    public function verifyHas(string $has){

        //未出现指定符号
        if(substr_count($has,$this->_hasSymbol) == 0){
            return true;
        }

        $arr = explode($this->_hasSymbol,$has);

        if(count($arr) != 2 || $arr[0]=='' || $arr[1] == ''){
            return $this->error('标签格式错误!');
        }

        return true;
    }

    /**
     * 获取用户信息
     * @param $uid
     * @return array|mixed
     */
    public function userInfo($uid){
        return $this->getUserInfo(['uid' => $uid]);
    }

}