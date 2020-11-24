<?php

namespace App\Http\Admin\Model;

use Extend\Base\BaseModel;
use EasySwoole\ORM\Utility\Schema\Table;
use EasySwoole\ORM\DbManager;
use EasySwoole\Mysqli\QueryBuilder;

use App\Http\Admin\Assemble\AuthUserAssemble;

class AuthUserModel extends BaseModel
{

    //对应的表名
    protected $tableName = MYSQL_PREFIX . 'auth_user';

    //主键字段
    public $primaryKey = 'id';

    protected $assemble = AuthUserAssemble::class;

    protected $autoTimeStamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';


    protected static $randStr = 'tp_auth_user';

    public static $redisPrefix = 'admin_reids_info_';

    public static function setPassword($value, $salt)
    {
        return md5(md5($value . self::$randStr) . $salt);
    }


    protected function getLastLoginIpAttr($value, $data)
    {

        return !empty($value) ? long2ip($value) : '';
    }


    protected function getPhoneAttr($value, $data)
    {

        return ($value === 0) ? '' : $value;
    }


    /**
     * 表的获取
     * 此处需要返回一个 EasySwoole\ORM\Utility\Schema\Table
     * @return Table
     */
    public function schemaInfo(bool $isCache = true): Table
    {

        $table = new Table($this->tableName);
        $table->colInt('id')->setIsPrimaryKey(true);
        $table->colVarChar('username', 20);
        $table->colChar('password', 32);
        $table->colBigInt('phone', 15);
        $table->colVarChar('salt', 8);
        $table->colTinyInt('status', 1);
        $table->colTinyInt('super', 1);
        $table->colVarChar('remark', 255);
        $table->colInt('create_time', 10);
        $table->colInt('last_login_time', 10);
        $table->colBigInt('last_login_ip', 20);
        $table->colVarChar('last_location', 100);
        $table->colVarChar('avatar', 255);
        $table->colVarChar('avatar_text', 255);
        $table->colTinyInt('online', 1);
        $table->colVarChar('nickname', 20);
        $table->colInt('update_time', 10);
        $table->colInt('handle_id', 10);
        return $table;
    }




    /**
     * 分页数据
     */
    public static function getListByPage(array $map, int $page, int $total, array $field = ['*', 'null as avatar_text'], array $order = ['id', 'DESC']): array
    {

        $model = self::create()->limit((($page - 1) * $total), $total)->withTotalCount();
        // 列表数据
        $list = $model->where($map)->field($field)->order(...$order)->all(null);

        $result = $model->lastQueryResult();
        // 总条数
        $totalCount = $result->getTotalCount();

        return [$list, $totalCount];
    }




    /**
     * Undocumented function
     * 单条数据 【查询用户名或手机号是否重复】
     * @param array $map
     * @param array $field
     * @return array|null
     */
    public static function getInfoByUsername(string $phone, array $field = ['*'], int $id = null): ?array
    {
        $map = '(`username` = ' . $phone . ' OR `phone` = ' . $phone . ')';
        if ($id) {
            $map .= ' AND `id` <> ' . $id;
        }
        $info = self::create()->where($map)->field($field)->get();

        return !empty($info) ? $info->toArray() : null;
    }






    /**
     * Undocumented function
     * 生成登录码
     * @param [type] $uid
     * @return string|null
     */
    public static function returnAppstr($uid): ?string
    {
        $redis = eRedis('redis', (3 * 3600));
        $appstr = $redis->get(self::$redisPrefix . $uid);
        $redis->del(self::$redisPrefix . $uid);
        $redis->del(self::$redisPrefix . $appstr);


        $map['id'] = $uid;
        $map['status'] = 1;
        $user = self::getInfoByMap($map, ['id', 'username', 'nickname', 'super']);

        if (empty($user)) {
            return null;
        }

        $tmp = [];

        $tmp['id'] = $uid;

        $tmp['username'] = $user['username'];

        $tmp['nickname'] = $user['nickname'];

        $tmp['super'] = $user['super'];
        //随机码
        $tmp['rand_str'] = rand_string(6);
        //时间
        $tmp['time'] = time();


        //唯一用户登陆识别串
        $md5_str = md5(json_encode($tmp));

        $redis->set(self::$redisPrefix . $md5_str, json_encode($tmp), 86400);

        $redis->set(self::$redisPrefix . $uid, $md5_str, 86400);

        return $md5_str;
    }


    /**
     * Undocumented function
     * 记录登录时间与IP
     * @param integer $uid
     * @param string $ip
     * @return string
     */
    public static function setLoginMsg(int $uid, string $ip = null): string
    {

        $arr['last_login_time'] = time();
        $arr['last_login_ip'] = !empty($ip) ? ip2long($ip) : 0;

        $map['id'] = $uid;
        $b = self::updateInfo($map, $arr);

        if (!$b) {
            return false;
        }



        return self::returnAppstr($uid);
    }



    /**
     * 退出登陆
     * @param type $uid
     */
    public static function outLogin(array $data)
    {

        $redis = eRedis();

        $redis->del(self::$redisPrefix . $data['user']['id']);

        $redis->del(self::$redisPrefix . $data['app_str']);

        return true;
    }
}
