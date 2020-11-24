<?php

namespace App\Http\Admin\Assemble;

use Extend\Base\BaseAssemble;
use App\Http\Admin\Model\PlugModel;

class PlugAssemble extends BaseAssemble
{

    protected $newData = [];

    protected $model = PlugModel::class;
    /**
     * Undocumented function
     * 静态调用
     * @return void
     */
    public static function create(): PlugAssemble
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
     * 
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setVersion($value, $data = []): string
    {
        $field = 'version';
        if (!empty($value)) {
            $this->newData[$field] = trim($value);
        } else {
            $this->newData[$field] = '';
        }
        return $this->newData[$field];
    }


    /**
     * Undocumented function
     * 
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setShowHowUrl($value, $data = []): string
    {
        $field = 'show_how_url';
        if (!empty($value)) {
            $this->newData[$field] = trim($value);
        } else {
            $this->newData[$field] = '';
        }
        return $this->newData[$field];
    }


    /**
     * Undocumented function
     * 
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setDocumentUrl($value, $data = []): string
    {
        $field = 'document_url';
        if (!empty($value)) {
            $this->newData[$field] = trim($value);
        } else {
            $this->newData[$field] = '';
        }
        return $this->newData[$field];
    }


    /**
     * Undocumented function
     * 
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setVersionId($value, $data = []): string
    {
        $field = 'version_id';
        if (!empty($value)) {
            $this->newData[$field] = trim($value);
        } else {
            $this->newData[$field] = '';
        }
        return $this->newData[$field];
    }


    /**
     * Undocumented function
     * 
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setUserType($value, $data = []): string
    {
        $field = 'user_type';
        if (!empty($value)) {
            if(is_array($value)) {
                $this->newData[$field] = implode(',',$value);
            }else{
                $this->newData[$field] = trim($value);
            }
            
        } else {
            $this->newData[$field] = '';
        }
        return $this->newData[$field];
    }



      /**
     * Undocumented function
     * 
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setPlatform($value, $data = []): string
    {
        $field = 'platform';
        if (!empty($value)) {
            if(is_array($value)) {
                $this->newData[$field] = implode(',',$value);
            }else{
                $this->newData[$field] = trim($value);
            }
        } else {
            $this->newData[$field] = '';
        }
        return $this->newData[$field];
    }

}