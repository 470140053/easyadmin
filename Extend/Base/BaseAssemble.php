<?php

namespace Extend\Base;

abstract class BaseAssemble
{
    /**
     * Undocumented variable
     * 不组装的字段
     * @var array
     */
    protected $noNeedField = [
        'thumb_text',
        'images_text',
        'avatar_text'
    ];


    /**
     * Undocumented function
     * 排序
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setSort($value, $data = []): int
    {
        $field = 'sort';
        $this->newData[$field] = !empty($value) ? intval($value) : 99;
        return $this->newData[$field];
    }


    /**
     * Undocumented function
     * 0-禁用 1-启用 2-删除
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setStatus($value, $data = []): int
    {
        $field = 'status';
        $this->newData[$field] = isset($value) ? intval($value) : 0;
        return $this->newData[$field];
    }


    /**
     * Undocumented function
     * 0-未审核 1-已审核 2-已反审
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setAuth($value, $data = []): int
    {
        $field = 'auth';
        $this->newData[$field] = isset($value) ? intval($value) : 0;
        return $this->newData[$field];
    }


    /**
     * Undocumented function
     * 1-提交审核 0-默认未提交 2-已审核
     * @param [type] $field
     * @param [type] $data
     * @return void
     */
    public function setIsAuth($value, $data = []): int
    {
        $field = 'is_auth';
        if (!empty($value)) {
            $this->newData[$field] = intval($value);
        } else if (isset($data['auth']) && $data['auth'] == 1) {
            $this->newData[$field] = 2;
        } else {
            $this->newData[$field] = 0;
        }

        return $this->newData[$field];
    }


    /**
     * Undocumented function
     * 审核通过时间
     * @param [type] $field
     * @param [type] $data
     * @return void
     */
    public function setAuthTime($value, $data = []): int
    {
        $field = 'auth_time';
        if (!empty($value)) {
            if (strtotime($value)) {
                $this->newData[$field] = strtotime($value);
            } else {
                $this->newData[$field] = $value;
            }
        } else if (!empty($data['auth']) && $data['auth'] == 1) {
            $this->newData[$field] = time();
        } else {
            $this->newData[$field] = 0;
        }

        return $this->newData[$field];
    }


    /**
     * Undocumented function
     * 所属管理员平台ID
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setUid($value, $data = []): int
    {
        $field = 'uid';
        if (!empty($value)) {
            $this->newData[$field] = intval($value);
        } else if (!empty($data['user']['id'])) {
            $this->newData[$field] = $data['user']['id'];
        } else {
            $this->newData[$field] = 0;
        }

        return $this->newData[$field];
    }



    /**
     * Undocumented function
     * 扩展属性
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setAttrs($value, $data = []): string
    {
        $field = 'attrs';
        if (!empty($value) && is_array($value)) {
            $this->newData[$field] = json_encode($value);
        } else {
            $this->newData[$field] = '';
        }

        return $this->newData[$field];
    }


    /**
     * Undocumented function
     * 内容
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setContent($value, $data = []): string
    {
        $field = 'content';
        if (!empty($value)) {
            $this->newData[$field] = trim($value);
        } else {
            $this->newData[$field] = '';
        }
        return $this->newData[$field];
    }


    /**
     * Undocumented function
     * SEO关键词
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setSeoKeyword($value, $data = []): string
    {
        $field = 'seo_keyword';
        if (!empty($value)) {
            $this->newData[$field] = trim($value);
        } else {
            $this->newData[$field] = '';
        }
        return $this->newData[$field];
    }



    /**
     * Undocumented function
     * SEO描述
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setSeoRemark($value, $data = []): string
    {
        $field = 'seo_remark';
        if (!empty($value)) {
            $this->newData[$field] = trim($value);
        } else {
            $this->newData[$field] = '';
        }
        return $this->newData[$field];
    }



    /**
     * Undocumented function
     * 上级ID [用于分类，用户的需要重写]
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setPid($value, $data = []): int
    {
        $field = 'pid';
        if (!empty($value)) {
            if (is_array($value)) {
                $this->newData[$field] = array_pop($value);
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
     * 上级路径 [用于分类，用户的需要重写]
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setPidPath($value, $data = []): string
    {
        $field = 'pid_path';
        if (!empty($data['pid']) && is_array($data['pid'])) {
            $this->newData[$field] = implode(',', $data['pid']);
        } else if (!empty($value) && is_array($value)) {
            $this->newData[$field] = implode(',', $value);
        } else {
            $this->newData[$field] = '';
        }
        return $this->newData[$field];
    }




    /**
     * Undocumented function
     * 上级ID [用于分类，用户的需要重写]
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setCid($value, $data = []): int
    {
        $field = 'cid';
        if (!empty($value)) {
            if (is_array($value)) {
                $this->newData[$field] = array_pop($value);
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
     * 上级路径 [用于分类，用户的需要重写]
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setCidPath($value, $data = []): string
    {
        $field = 'cid_path';
        if (!empty($data['cid']) && is_array($data['cid'])) {
            $this->newData[$field] = implode(',', $data['cid']);
        } else if (!empty($value) && is_array($value)) {
            $this->newData[$field] = implode(',', $value);
        } else {
            $this->newData[$field] = '';
        }
        return $this->newData[$field];
    }



    /**
     * Undocumented function
     * 用户名
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setUsername($value, $data = []): string
    {
        $field = 'username';
        if (!empty($value)) {
            $this->newData[$field] = trim($value);
        } else {
            $this->newData[$field] = '';
        }
        return $this->newData[$field];
    }




    /**
     * Undocumented function
     * 密码
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setPassword($value, $data = []): array
    {
        $field = 'password';
        if (!empty($value)) {
            $this->newData['salt'] = rand_string(6);
            $this->newData[$field] = $this->model::setPassword($value, $this->newData['salt']);;
        } else {
            $this->newData['salt'] = '';
            $this->newData[$field] = '';
        }
        return $this->newData;
    }



    /**
     * Undocumented function
     * 二级密码
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setPayPwd($value, $data = []): array
    {
        $field = 'pay_pwd';
        if (!empty($value)) {
            $this->newData['pay_salt'] = rand_string(6);
            $this->newData[$field] = $this->model::setPassword($value, $this->newData['pay_salt']);;
        } else {
            $this->newData['pay_salt'] = '';
            $this->newData[$field] = '';
        }
        return $this->newData;
    }


    /**
     * Undocumented function
     * 手机号码
     * @param [type] $field
     * @param [type] $data
     * @return void
     */
    public function setPhone($value, $data = []): int
    {
        $field = 'phone';
        if (!empty($value)) {
            $this->newData[$field] = intval($value);
        } else {
            $this->newData[$field] = 0;
        }

        return $this->newData[$field];
    }



    /**
     * Undocumented function
     * 备注说明
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setRemark($value, $data = []): string
    {
        $field = 'remark';
        if (!empty($value)) {
            $this->newData[$field] = trim($value);
        } else {
            $this->newData[$field] = '';
        }
        return $this->newData[$field];
    }


    /**
     * Undocumented function
     * 最后登录时间
     * @param [type] $field
     * @param [type] $data
     * @return void
     */
    public function setLastLoginTime($value, $data = []): int
    {
        $field = 'last_login_time';
        if (!empty($value)) {
            $this->newData[$field] = intval($value);
        } else {
            $this->newData[$field] = 0;
        }

        return $this->newData[$field];
    }


    /**
     * Undocumented function
     * 最后登录IP
     * @param [type] $field
     * @param [type] $data
     * @return void
     */
    public function setLastLoginIp($value, $data = []): int
    {
        $field = 'last_login_ip';
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
     * 最后登录位置
     * @param [type] $field
     * @param [type] $data
     * @return void
     */
    public function setLastLocation($value, $data = []): string
    {
        $field = 'last_location';
        if (!empty($value)) {
            $this->newData[$field] = trim($value);
        } else {
            $this->newData[$field] = '';
        }

        return $this->newData[$field];
    }


    /**
     * Undocumented function
     * 昵称
     * @param [type] $field
     * @param [type] $data
     * @return void
     */
    public function setNickname($value, $data = []): string
    {
        $field = 'nickname';
        if (!empty($value)) {
            $this->newData[$field] = trim($value);
        } else {
            $this->newData[$field] = '';
        }

        return $this->newData[$field];
    }



    /**
     * Undocumented function
     * 0-保密 1-男 2-女
     * @param [type] $field
     * @param [type] $data
     * @return void
     */
    public function setSex($value, $data = []): int
    {
        $field = 'sex';
        if (!empty($value)) {
            $this->newData[$field] = intval($value);
        } else {
            $this->newData[$field] = 0;
        }

        return $this->newData[$field];
    }



    /**
     * Undocumented function
     * 头像
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
     * 生日
     * @param [type] $field
     * @param [type] $data
     * @return void
     */
    public function setBirthday($value, $data = []): int
    {
        $field = 'birthday';
        if (!empty($value)) {
            if (strtotime($value)) {
                $this->newData[$field] = strtotime($value);
            } else {
                $this->newData[$field] = $value;
            }
        } else {
            $this->newData[$field] = 0;
        }

        return $this->newData[$field];
    }


    /**
     * Undocumented function
     * 微信OPENID
     * @param [type] $field
     * @param [type] $data
     * @return void
     */
    public function setOpenid($value, $data = []): string
    {
        $field = 'openid';
        if (!empty($value)) {
            $this->newData[$field] = trim($value);
        } else {
            $this->newData[$field] = '';
        }

        return $this->newData[$field];
    }


    /**
     * Undocumented function
     * 等级编号
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setLevel($value, $data = []): int
    {
        $field = 'level';
        if (!empty($value)) {
            $this->newData[$field] = intval($value);
        } else {
            $this->newData[$field] = 0;
        }
        return $this->newData[$field];
    }


    /**
     * Undocumented function
     * 等级名称
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setLevelTitle($value, $data = []): string
    {
        $field = 'level_title';
        if (!empty($value)) {
            $this->newData[$field] = trim($value);
        } else {
            $this->newData[$field] = '';
        }
        return $this->newData[$field];
    }


    /**
     * Undocumented function
     * 会员到期时间
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setExpireTime($value, $data = []): int
    {
        $field = 'expire_time';
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
     * 钱包余额
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setBalance($value, $data = []): float
    {
        $field = 'balance';
        if (!empty($value)) {
            $this->newData[$field] = $value;
        } else {
            $this->newData[$field] = 0.00;
        }
        return $this->newData[$field];
    }


    /**
     * Undocumented function
     * 冻结金额
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setFrozenBalance($value, $data = []): float
    {
        $field = 'frozen_balance';
        if (!empty($value)) {
            $this->newData[$field] = $value;
        } else {
            $this->newData[$field] = 0.00;
        }
        return $this->newData[$field];
    }


    /**
     * Undocumented function
     * 操作人
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setHandleId($field, $value, $data = []): int
    {
        $field = 'handle_id';
        if (!empty($value)) {
            $this->newData[$field] = intval($value);
        } else if (!empty($data['user']['id'])) {
            $this->newData[$field] = $data['user']['id'];
        } else {
            $this->newData[$field] = 0;
        }
        return $this->newData[$field];
    }



    /**
     * Undocumented function
     * 名称
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setTitle($value, $data = []): string
    {
        $field = 'title';
        if (!empty($value)) {
            $this->newData[$field] = trim($value);
        } else {
            $this->newData[$field] = '';
        }
        return $this->newData[$field];
    }

    /**
     * Undocumented function
     * 别名
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setAlias($value, $data = []): string
    {
        $field = 'alias';
        if (!empty($value)) {
            $this->newData[$field] = trim($value);
        } else {
            $this->newData[$field] = '';
        }
        return $this->newData[$field];
    }



    /**
     * Undocumented function
     * 链接地址
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setName($value, $data = []): string
    {
        $field = 'name';
        if (!empty($value)) {
            $this->newData[$field] = trim($value);
        } else {
            $this->newData[$field] = '';
        }
        return $this->newData[$field];
    }



    /**
     * Undocumented function
     * 类型
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setType($value, $data = []): int
    {
        $field = 'type';
        if (!empty($value)) {
            $this->newData[$field] = intval($value);
        } else {
            $this->newData[$field] = 1;
        }
        return $this->newData[$field];
    }


    /**
     * Undocumented function
     * 是否显示菜单
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setIsmenu($value, $data = []): int
    {
        $field = 'ismenu';

        if (!empty($value)) {
            $this->newData[$field] = 1;
        } else {
            $this->newData[$field] = 0;
        }
        return $this->newData[$field];
    }


    /**
     * Undocumented function
     * 图标
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setIcon($value, $data = []): string
    {
        $field = 'icon';
        if (!empty($value)) {
            $this->newData[$field] = trim($value);
        } else {
            $this->newData[$field] = '';
        }
        return $this->newData[$field];
    }




    /**
     * Undocumented function
     * 缩略图ID
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setThumb($value, $data = []): int
    {
        $field = 'thumb';

        if (!empty($value)) {
            $this->newData[$field] = intval($value);
        } else {
            $this->newData[$field] = 0;
        }
        return $this->newData[$field];
    }



    /**
     * Undocumented function
     * 缩略图ID
     * @param [type] $field
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function setImages($value, $data = []): string
    {
        $field = 'images';

        if (!empty($value)) {
            if (is_array($value)) {
                $this->newData[$field] = implode(',', $value);
            } else {
                $this->newData[$field] = intval($value);
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
    public function setIsPay($value, $data = []): int
    {
        $field = 'is_pay';

        if (!empty($value)) {
            $this->newData[$field] = intval($value);
        } else {
            $this->newData[$field] = 0;
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
    public function setMoney($value, $data = []): float
    {
        $field = 'money';

        if (!empty($value)) {
            $this->newData[$field] = floatVal($value);
        } else {
            $this->newData[$field] = 0.00;
        }
        return $this->newData[$field];
    }





    public function setAuthor($value, $data = []): string
    {
        $field = 'author';
        if (!empty($value)) {
            $this->newData[$field] = trim($value);
        } else {
            $this->newData[$field] = '';
        }
        return $this->newData[$field];
    }


    public function setNote($value, $data = []): string
    {
        $field = 'note';
        if (!empty($value)) {
            $this->newData[$field] = trim($value);
        } else {
            $this->newData[$field] = '';
        }
        return $this->newData[$field];
    }
}
