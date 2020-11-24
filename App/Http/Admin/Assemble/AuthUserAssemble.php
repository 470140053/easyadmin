<?php

namespace App\Http\Admin\Assemble;


use Extend\Base\BaseAssemble;
use App\Http\Admin\Model\AuthUserModel;

class AuthUserAssemble extends BaseAssemble
{

    protected $newData = [];

    protected $model = AuthUserModel::class;
    /**
     * Undocumented function
     * 静态调用
     * @return void
     */
    public static function create(): AuthUserAssemble
    {
        return new self();
    }


    /**
     * Undocumented function
     * 组装数组 【多用于添加，组装数组】
     * @param [type] $data
     * @return void
     */
    public static function packageData(array $data): array
    {
        $obj = self::create();
        $modelColumns = $obj->model::create()->schemaInfo();
        if (!empty($modelColumns->getColumns())) {
            $noNeedField = $obj->noNeedField;

            foreach ($modelColumns->getColumns() as $k => $v) {
                if (!in_array($k, $noNeedField)) {
                    $name = 'set' . convertUnderline($k);

                    if (method_exists($obj, $name)) {
                        $obj->$name((isset($data[$k]) ? $data[$k] : null), $data);
                    }
                }
            }
        }

        return $obj->newData;
    }




    /**
     * Undocumented function
     * 组装数组 【多用于添加，组装数组】
     * @param [type] $data
     * @return void
     */
    public static function package(array $data): array
    {
        $obj = self::create();
        $modelColumns = $obj->model::create()->schemaInfo();
        if (!empty($modelColumns->getColumns())) {
            $noNeedField = $obj->noNeedField;

            foreach ($data as $k => $v) {
                if (!in_array($k, $noNeedField)) {
                    $name = 'set' . convertUnderline($k);

                    if (method_exists($obj, $name)) {
                        $obj->$name((isset($data[$k]) ? $data[$k] : null), $data);
                    }
                }
            }
        }
        return $obj->newData;
    }




    /**
     * Undocumented function
     * 会员图片
     * @param [type] $field
     * @param [type] $data
     * @return void
     */
    public function setAvatar($value, $data = []): int
    {
        $field = 'avatar';
        if (!empty($value)) {
            $this->newData[$field] = intval($value);
        } else {
            $this->newData[$field] = 0;
        }

        return $this->newData[$field];
    }


    /**
     * Undocumented function
     * 是否在线 1-在线 0-离线
     * @param [type] $field
     * @param [type] $data
     * @return void
     */
    public function setOnline($value, $data = []): int
    {
        $field = 'online';
        if (!empty($value)) {
            $this->newData[$field] = 1;
        } else {
            $this->newData[$field] = 0;
        }

        return $this->newData[$field];
    }
}
