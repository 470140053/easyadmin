<?php

namespace App\Http\Admin\Assemble;


use Extend\Base\BaseAssemble;
use App\Http\Admin\Model\AuthGroupModel;

class AuthGroupAssemble extends BaseAssemble
{

    protected $newData = [];

    protected $model = AuthGroupModel::class;
    /**
     * Undocumented function
     * 静态调用
     * @return void
     */
    public static function create(): AuthGroupAssemble
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
     * 规则名称
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setRules($value, $data = []): string
    {
        $field = 'rules';
        if (!empty($value)) {
            if (is_array($value)) {
                $this->newData[$field] = implode(',', $value);
            } else {
                $this->newData[$field] = $value;
            }
        } else {
            $this->newData[$field] = '';
        }
        return $this->newData[$field];
    }
}
