<?php

namespace App\Http\Addons\User\Model;

use Extend\Base\BaseModel;
use EasySwoole\ORM\Utility\Schema\Table;


class UserFrozenModel extends BaseModel{

    //对应的表名
    protected $tableName = MYSQL_PREFIX . 'user_frozen';

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
        $table->colVarChar('note',50);
        $table->colInt('admin_id',10);
        $table->colVarChar('admin_username',32);
        $table->colInt('create_time',10);
        $table->colInt('update_time',10);
        return $table;
    }

    
}