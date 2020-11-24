<?php

namespace App\Http\Admin\Model;

use Extend\Base\BaseModel;
use EasySwoole\ORM\Utility\Schema\Table;
use EasySwoole\ORM\DbManager;

class AdminLogModel extends BaseModel
{
    //对应的表名
    protected $tableName = MYSQL_PREFIX . 'admin_log';

    //主键字段
    public $primaryKey = 'id';

    protected $autoTimeStamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected static function getIpAttr($value, $data)
    {
        return !empty($value) ? long2ip($value) : '';
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
        $table->colInt('admin_id', 10);
        $table->colVarchar('username', 32);
        $table->colVarchar('url', 255);
        $table->colVarchar('class', 255);
        $table->colVarchar('title', 100);
        $table->colText('content');
        $table->colBigInt('ip', 20);
        $table->colVarchar('useragent', 255);
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
     * 通过主键删除
     * @param integer $id
     * @return boolean
     */
    public static function deleteInfoByMap(array $map): bool
    {

        $b = self::create()->where($map)->destroy();
        if (!$b) {
            return false;
        }



        return true;
    }
}
