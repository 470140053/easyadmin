<?php

namespace App\Http\Admin\Assemble;


use Extend\Base\BaseAssemble;
use App\Http\Admin\Model\AuthRuleModel;
use App\Http\Admin\Model\TemplatesModel;

class AuthRuleAssemble extends BaseAssemble
{

    protected $newData = [];

    protected $model = AuthRuleModel::class;
    /**
     * Undocumented function
     * 静态调用
     * @return void
     */
    public static function create(): AuthRuleAssemble
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


    public function setTplType($value, $data = []): int
    {
        $field = 'tpl_type';
        if (!empty($value)) {
            $this->newData[$field] = intval($value);
        } else {
            $this->newData[$field] = 0;
        }
        return $this->newData[$field];
    }

    public function setIdentifying($value, $data = []): string
    {
        $field = 'identifying';
        if (!empty($value)) {
            $this->newData[$field] = trim($value);
        } else {
            $this->newData[$field] = '';
        }
        return $this->newData[$field];
    }

    
    public function setTplAlias($value, $data = []): string
    {
        $field = 'tpl_alias';
        if (!empty($value) && isset($data['tpl_type']) && $data['tpl_type'] === 0) {
            $this->newData[$field] = trim($value);
        } else {
            $this->newData[$field] = '';
        }
        return $this->newData[$field];
    }


    public function setTpl($value, $data = []): string
    {
        $field = 'tpl';
        if (!empty($data['tpl_type'])) {
            if (!empty($value)) {
                $this->newData[$field] = trim($value);
            } else {
                $this->newData[$field] = '';
            }
        } else if (!empty($data['tpl_alias'])) {
            $tplInfo = TemplatesModel::getInfoByMap(['alias' => $data['tpl_alias']], ['template_type']);
            if (!empty($tplInfo)) {
                $this->newData[$field] = TemplatesModel::$tplArr[$tplInfo['template_type']];
            } else {
                $this->newData[$field] = '';
            }
        } else {
            $this->newData[$field] = '';
        }

        return $this->newData[$field];
    }
}
