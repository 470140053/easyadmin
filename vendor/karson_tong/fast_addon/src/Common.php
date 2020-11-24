<?php

define('ADDON_PATH', '/App/Http/Addons' . DIRECTORY_SEPARATOR);

/**
 * 获取插件类的类名
 * @param $name 插件名
 * @param string $type 返回命名空间类型
 * @param string $class 当前类名
 * @return string
 */
function get_addon_class($name, $type = 'hook', $class = null)
{
    $name = parseName($name);
    // 处理多级控制器情况
    if (!is_null($class) && strpos($class, '.')) {
        $class = explode('.', $class);

        $class[count($class) - 1] = parseName(end($class), 1);
        $class = implode('\\', $class);
    } else {
        $class = parseName(is_null($class) ? $name : $class, 1);
    }
    switch ($type) {
        case 'controller':
            $namespace = "\\App\\Http\\Addons\\" . $name . "\\Controller\\" . $class;
            break;
        default:
            $namespace = "\\App\\Http\\Addons\\" . $name . "\\" . $class;
    }

    return class_exists($namespace) ? $namespace : '';
}


/**
 * 设置基础配置信息
 * @param string $name 插件名
 * @param array $array
 * @return boolean
 * @throws Exception
 */
function set_addon_info($name, $array)
{
    $file =  EASYSWOOLE_ROOT . ADDON_PATH . $name . DIRECTORY_SEPARATOR . 'info.ini';
    $addon = get_addon_instance($name);
    $array = $addon->setInfo($name, $array);

    $res = array();
    foreach ($array as $key => $val) {
        if (is_array($val)) {
            $res[] = "[$key]";
            foreach ($val as $skey => $sval)
                $res[] = "$skey = " . (is_numeric($sval) ? $sval : $sval);
        } else
            $res[] = "$key = " . (is_numeric($val) ? $val : $val);
    }
    if ($handle = fopen($file, 'w')) {
        fwrite($handle, implode("\n", $res) . "\n");
        fclose($handle);
        //清空当前配置缓存
        $redis = eRedis();
        $redis->hSet('addoninfo', $name, null);
    } else {
        throw new Exception("文件没有写入权限");
    }
    return true;
}


/**
 * 获取插件类的配置值值
 * @param string $name 插件名
 * @return array
 */
function get_addon_config($name)
{
    $addon = get_addon_instance($name);
    if (!$addon) {
        return [];
    }
    return $addon->getConfig($name);
}


/**
 * 获取插件类的配置数组
 * @param string $name 插件名
 * @return array
 */
function get_addon_fullconfig($name)
{
    $addon = get_addon_instance($name);
    if (!$addon) {
        return [];
    }
    return $addon->getFullConfig($name);
}

/**
 * 读取插件的基础信息
 * @param string $name 插件名
 * @return array
 */
function get_addon_info($name)
{
    $addon = get_addon_instance($name);
    if (!$addon) {
        return [];
    }

    return $addon->getInfo($name);
}


/**
 * 获取插件的单例
 * @param $name
 * @return mixed|null
 */
function get_addon_instance($name)
{
    static $_addons = [];
    if (isset($_addons[$name])) {
        return $_addons[$name];
    }
    $class = get_addon_class($name);

    if (class_exists($class)) {
        $_addons[$name] = new $class();
        return $_addons[$name];
    } else {
        return null;
    }
}



/**
 * 获得插件列表
 * @return array
 */
function get_addon_list()
{
    
    $results = scandir(EASYSWOOLE_ROOT . ADDON_PATH);
    
    $list = [];
    foreach ($results as $name) {
        if ($name === '.' or $name === '..')
            continue;
        $addonDir =  EASYSWOOLE_ROOT . ADDON_PATH . $name . DIRECTORY_SEPARATOR;
        if (!is_dir($addonDir))
            continue;

        if (!is_file($addonDir . ucfirst($name) . '.php'))
            continue;

        $info_file = $addonDir . 'info.ini';
        if (!is_file($info_file))
            continue;
        $info = parse($info_file);
        
        // $info['url'] = addon_url($name);
        $list[$name] = $info;
    }
    return $list;
}



/**
 * 写入配置文件
 * @param string $name 插件名
 * @param array $config 配置数据
 * @param boolean $writefile 是否写入配置文件
 */
function set_addon_config($name, $config, $writefile = true)
{
    $addon = get_addon_instance($name);
    $addon->setConfig($name, $config);
    $fullconfig = get_addon_fullconfig($name);
    foreach ($fullconfig as $k => &$v) {
        if (isset($config[$v['name']])) {
            $value = $v['type'] !== 'array' && is_array($config[$v['name']]) ? implode(',', $config[$v['name']]) : $config[$v['name']];
            $v['value'] = $value;
        }
    }
    if ($writefile) {
        // 写入配置文件
        set_addon_fullconfig($name, $fullconfig);
    }
    return true;
}


/**
 * 写入配置文件
 *
 * @param string $name 插件名
 * @param array $array
 * @return boolean
 * @throws Exception
 */
function set_addon_fullconfig($name, $array)
{
    $file = ADDON_PATH . $name . DIRECTORY_SEPARATOR . 'config.php';
    if (!is_really_writable($file)) {
        throw new Exception("文件没有写入权限");
    }
    if ($handle = fopen($file, 'w')) {
        fwrite($handle, "<?php\n\n" . "return " . var_export($array, TRUE) . ";\n");
        fclose($handle);
    } else {
        throw new Exception("文件没有写入权限");
    }
    return true;
}

/**
 * 获得插件自动加载的配置
 * @return array
 */
function get_addon_autoload_config($truncate = false)
{
    // // 读取addons的配置
    // $config = (array)Config::get('addons');
    // if ($truncate) {
    //     // 清空手动配置的钩子
    //     $config['hooks'] = [];
    // }
    // $route = [];
    // // 读取插件目录及钩子列表
    // $base = get_class_methoDIRECTORY_SEPARATOR("\\think\\Addons");
    // $base = array_merge($base, ['install', 'uninstall', 'enable', 'disable']);

    // $url_domain_deploy = Config::get('url_domain_deploy');
    // $addons = get_addon_list();
    // $domain = [];
    // foreach ($addons as $name => $addon) {
    //     if (!$addon['state'])
    //         continue;

    //     // 读取出所有公共方法
    //     $methoDIRECTORY_SEPARATOR = (array)get_class_methoDIRECTORY_SEPARATOR("\\addons\\" . $name . "\\" . ucfirst($name));
    //     // 跟插件基类方法做比对，得到差异结果
    //     $hooks = array_diff($methoDIRECTORY_SEPARATOR, $base);
    //     // 循环将钩子方法写入配置中
    //     foreach ($hooks as $hook) {
    //         $hook = Loader::parseName($hook, 0, false);
    //         if (!isset($config['hooks'][$hook])) {
    //             $config['hooks'][$hook] = [];
    //         }
    //         // 兼容手动配置项
    //         if (is_string($config['hooks'][$hook])) {
    //             $config['hooks'][$hook] = explode(',', $config['hooks'][$hook]);
    //         }
    //         if (!in_array($name, $config['hooks'][$hook])) {
    //             $config['hooks'][$hook][] = $name;
    //         }
    //     }
    //     $conf = get_addon_config($addon['name']);
    //     if ($conf) {
    //         $conf['rewrite'] = isset($conf['rewrite']) && is_array($conf['rewrite']) ? $conf['rewrite'] : [];
    //         $rule = array_map(function ($value) use ($addon) {
    //             return "{$addon['name']}/{$value}";
    //         }, array_flip($conf['rewrite']));
    //         if ($url_domain_deploy && isset($conf['domain']) && $conf['domain']) {
    //             $domain[] = [
    //                 'addon'  => $addon['name'],
    //                 'domain' => $conf['domain'],
    //                 'rule'   => $rule
    //             ];
    //         } else {
    //             $route = array_merge($route, $rule);
    //         }
    //     }
    // }
    // $config['route'] = $route;
    // $config['route'] = array_merge($config['route'], $domain);
    // return $config;
}




function parse($config)
{
    if (is_file($config)) {
        return parse_ini_file($config, true);
    } else {
        return parse_ini_string($config, true);
    }
}
