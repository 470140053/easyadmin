<?php

namespace App\Http\Addons\User\Assemble;


use Extend\Base\BaseAssemble;
use App\Http\Addons\User\Model\UserModel;

class UserAssemble extends BaseAssemble
{

    protected $newData = [];

    protected $model = UserModel::class;
    /**
     * Undocumented function
     * 静态调用
     * @return void
     */
    public static function create(): UserAssemble
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
     * 注册IP
     * @param [type] $field
     * @param [type] $data
     * @return void
     */
    public function setRegIp($value, $data = []): int
    {
        $field = 'reg_ip';
        if (!empty($value)) {
            if(ip2long($value)) {
                $this->newData[$field] = ip2long($value);
            }else{
                $this->newData[$field] = intval($value);
            }
        } else {
            $this->newData[$field] = 0;
        }

        return $this->newData[$field];
    }



    /**
     * Undocumented function
     * 0-账号注册 1-手机注册 2-邮箱注册 3-微信注册
     * @param [type] $field
     * @param [type] $data
     * @return void
     */
    public function setRegType($value, $data = []): int
    {
        $field = 'reg_type';
        if (isset($value)) {
            $this->newData[$field] = intval($value);
        } else {
            $this->newData[$field] = 2;
        }

        return $this->newData[$field];
    }



    /**
     * Undocumented function
     * 二维码图片地址
     * @param [type] $field
     * @param [type] $data
     * @return void
     */
    public function setQrCodeThumb($value, $data = []): int
    {
        $field = 'qr_code_thumb';
        if (!empty($value)) {
            $this->newData[$field] = $value;
        } else {
            $this->newData[$field] = 0;
        }

        return $this->newData[$field];
    }



    /**
     * Undocumented function
     * 上一级ID
     * @param [type] $field
     * @param [type] $data
     * @return void
     */
    public function setPid($value, $data = []): int
    {
        $field = 'pid';
        if (!empty($value)) {
            $this->newData[$field] = intval($value);
        } else {
            $this->newData[$field] = 0;
        }

        return $this->newData[$field];
    }

    /**
     * Undocumented function
     * 上二级ID
     * @param [type] $field
     * @param [type] $data
     * @return void
     */
    public function setPpid($value, $data = []): int
    {
        $field = 'ppid';
        if (!empty($value)) {
            $this->newData[$field] = intval($value);
        } else {
            $this->newData[$field] = 0;
        }

        return $this->newData[$field];
    }


    /**
     * Undocumented function
     * 上三级ID
     * @param [type] $field
     * @param [type] $data
     * @return void
     */
    public function setPppid($value, $data = []): int
    {
        $field = 'pppid';
        if (!empty($value)) {
            $this->newData[$field] = intval($value);
        } else {
            $this->newData[$field] = 0;
        }

        return $this->newData[$field];
    }


    /**
     * Undocumented function
     * 上一级用户名
     * @param [type] $field
     * @param [type] $data
     * @return void
     */
    public function setPidUsername($value, $data = []): string
    {
        $field = 'pid_username';
        if (!empty($value)) {
            $this->newData[$field] = trim($value);
        } else {
            $this->newData[$field] = '';
        }

        return $this->newData[$field];
    }


    /**
     * Undocumented function
     * 上二级用户名
     * @param [type] $field
     * @param [type] $data
     * @return void
     */
    public function setPpidUsername($value, $data = []): string
    {
        $field = 'ppid_username';
        if (!empty($value)) {
            $this->newData[$field] = trim($value);
        } else {
            $this->newData[$field] = '';
        }

        return $this->newData[$field];
    }



    /**
     * Undocumented function
     * 上三级用户名
     * @param [type] $field
     * @param [type] $data
     * @return void
     */
    public function setPppidUsername($value, $data = []): string
    {
        $field = 'pppid_username';
        if (!empty($value)) {
            $this->newData[$field] = trim($value);
        } else {
            $this->newData[$field] = '';
        }

        return $this->newData[$field];
    }



    /**
     * Undocumented function
     * 推广比例 下一级
     * @param [type] $field
     * @param [type] $data
     * @return void
     */
    public function setPidRatio($value, $data = []): float
    {
        $field = 'pid_ratio';
        if (!empty($value)) {
            $this->newData[$field] = floatval($value);
        } else {
            $this->newData[$field] = 0.00;
        }

        return $this->newData[$field];
    }


    /**
     * Undocumented function
     * 推广比例 下二级
     * @param [type] $field
     * @param [type] $data
     * @return void
     */
    public function setPpidRatio($value, $data = []): float
    {
        $field = 'ppid_ratio';
        if (!empty($value)) {
            $this->newData[$field] = floatval($value);
        } else {
            $this->newData[$field] = 0.00;
        }

        return $this->newData[$field];
    }


    /**
     * Undocumented function
     * 推广比例 下三级
     * @param [type] $field
     * @param [type] $data
     * @return void
     */
    public function setPppidRatio($value, $data = []): float
    {
        $field = 'pppid_ratio';
        if (!empty($value)) {
            $this->newData[$field] = floatval($value);
        } else {
            $this->newData[$field] = 0.00;
        }

        return $this->newData[$field];
    }



    /**
     * Undocumented function
     * 真实姓名
     * @param [type] $field
     * @param [type] $data
     * @return void
     */
    public function setRealname($value, $data = []): string
    {
        $field = 'realname';
        if (!empty($value)) {
            $this->newData[$field] = trim($value);
        } else {
            $this->newData[$field] = '';
        }

        return $this->newData[$field];
    }



    /**
     * Undocumented function
     * 国家
     * @param [type] $field
     * @param [type] $data
     * @return void
     */
    public function setCountry($value, $data = []): string
    {
        $field = 'country';
        if (!empty($value)) {
            $this->newData[$field] = trim($value);
        } else {
            $this->newData[$field] = '';
        }

        return $this->newData[$field];
    }


    /**
     * Undocumented function
     * 省
     * @param [type] $field
     * @param [type] $data
     * @return void
     */
    public function setProvince($value, $data = []): string
    {
        $field = 'province';
        if (!empty($value)) {
            $this->newData[$field] = trim($value);
        } else {
            $this->newData[$field] = '';
        }

        return $this->newData[$field];
    }

    /**
     * Undocumented function
     * 市
     * @param [type] $field
     * @param [type] $data
     * @return void
     */
    public function setCity($value, $data = []): string
    {
        $field = 'city';
        if (!empty($value)) {
            $this->newData[$field] = trim($value);
        } else {
            $this->newData[$field] = '';
        }

        return $this->newData[$field];
    }

    /**
     * Undocumented function
     * 区
     * @param [type] $field
     * @param [type] $data
     * @return void
     */
    public function setArea($value, $data = []): string
    {
        $field = 'area';
        if (!empty($value)) {
            $this->newData[$field] = trim($value);
        } else {
            $this->newData[$field] = '';
        }

        return $this->newData[$field];
    }
}
