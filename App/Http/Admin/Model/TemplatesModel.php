<?php

namespace App\Http\Admin\Model;

use Extend\Base\BaseModel;
use EasySwoole\ORM\Utility\Schema\Table;

class TemplatesModel extends BaseModel
{
    //对应的表名
    protected $tableName = MYSQL_PREFIX . 'templates';

    //主键字段
    public $primaryKey = 'id';

    protected $autoTimeStamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    public static $tplArr = [
        'table' => 'base/list/index',
        'tree' => 'base/tree/index',
        'form' => 'base/form/index',
    ];

    protected function getTemplateContentAttr($value, $data)
    {
        $arr = [];
        if (is_string($value)) {
            $arr = json_decode($value, true);
        }

        return $arr;
    }

    protected function setTemplateContentAttr($value, $data)
    {
        $str = '';
        if (!empty($value) && is_array($value)) {
            $str = json_encode($value);
        } else {
            $str = $value;
        }

        return $str;
    }


    protected function getIsFooterAttr($value, $data)
    {
        return ($value == 1) ? true : false;
    }

    protected function setIsFooterAttr($value, $data)
    {
        return $value ? 1 : 0;
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
        $table->colVarChar('title', 50);
        $table->colVarChar('alias', 50);
        $table->colVarChar('template_type', 20);
        $table->colVarChar('index_url', 255);
        $table->colVarChar('ajax_url', 255);
        $table->colText('template_content');
        $table->colTinyInt('status', 1);
        $table->colInt('create_time', 10);
        $table->colInt('update_time', 10);
        $table->colTinyInt('is_footer', 1);
        $table->colVarChar('note', 200);
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
     * 添加、编辑
     * @param array $data
     * @param string $type
     * @return void
     */
    public static function saveData(array $data, string $type)
    {
        if ($type === 'edit') {
            $pk = self::create()->primaryKey;
            if (empty($data[$pk])) {
                return false;
            }
            $map[$pk] = $data[$pk];
            return self::updateInfo($map, $data);
        } else {
            return self::addInfo($data);
        }
    }


    /**
     * Undocumented function
     * 根据别名查询单条数据
     * @param string $alias
     * @param array $field
     * @param integer $id
     * @return array|null
     */
    public static function getInfoByAlias(string $alias, array $field = ['*'], int $id = null): ?array
    {
        $map['alias'] = $alias;
        if (!empty($id)) {
            $map['id'] = [$id, '<>'];
        }
        return self::getInfoByMap($map, $field);
    }
}
