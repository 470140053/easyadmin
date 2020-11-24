<?php

namespace App\Http\Addons\User\Model;

use Extend\Base\BaseModel;
use EasySwoole\ORM\Utility\Schema\Table;


class UserWalletLogModel extends BaseModel{

    //对应的表名
    protected $tableName = MYSQL_PREFIX . 'user_wallet_log';

    //主键字段
    public $primaryKey = 'id';

    protected $autoTimeStamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    /**
     * 表的获取
     * 此处需要返回一个 EasySwoole\ORM\Utility\Schema\Table
     * @return Table
     */
    public function schemaInfo(bool $isCache = true): Table
    {
        $table = new Table($this->tableName);
        $table->colInt('id')->setIsPrimaryKey(true);
        $table->colInt('uid',10);
        $table->colTinyInt('type',1);
        $table->colTinyInt('flag',1);
        $table->colTinyInt('status',1);
        $table->colDecimal('amount',10,2);
        $table->colDecimal('balance',10,2);
        $table->colDecimal('frozen_balance',10,2);
        $table->colVarChar('note',200);
        $table->colVarChar('type_text',32);
        $table->colVarChar('flag_text',32);
        $table->colInt('source_id',10);
        $table->colVarChar('source_realname',32);
        $table->colInt('create_time',10);
        $table->colInt('update_time',10);
        $table->colVarChar('table_name',50);
        return $table;
    }

    
}