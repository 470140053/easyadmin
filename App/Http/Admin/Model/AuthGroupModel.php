<?php

namespace App\Http\Admin\Model;

use Extend\Base\BaseModel;
use EasySwoole\ORM\Utility\Schema\Table;

use App\Http\Admin\Assemble\AuthGroupAssemble;

class AuthGroupModel extends BaseModel
{
    //对应的表名
    protected $tableName = MYSQL_PREFIX . 'auth_group';

    //主键字段
    public $primaryKey = 'id';

    protected $assemble = AuthGroupAssemble::class;

    protected $autoTimeStamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';


    protected static function getRulesAttr($value, $data)
    {
        if (isset($data['rules']) && is_string($value)) {
            return explode(',', $value);
        }
        return [];
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
        $table->colVarChar('title', 100);
        $table->colTinyInt('status', 1);
        $table->colText('rules');
        $table->colInt('aid', 10);
        $table->colInt('handle_id', 10);

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
     * 导出数据
     * @param array $map
     * @return array
     */
    public static function getExportList(array $map): array
    {
        $list = self::create()->where($map)->all(null);
        return $list;
    }



    /**
     * 查询此用户的权限数据
     */
    public static function getGroupAndRuleListByUid(int $uid, array $field = ['a.title', 'a.rules', 'b.uid', 'b.group_id']): array
    {

        $map['b.uid']    = $uid;

        $map['a.status'] = 1;

        return self::create()->alias('a')->where($map)->join(AuthGroupAccessModel::create()->getTableName() . ' as b', 'a.id = b.group_id')->field($field)->all();
    }




    public static function getUserGroupIds(int $uid): array
    {
        $map['b.uid'] = $uid;
        $map['a.status'] = 1;
        $list =  self::create()->alias('a')->where($map)
            ->join(AuthGroupAccessModel::create()->getTableName() . ' as b', 'a.id = b.group_id')
            ->column('a.id');

        return !empty($list) ? $list  : [];
    }
}
