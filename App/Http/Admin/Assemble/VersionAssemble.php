<?php

namespace App\Http\Admin\Assemble;


use Extend\Base\BaseAssemble;
use App\Http\Admin\Model\VersionModel;

class VersionAssemble extends BaseAssemble
{

    protected $newData = [];

    protected $model = VersionModel::class;
    /**
     * Undocumented function
     * 静态调用
     * @return void
     */
    public static function create(): VersionAssemble
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
     * 旧版本号
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setOldversion($value, $data = []): string
    {
        $field = 'oldversion';
        if (!empty($value)) {
            $this->newData[$field] = trim($value);
        } else {
            $this->newData[$field] = '';
        }
        return $this->newData[$field];
    }

    /**
     * Undocumented function
     * 新版本号
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setNewversion($value, $data = []): string
    {
        $field = 'newversion';
        if (!empty($value)) {
            $this->newData[$field] = trim($value);
        } else {
            $this->newData[$field] = '';
        }
        return $this->newData[$field];
    }


    /**
     * Undocumented function
     * 包大小
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setPackagesize($value, $data = []): string
    {
        $field = 'packagesize';
        if (!empty($value)) {
            $this->newData[$field] = trim($value);
        } else {
            $this->newData[$field] = '';
        }
        return $this->newData[$field];
    }



    /**
     * Undocumented function
     * 下载地址
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setDownloadId($value, $data = []): int
    {
        $field = 'download_id';
        if (!empty($value)) {
            $this->newData[$field] = intval($value);
        } else {
            $this->newData[$field] = 0;
        }
        return $this->newData[$field];
    }




    /**
     * Undocumented function
     * 强制更新 1-是 0-否
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setEnforce($value, $data = []): int
    {
        $field = 'enforce';
        if (!empty($value)) {
            $this->newData[$field] = intval($value);
        } else {
            $this->newData[$field] = 0;
        }
        return $this->newData[$field];
    }


    /**
     * Undocumented function
     * 权重
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setWeigh($value, $data = []): int
    {
        $field = 'weigh';
        if (!empty($value)) {
            $this->newData[$field] = intval($value);
        } else {
            $this->newData[$field] = 0;
        }
        return $this->newData[$field];
    }
}
