<?php


namespace App\Http\Api\Controller;

use Extend\Base\BaseController;
use App\Http\Addons\User\Model\UserModel;

abstract class ExteriorApi extends BaseController
{
    /**
     * Undocumented variable
     *
     * @var [type]
     */
    protected $model = null;

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    protected $validateName = '';


    /**
     * appkey
     * @var string
     */
    protected  $appkey       = 'C79658C35BBEAD2D';

    /**
     * appsecret
     * @var string
     */
    protected  $appsecret    = 'DDCCE4F6C79658C35BBEAD2DB4B465FF';


    protected $userModel = UserModel::class;

    //如果接口需要验证路由权限，请自己写。可以参考admin下的
    protected $authModel = Auth::class;


    protected $isIp = false;
}
