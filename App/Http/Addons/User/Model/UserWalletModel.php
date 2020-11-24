<?php

namespace App\Http\Addons\User\Model;

use Extend\Base\BaseModel;
use EasySwoole\ORM\Utility\Schema\Table;
use EasySwoole\ORM\DbManager;
use EasySwoole\Mysqli\QueryBuilder;

class UserWalletModel extends BaseModel{

    //对应的表名
    protected $tableName = MYSQL_PREFIX . 'user_wallet';

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
        $table->colTinyInt('level',4);
        $table->colVarChar('level_title',32);
        $table->colInt('level_time',10);
        $table->colTinyInt('primary_level',4);
        $table->colInt('level_expire_time',10);
        $table->colTinyInt('status',1);
        $table->colTinyInt('auth',1);
        $table->colDecimal('balance',10,2);
        $table->colDecimal('balance_text',10,2);
        $table->colInt('score',10);
        $table->colInt('score_text',10);
        $table->colInt('grow',10);
        $table->colInt('grow_text',10);
        return $table;
    }


    public static function setBalance(array $data) : bool {
        try {
            DbManager::getInstance()->startTransaction();

            $log = [];

            $map['id'] = $data['id'];
            if($data['diff_type'] == 1) {
                //增加
                $arr['balance'] = QueryBuilder::inc($data['diff']);

                
                $log['flag'] = 1;
            }else{
                //减少
                $map['balance'] = [$data['diff'],'>='];
                $arr['balance'] = QueryBuilder::dec($data['diff']);

                $log['flag'] = 2;
            }

            $res = self::updateInfo($map,$arr);
            if(!$res) {
                DbManager::getInstance()->rollback();
                return false;
            }

            $log['type'] = 2;
            $log['status'] = 1;
            $log['uid'] = $data['id'];
            $log['amount'] = $data['diff'];
            $log['note'] = '平台客服操作：'.$data['note'];
            $log['source_id'] = $data['user']['id'];
            $log['source_realname'] = $data['user']['username'];
            $res1 = UserWalletLogModel::addInfo($log);
            if(!$res1) {
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

