<?php

namespace App\Http\Admin\Model;

use Extend\Base\BaseModel;
use EasySwoole\ORM\Utility\Schema\Table;
use EasySwoole\ORM\DbManager;
use EasySwoole\Mysqli\QueryBuilder;


class CoverModel extends BaseModel
{

    //对应的表名
    protected $tableName = MYSQL_PREFIX . 'cover';

    //主键字段
    public $primaryKey = 'id';

    protected $autoTimeStamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected static function getSrcTextAttr($value, $data)
    {
        if (!empty($data['path'])) {
            $host = !empty($data['host']) ? $data['host'] . '/' : '';

            $path = $host . $data['path'];

            $value = (new \App\Common\QiNiuUpload())->returnAuthPath($path);
        }


        return $value;
    }


    protected static function getTypeTextAttr($value, $data)
    {
        $str = '';
        if (!empty($data['type'])) {
            switch ($data['type']) {
                case 2:
                    $str = '视频';
                    break;
                case 3:
                    $str = '文件';
                    break;
                default:
                    $str = '图片';
                    break;
            }
        }


        return  $str;
    }




    protected static function getIsPrivateTextAttr($value, $data)
    {
        $str = '';
        if (!empty($data['is_private'])) {
            $str = '私有存储';
        } else {
            $str = '公有存储';
        }


        return  $str;
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
        $table->colVarChar('path', 500);
        $table->colVarChar('host', 255);
        $table->colVarChar('src_text', 255);
        $table->colVarChar('name', 100);
        $table->colVarChar('hash', 255);
        $table->colVarChar('type', 50);
        $table->colVarChar('type_text', 50);
        $table->colInt('create_time', 10);
        $table->colInt('update_time', 10);
        $table->colTinyInt('is_private', 1);
        $table->colTinyInt('is_private_text', 1);
        $table->colInt('size', 10);

        return $table;
    }


    /**
     * 分页数据
     */
    public static function getListByPage(array $map, int $page, int $total, array $field = ['*', 'null as src_text', '0 as isCheck', 'null as is_private_text', 'null as type_text'], array $order = ['id', 'DESC']): array
    {

        $model = self::create()->limit((($page - 1) * $total), $total)->withTotalCount();

        // 列表数据
        $list = $model->where($map)->field($field)->order(...$order)->all(null);

        $result = $model->lastQueryResult();
        // 总条数
        $totalCount = $result->getTotalCount();

        return [$list, $totalCount];
    }
}
