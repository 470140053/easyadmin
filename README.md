# easyadmin
easyswoole + vue 写的一个可拖拽表单、Table列表的管理后台


#### 主要特性
EasyAdmin已经具备php最优秀的二开框架的雏形，并提供免费使用。

基于Easyswoole二次开发
基于VUE+Element-UI进行样式模板开发
强大的插件扩展功能，在线安装卸载升级插件
基于Auth验证的权限管理系统
支持无限级父子级权限继承，父级的管理员可任意增删改子级管理员及权限设置
支持单管理员多角色
支持管理子级数据或个人数据
通用的会员模块和API模块
共用同一账号体系的Web端会员中心权限验证和API接口会员权限验证
丰富的插件应用市场
自定义拖拽生成表单、TABLE列表，完全可以减少前端代码编写，自动生成

# 安装教程
1. 安装swoole扩展和redis扩展

2.  配置NGINX
    加入反向代理
    location / {
    proxy_http_version 1.1;
    proxy_set_header Connection "keep-alive";
    proxy_set_header X-Real-IP $remote_addr;
    if (!-f $request_filename) {
        proxy_pass http://127.0.0.1:9508;
    }
}
3.  访问 http://127.0.0.1:xxxx/install.php 进行安装
    或者手动安装

4.  手动安装启动命令 php easyswoole server start -d

了解更多可以参考
    EasySwoole手册 https://www.easyswoole.com/
    Element-UI文档 https://element.eleme.cn/
    VUE文档 https://cn.vuejs.org/

# 使用说明

如果有啥问题可以进QQ群聊 613315644

1. 前端模板地址 ： https://gitee.com/tv1898/easyadmin-vue

2. 演示地址 ： http://test.easyadmin.epai8.com/admin

账号：admin
密码：admin1
