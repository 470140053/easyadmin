<?php

namespace App\Http\Admin\Controller;

use Extend\Base\BaseAuth;

use App\Http\Admin\Model\AuthRuleModel;
use App\Http\Admin\Model\AuthGroupModel;

/**
 * Undocumented class
 * 项目管理控制器
 */

class Auth extends BaseAuth
{
    // 权限开关 
    protected $authOn = true;

    protected $GroupModel = AuthGroupModel::class;

    protected $RuleModel = AuthRuleModel::class;
}
