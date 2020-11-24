<?php

namespace App\Http\Admin\Model;

use Extend\Base\BaseModel;
use EasySwoole\ORM\Utility\Schema\Table;
use App\Http\Admin\Assemble\VersionAssemble;

class VersionModel extends BaseModel
{
    //对应的表名
    protected $tableName = MYSQL_PREFIX . 'version';

    //主键字段
    public $primaryKey = 'id';

    protected $assemble = VersionAssemble::class;

    protected $autoTimeStamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';


    /**
     * Undocumented function
     * 扩展属性
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    protected static function getDownloadurlAttr($value, $data)
    {
        return !empty($data['download_id']) ? getThumbUrlAttr($data['download_id']) : '';
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
        $table->colVarChar('oldversion', 30);
        $table->colVarChar('newversion', 30);
        $table->colVarChar('packagesize', 30);
        $table->colVarChar('content', 500);
        $table->colInt('download_id', 10);
        $table->colVarChar('downloadurl', 255);
        $table->colTinyInt('enforce', 1);
        $table->colInt('create_time', 10);
        $table->colInt('update_time', 10);
        $table->colInt('weigh', 10);
        $table->colTinyInt('status', 1);
        $table->colTinyInt('type', 1);
        return $table;
    }

    /**
     * 分页数据
     */
    public static function getListByPage(array $map, int $page, int $total, array $field = ['*'], array $order = ['weigh', 'ASC']): array
    {

        $model = self::create()->limit((($page - 1) * $total), $total)->withTotalCount();

        // 列表数据
        $list = $model->where($map)->field($field)->order(...$order)->all(null);

        $result = $model->lastQueryResult();
        // 总条数
        $totalCount = $result->getTotalCount();

        return [$list, $totalCount];
    }





    public static function getVersionInfo(array $map, $field = ['oldversion', 'newversion', 'packagesize', 'content', 'download_id', 'null as downloadurl', 'enforce', 'type']): ?array
    {
        $info = self::create()->where($map)->order(['newversion', 'DESC'])->field($field)->get();
        return !empty($info) ? $info->toArray() : null;
    }
}
