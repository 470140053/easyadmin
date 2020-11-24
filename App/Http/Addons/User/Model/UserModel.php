<?php

namespace App\Http\Addons\User\Model;

use Extend\Base\BaseModel;
use EasySwoole\ORM\Utility\Schema\Table;
use EasySwoole\ORM\DbManager;

use App\Http\Addons\User\Assemble\UserAssemble;

class UserModel extends BaseModel
{

    //对应的表名
    protected $tableName = MYSQL_PREFIX . 'user';

    //主键字段
    public $primaryKey = 'id';

    protected $assemble = UserAssemble::class;

    protected $autoTimeStamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected static $randStr = 'tp_user';

    public static $redisPrefix = 'user_reids_info_';

    /**
     * 表的获取
     * 此处需要返回一个 EasySwoole\ORM\Utility\Schema\Table
     * @return Table
     */
    public function schemaInfo(bool $isCache = true): Table
    {
        $table = new Table($this->tableName);
        $table->colInt('id')->setIsPrimaryKey(true);
        $table->colVarChar('username', 32);
        $table->colVarChar('password', 32);
        $table->colVarChar('salt', 8);
        $table->colVarChar('pay_pwd', 32);
        $table->colVarChar('pay_salt', 8);
        $table->colBigInt('phone', 15);
        $table->colVarChar('nickname', 32);
        $table->colTinyInt('sex', 1);
        $table->colVarChar('avatar', 255);
        $table->colVarChar('avatar_text', 255);
        $table->colVarChar('openid', 64);
        $table->colInt('birthday', 10);
        $table->colBigInt('reg_ip', 20);
        $table->colTinyInt('reg_type', 1);
        $table->colInt('last_login_time', 10);
        $table->colBigInt('last_login_ip', 20);
        $table->colVarChar('qr_code_thumb', 255);
        $table->colTinyInt('status', 1);
        $table->colTinyInt('auth', 1);
        $table->colTinyInt('is_auth', 1);
        $table->colInt('auth_time', 10);
        $table->colInt('pid', 10);
        $table->colInt('ppid', 10);
        $table->colInt('pppid', 10);
        $table->colVarChar('pid_username', 32);
        $table->colVarChar('ppid_username', 32);
        $table->colVarChar('pppid_username', 32);
        $table->colDecimal('pid_ratio', 10, 2);
        $table->colDecimal('ppid_ratio', 10, 2);
        $table->colDecimal('pppid_ratio', 10, 2);
        $table->colVarChar('realname', 32);
        $table->colVarChar('country', 32);
        $table->colVarChar('province', 32);
        $table->colVarChar('city', 32);
        $table->colVarChar('area', 32);
        $table->colInt('create_time', 10);
        $table->colInt('update_time', 10);
        return $table;
    }

    /**
     * 分页数据
     */
    public static function getListByPage(array $map, int $page, int $total, array $field = ['*'], array $order = ['id', 'DESC']): array
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
     * 密码
     * @param [type] $value
     * @param [type] $salt
     * @return void
     */
    public static function setPassword($value, $salt)
    {
        return md5(md5($value . self::$randStr) . $salt);
    }


    /**
     * Undocumented function
     * 注册
     * @param array $data
     * @param array $referee
     * @return boolean
     */
    public static function register(array $data, array $referee): bool
    {
        try {
            DbManager::getInstance()->startTransaction();

            $data['status'] = UserAssemble::create()->setStatus(1);
            $arr = UserAssemble::packageData($data);
            $arr['reg_ip'] = UserAssemble::create()->setRegIp($data['realIp']);
            $arr['pid'] = UserAssemble::create()->setPid($referee['id']);
            $arr['ppid'] = UserAssemble::create()->setPpid($referee['pid']);
            $arr['pppid'] = UserAssemble::create()->setPppid($referee['ppid']);
            $arr['pid_name'] = UserAssemble::create()->setPidUsername($referee['username']);
            $arr['ppid_name'] = UserAssemble::create()->setPpidUsername($referee['pid_username']);
            $arr['pppid_name'] = UserAssemble::create()->setPppidUsername($referee['ppid_username']);
            $res =  self::addInfo($arr);
            if (!$res) {
                DbManager::getInstance()->rollback();
                return false;
            }

            $res1 = UserWalletModel::addInfo(['id' => $res,'status'=>1]);
            if (!$res1) {
                DbManager::getInstance()->rollback();
                return false;
            }

            //查询推荐人的上级关系
            $relation = UserRelationModel::getInfoById($referee['id']);
            //用户ID
            $relationArr['id'] = $res;
            //关系链
            $relationArr['relation'] = (string) !empty($relation) ?  $relation['relation'] . ',' . $res : $res;

            $res2 = UserRelationModel::addInfo($relationArr);
            if (!$res2) {
                DbManager::getInstance()->rollback();
                return false;
            }

            //所有上级关系与当前用户绑定关系
            $sql = 'UPDATE ' . (UserRelationAscModel::create()->getTableName()) . ' SET `relation` = CONCAT(`relation`,",","' . $res . '") WHERE `id` in (' . implode(',', array_filter(explode(',', $relationArr['relation']))) . ')';
            $res3 = UserRelationAscModel::create()->func(function ($builder) use ($sql) {
                $builder->raw($sql);
            });
            if (!$res3) {
                DbManager::getInstance()->rollback();
                return false;
            }

            //当前用户增加团队关系网记录
            $relationAsc['id'] = $res;
            $relationAsc['relation'] = (string) $res;
            $res4 = UserRelationAscModel::addInfo($relationAsc);
            if (!$res4) {
                DbManager::getInstance()->rollback();
                return false;
            }

            //增加一个定时任务 处理用户注册后的事情 比如发优惠券和通知什么的
            queue()->add(\Service\UserServer::class, 'register', [$res], (time() + 5));

            DbManager::getInstance()->commit();
            return true;
        } catch (\Exception $exception) {
            DbManager::getInstance()->rollback();
            return false;
        } 
    }



    /**
     * Undocumented function
     * 记录登录时间与IP
     * @param integer $uid
     * @param string $ip
     * @return string
     */
    public static function setLoginMsg(int $id, string $ip = null): string
    {

        $arr['last_login_time'] = time();
        $arr['last_login_ip'] = !empty($ip) ? ip2long($ip) : 0;

        $map['id'] = $id;
        $b = self::updateInfo($map, $arr);

        if (!$b) {
            return false;
        }

        return self::returnAppstr($id);
    }



    /**
     * Undocumented function
     * 生成登录码
     * @param [type] $uid
     * @return string|null
     */
    public static function returnAppstr(int $uid): ?string
    {
        $redis = eRedis('redis', (3 * 3600));
        $appstr = $redis->get(self::$redisPrefix . $uid);
        $redis->del(self::$redisPrefix . $uid);
        $redis->del(self::$redisPrefix . $appstr);


        $map['id'] = $uid;
        $map['status'] = 1;
        $user = self::getInfoByMap($map, ['id', 'username', 'nickname']);
        if (empty($user)) {
            return null;
        }


        $tmp = [];

        $tmp['id'] = $uid;

        $tmp['username'] = $user['username'];

        $tmp['nickname'] = $user['nickname'];

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


    /**
     * Undocumented function
     * 更新状态
     * @param integer $id
     * @param integer $status
     * @return void
     */
    public static function updateUserStatus(int $id,int $status) {
        $map['id'] = $id;
        try {
            DbManager::getInstance()->startTransaction();
            $res = self::updateInfo($map,['status'=>$status]);
            if (!$res) {
                DbManager::getInstance()->rollback();
                return false;
            }

            $res1 = UserWalletModel::updateInfo($map,['status'=>$status]);
            if (!$res1) {
                DbManager::getInstance()->rollback();
                return false;
            }

            DbManager::getInstance()->commit();
            return true;
        } catch (\Exception $exception) {
            DbManager::getInstance()->rollback();
            return false;
        }
        
    }


    /**
     * Undocumented function
     * 删除用户
     * @param integer $id
     * @return void
     */
    public static function deleteUser(int $id) {
        try {
            DbManager::getInstance()->startTransaction();
            $res = self::create()->destroy($id);
            if (!$res) {
                DbManager::getInstance()->rollback();
                return false;
            }

            $res1 = UserWalletModel::create()->destroy($id);
            if (!$res1) {
                DbManager::getInstance()->rollback();
                return false;
            }

            $res2 = UserRelationAscModel::create()->destroy($id);
            if (!$res2) {
                DbManager::getInstance()->rollback();
                return false;
            }

            $res3 = UserRelationModel::create()->destroy($id);
            if (!$res3) {
                DbManager::getInstance()->rollback();
                return false;
            }
            DbManager::getInstance()->commit();
            return true;
        } catch (\Exception $exception) {
            DbManager::getInstance()->rollback();
            return false;
        }
        
    }

    

    public static function frozen($data) {
        try {
            DbManager::getInstance()->startTransaction();

            $res = self::updateStatusById($data['id'],2);
            if (!$res) {
                DbManager::getInstance()->rollback();
                return false;
            }

            $res1 = UserWalletModel::updateStatusById($data['id'],2);
            if (!$res1) {
                DbManager::getInstance()->rollback();
                return false;
            }


            //封号记录
            $log['uid'] = $data['id'];
            $log['note'] = $data['note'];
            $log['admin_id'] = $data['user']['id'];
            $log['admin_username'] = $data['user']['username'];

            $res2 = UserFrozenModel::addInfo($log);
            if (!$res2) {
                DbManager::getInstance()->rollback();
                return false;
            }

            DbManager::getInstance()->commit();
            return true;
        } catch (\Exception $exception) {
            DbManager::getInstance()->rollback();
            return false;
        }
    }


}
