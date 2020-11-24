<?php

namespace App\Http\Admin\Model;

use Extend\Base\BaseModel;
use EasySwoole\ORM\Utility\Schema\Table;
use EasySwoole\ORM\DbManager;

use App\Http\Admin\Assemble\AuthRuleAssemble;

class AuthRuleModel extends BaseModel
{
    //对应的表名
    protected $tableName = MYSQL_PREFIX . 'auth_rule';

    //主键字段
    public $primaryKey = 'id';

    protected $assemble = AuthRuleAssemble::class;


    protected static function getIsmenuAttr($value, $data)
    {
        return !empty($value) ? true : false;
    }

    protected static function getTplTypeAttr($value, $data)
    {
        return !empty($value) ? true : false;
    }

    protected static function getPidPathAttr($value, $data)
    {
        return !empty($value) ? explode(',', $value) : [];
    }

    protected static function getTplIdAttr($value, $data)
    {
        return !empty($value) ? $value : (!empty($data['tpl']) ? $data['tpl'] : 0);
    }



    /**
     * 表的获取
     * 此处需要返回一个 EasySwoole\ORM\Utility\Schema\Table
     * @return Table
     */
    public function schemaInfo(bool $isCache = true): Table
    {
        $table = new Table($this->tableName);
        $table->colMediumInt('id')->setIsPrimaryKey(true);
        $table->colMediumInt('pid', 9);
        $table->colVarChar('pid_path', 30);
        $table->colChar('name', 20);
        $table->colChar('title', 20);
        $table->colTinyInt('type', 1);
        $table->colTinyInt('sort', 4);
        $table->colTinyInt('status', 1);
        $table->colVarChar('icon', 50);
        $table->colTinyInt('ismenu', 1);
        $table->colInt('tpl_id', 10);
        $table->colTinyInt('tpl_type', 1);
        $table->colVarChar('tpl_alias', 50);
        $table->colVarChar('tpl', 32);
        $table->colVarChar('identifying', 32);
        

        return $table;
    }



    public static function getChildrenList(int $id): array
    {
        $list = self::create()->where('find_in_set(?,pid_path)', [$id])->field('id')->all(null);
        if (!empty($list)) {
            return array_unique(array_column($list, 'id'));
        }
        return [];
    }


    /**
     * Undocumented function
     * 通过主键删除
     * @param integer $id
     * @return boolean
     */
    public static function deleteInfoById(int $id): bool
    {
        $list = self::getChildrenList($id);
        $list[] = $id;
        $map['id'] = [$list, 'in'];
        $b = self::create()->destroy($map);
        if (!$b) {
            return false;
        }

        return true;
    }


    /**
     * Undocumented function
     * 创建
     * @param array $data
     * @param integer $pid
     * @return boolean
     */
    public static function createMenu(array $data,int $pid = 0,array $pid_path=[]) : bool{
        try {
            DbManager::getInstance()->startTransaction();
            foreach ($data as $v) {
                $v['pid'] = $pid;
                $v['pid_path'] = $pid_path;
                $arr = self::create()->assemble::packageData($v);
                $id = self::addInfo($arr);

                if(!empty($v['sublist'])) {
                    self::createMenu($v['sublist'],$id,array_unique(array_merge($pid_path,[$id])));
                }
            }

            DbManager::getInstance()->commit();
            return true;
        } catch (\Exception $exception) {
            DbManager::getInstance()->rollback();
            return false;
        }

    }

    /**
     * Undocumented function
     * 删除
     * @param string $identifying
     * @return boolean
     */
    public static function delMenu(string $identifying) : bool{
        $map['identifying'] = $identifying;
        return self::create()->where($map)->destroy();
    }


    /**
     * Undocumented function
     * 启用
     * @param string $identifying
     * @return boolean
     */
    public static function enableMenu(string $identifying) : bool{
        $map['identifying'] = $identifying;
        $arr['status'] = 1;
        return self::updateInfo($map,$arr);
    }


    /**
     * Undocumented function
     * 禁用
     * @param string $identifying
     * @return boolean
     */
    public static function disableMenu(string $identifying) : bool{
        $map['identifying'] = $identifying;
        $arr['status'] = 0;
        return self::updateInfo($map,$arr);
    }
}
