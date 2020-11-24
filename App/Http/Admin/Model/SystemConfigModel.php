<?php

namespace App\Http\Admin\Model;

use Extend\Base\BaseModel;
use EasySwoole\ORM\Utility\Schema\Table;
use EasySwoole\ORM\DbManager;

class SystemConfigModel extends BaseModel
{
    //对应的表名
    protected $tableName = MYSQL_PREFIX . 'system_config';

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
        $table->colVarchar('title', 100);
        $table->colVarchar('name', 30);
        $table->colVarchar('group', 30);
        $table->colVarchar('value', 300);
        return $table;
    }



    public static function getListByGroupName(string $group = null) : array  {
        $redis = eRedis();
        $arr = [];
        $arr = json_decode($redis->get(self::create()->getTableName),true);
        if(empty($arr)) {
            $list = self::create()->all();
            if(!empty($list)) {
                $list = voidToArray($list);
                foreach($list as $v) {
                    $arr[$v['group']][$v['name']] = $v['value'];
                }

                $redis->set(self::create()->getTableName,json_encode($arr),7200);
            }
        }
        
        return !empty($arr[$group]) ? $arr[$group] : [];

    }

}