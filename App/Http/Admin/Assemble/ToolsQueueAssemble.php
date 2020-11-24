<?php

namespace App\Http\Admin\Assemble;


use Extend\Base\BaseAssemble;
use App\Http\Admin\Model\ToolsQueueModel;

class ToolsQueueAssemble extends BaseAssemble
{

    protected $newData = [];

    protected $model = ToolsQueueModel::class;
    /**
     * Undocumented function
     * 静态调用
     * @return void
     */
    public static function create(): ToolsQueueAssemble
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
     * 执行类
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setClass($value, $data = []): string
    {
        $field = 'class';
        if (!empty($value)) {
            $this->newData[$field] = trim($value);
        } else {
            $this->newData[$field] = '';
        }
        return $this->newData[$field];
    }



    /**
     * Undocumented function
     * 参数
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setArgs($value, $data = []): string
    {
        $field = 'args';
        if (!empty($value)) {
            $this->newData[$field] = trim($value);
        } else {
            $this->newData[$field] = '';
        }
        return $this->newData[$field];
    }


    /**
     * Undocumented function
     * 时间
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setTime($value, $data = []): int
    {
        $field = 'time';
        if (!empty($value)) {
            if (strtotime($value)) {
                $this->newData[$field] = strtotime($value);
            } else {
                $this->newData[$field] = intval($value);
            }
        } else {
            $this->newData[$field] = 0;
        }
        return $this->newData[$field];
    }


    /**
     * Undocumented function
     * 次数
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setNum($value, $data = []): int
    {
        $field = 'num';
        if (!empty($value)) {
            $this->newData[$field] = intval($value);
        } else {
            $this->newData[$field] = 0;
        }
        return $this->newData[$field];
    }




    /**
     * Undocumented function
     * 间隔
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setDelay($value, $data = []): int
    {
        $field = 'delay';
        if (!empty($value)) {
            $this->newData[$field] = intval($value);
        } else {
            $this->newData[$field] = 0;
        }
        return $this->newData[$field];
    }

    /**
     * Undocumented function
     * 0单次 1循环
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setMode($value, $data = []): int
    {
        $field = 'mode';
        if (!empty($value)) {
            $this->newData[$field] = intval($value);
        } else {
            $this->newData[$field] = 0;
        }
        return $this->newData[$field];
    }
}
