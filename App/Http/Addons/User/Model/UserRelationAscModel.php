<?php

namespace App\Http\Addons\User\Model;

use Extend\Base\BaseModel;
use EasySwoole\ORM\Utility\Schema\Table;


class UserRelationAscModel extends BaseModel{

    //对应的表名
    protected $tableName = MYSQL_PREFIX . 'user_relation_asc';

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
        $table->colText('relation');
        return $table;
    }


}