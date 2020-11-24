<?php

namespace App\Http\Admin\Model;

use Extend\Base\BaseModel;
use EasySwoole\ORM\Utility\Schema\Table;
use App\Http\Admin\Assemble\FriendLinkAssemble;

class FriendLinkModel extends BaseModel
{

    //对应的表名
    protected $tableName = MYSQL_PREFIX . 'friend_link';

    //主键字段
    public $primaryKey = 'id';

    protected $assemble = FriendLinkAssemble::class;

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
        $table->colInt('aid', 10);
        $table->colInt('app_id', 10);
        $table->colInt('sort', 10);
        $table->colInt('create_time', 10);
        $table->colInt('update_time', 10);
        $table->colVarChar('title', 100);
        $table->colVarChar('url', 255);
        $table->colInt('thumb', 10);
        $table->colVarChar('thumb_text', 255);
        $table->colTinyInt('status', 1);
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
}
