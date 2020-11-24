<?php

namespace App\Http\Admin\Model;


use EasySwoole\ORM\AbstractModel;


class ConfigModel extends AbstractModel
{

    /**
     * Undocumented variable
     * 用户类型
     * @var array
     */
    public static $userType = [
        ['id'=>'user','title'=>'普通用户'],
        ['id'=>'developer','title'=>'开发者'],
    ];


    /**
     * Undocumented variable
     * 用户类型
     * @var array
     */
    public static $platformTypeList = [
        ['id'=>'web','title'=>'网站端'],
        ['id'=>'wap','title'=>'H5'],
        ['id'=>'android','title'=>'Android'],
        ['id'=>'ios','title'=>'IOS'],
        ['id'=>'wechat','title'=>'微信公众号'],
        ['id'=>'wechatapp','title'=>'微信小程序'],
        ['id'=>'qqapp','title'=>'QQ小程序'],
        ['id'=>'aliapp','title'=>'支付宝小程序'],
        ['id'=>'toutiaoapp','title'=>'头条小程序'],
        ['id'=>'baiduapp','title'=>'百度小程序'],
        ['id'=>'quickapp','title'=>'快应用'],
        ['id'=>'zijieapp','title'=>'字节跳动小程序'],
        ['id'=>'360WebView','title'=>'360小程序'],
    ];
    
}