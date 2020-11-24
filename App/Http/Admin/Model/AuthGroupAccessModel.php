<?php

namespace App\Http\Admin\Model;

use Extend\Base\BaseModel;
use EasySwoole\ORM\Utility\Schema\Table;
use EasySwoole\ORM\DbManager;

class AuthGroupAccessModel extends BaseModel
{
    //对应的表名
    protected $tableName = MYSQL_PREFIX . 'auth_group_access';

    //主键字段
    public $primaryKey = 'id';


    /**
     * 表的获取
     * 此处需要返回一个 EasySwoole\ORM\Utility\Schema\Table
     * @return Table
     */
    public function schemaInfo(bool $isCache = true): Table
    {
        $table = new Table($this->tableName);
        $table->colInt('id')->setIsPrimaryKey(true);
        $table->colInt('uid', 10);
        $table->colInt('group_id', 10);
        return $table;
    }


    /**
     * Undocumented function
     * 返回用户的权限规则id
     * @param integer $uid
     * @return array
     */
    public static function getUserGroupListByUid(int $uid, string $field = 'b.rules'): array
    {

        $map['a.uid'] = $uid;

        $map['b.status'] = 1;

        $list = self::create()->where($map)->alias('a')->join(AuthGroupModel::create()->getTableName() . ' b', 'a.group_id = b.id')->field($field)->all();

        $ids = [];

        if (!empty($list)) {

            foreach ($list as $v) {
                $ids = array_merge($ids, explode(',', $v['rules']));
            }
        }

        return array_unique($ids);
    }



    /**
     * Undocumented function
     * 管理员权限分组
     * @param array $data
     * @return boolean
     */
    public static function setPower(array $data): bool
    {
        DbManager::getInstance()->startTransaction();
        try {
            self::create()->where(['uid' => $data['id']])->destroy();

            $arr = [];
            foreach ($data['rules'] as $v) {
                $arr[] = [
                    'uid' => $data['id'],
                    'group_id' =>  $v,
                ];
            }

            $res = self::addInfoAll($arr);

            if ($res) {

                DbManager::getInstance()->commit();

                return true;
            } else {

                DbManager::getInstance()->rollback();

                return false;
            }
        } catch (\Exception $exception) {

            DbManager::getInstance()->rollback();

            return false;
        }
    }
}
