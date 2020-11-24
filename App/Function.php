<?php


/**
 * 记录日志
 * @param $msg
 * @param string $type
 * @param string $fileName
 * @return bool
 */
function writeLog($msg, string $type = 'INFO', string $fileName = '')
{

    $config = [
        'path' => EASYSWOOLE_ROOT . '/Log/'
    ];

    return \Extend\Driver\FilesDriver::getInstance($config)->set($msg, $type, $fileName);
}



/**
 * 队列
 * @return \App\Common\Queue
 */
function queue(): \App\Common\Queue
{
    return \App\Common\Queue::getInstance();
}


/**
 * socket帮助类
 * @return \App\Common\SocketHelp
 */
function socketHelp(): \App\Common\SocketHelp
{
    return \App\Common\SocketHelp::getInstance();
}


/**
 * swoole服务
 * @return \Swoole\Http\Server|\Swoole\Server|\Swoole\Server\Port|\Swoole\WebSocket\Server|null
 */
function swooleServer()
{
    return socketHelp()->swooleServer();
}

/**
 * 推送uid
 * @param array|int $uids
 * @param array|string $data
 * @param null $server
 * @return bool
 */
function pushUid($uids, $data, $server = null)
{
    return socketHelp()->pushUid($uids, $data, $server);
}

/**
 * 推送
 * @param array|int $fds
 * @param array|string $data
 * @param null $server
 * @return bool
 */
function push($fds, $data, $server = null)
{
    return socketHelp()->push($fds, $data, $server);
}



/**
 * 调用easyswoole redis
 * @param string $name
 * @param null $timeout
 * @return \EasySwoole\Redis\Redis|null
 */
function eRedis(string $name = 'redis', $timeout = null): ?\EasySwoole\Redis\Redis
{
    return \EasySwoole\RedisPool\Redis::defer($name, $timeout);
}

/**
 * @param $name
 * @param $value
 * @return bool
 */
function hMSetRedis($name, $value)
{
    $redis = eRedis();
    return $redis->hMSet($name, $value);
}

/**
 * @param $name
 * @return bool|string
 */
function hGetRedis($name)
{
    $redis = eRedis();
    return $redis->hGetAll($name);
}

/**
 * 返回工具
 * @return \App\Common\ReturnUtil
 */
function returnUtil(): \App\Common\ReturnUtil
{
    return \App\Common\ReturnUtil::getInstance();
}

/**
 * 判断字符串是否为 Json 格式
 * @param  string     $data  Json 字符串
 * @param  bool       $assoc 是否返回关联数组。默认返回对象
 * @return bool|array 成功返回转换后的对象或数组，失败返回 false
 */
function isJson($data = '', $assoc = false)
{
    $data = json_decode($data, $assoc);
    if ($data && (is_object($data)) || (is_array($data) && !empty($data))) {
        return $data;
    }
    return false;
}

/**
 * 获取钩子数据
 * @param string $layer 层级
 * @param string $method 方法
 * @param array $vars
 * @return array|null
 */
function hook(string $layer, string $method, array $vars = [])
{
    if (empty($layer)) {
        return null;
    }
    $apiPath = EASYSWOOLE_ROOT . '/App/Hook/' . ucfirst($layer) . '/*.php';
    $apiList = glob($apiPath);
    if (empty($apiList)) {
        return [];
    }
    $appPathStr = strlen(EASYSWOOLE_ROOT);
    $method = lcfirst($method);

    $data = [];
    foreach ($apiList as $value) {
        $path = substr($value, $appPathStr, -4);
        $appName = explode('/', $path);
        $appName = $appName[count($appName) - 1];
        $class = str_replace('/', '\\', $path);
        if (!class_exists($class)) {
            return null;
        }
        $class = new $class();
        if (method_exists($class, $method)) {
            $data[$appName] = call_user_func_array([$class, $method], $vars);
        }
    }
    return $data;
}

/**
 * 精准指向钩子
 * @param string $layer 层级
 * @param string $module 模块(类名)
 * @param string $method 方法
 * @param array $vars
 * @return mixed|null
 */
function hookClear(string $layer, string $module, string $method, array $vars = [])
{
    if (empty($layer)) {
        return null;
    }

    //获取token
    $class = '\\App\\Hook\\' . ucfirst($layer) . '\\' . ucfirst($module);
    if (!class_exists($class)) {
        return null;
    }

    $class = new $class();

    if (!method_exists($class, $method)) {
        return null;
    }

    return call_user_func_array([$class, $method], $vars);
}


/**
 * Undocumented function
 * 唯一识别码
 * @return void
 */
function getUuid()
{
    mt_srand((float) microtime() * 10000);
    $charid = strtoupper(md5(uniqid(rand(), true))); //根据当前时间（微秒计）生成唯一id.
    $hyphen = chr(45);
    $uuid = '' . substr($charid, 0, 8) . $hyphen . substr($charid, 8, 4) . $hyphen . substr($charid, 12, 4) . $hyphen . substr($charid, 16, 4) . $hyphen . substr($charid, 20, 12);

    return $uuid;
}

/**
 * 帮助类
 * @return \App\Common\Help
 */
function help(): \App\Common\Help
{
    return \App\Common\Help::getInstance();
}

/**
 * 域名地址
 * @return string
 */
function domain(): string
{
    return \EasySwoole\EasySwoole\Config::getInstance()->getConf('USE')['domain'];
}

/**
 * 模块调用
 * @param $class
 * @return callable|string|null $class
 * @throws Throwable
 */
function target($class)
{
    return help()->target($class);
}

/**
 * 字符串截取指定长度 超过省略号代替
 * @param $str
 * @param $len
 * @param string $endStr
 * @return string
 */
function cutString($str, $len, $endStr = '...')
{
    // 检查长度
    if (mb_strwidth($str, 'UTF-8') <= $len) {
        return $str;
    }

    // 截取
    $i = 0;
    $tlen = 0;
    $tstr = '';

    while ($tlen < $len) {
        $chr = mb_substr($str, $i, 1, 'UTF-8');
        $chrLen = ord($chr) > 127 ? 2 : 1;

        if ($tlen + $chrLen > $len) break;

        $tstr .= $chr;
        $tlen += $chrLen;
        $i++;
    }

    if ($tstr != $str) {
        $tstr .= $endStr;
    }

    return $tstr;
}

/**
 * 将下划线命名转换为驼峰式命名
 * @param string $str
 * @param bool $ucFirst
 * @return mixed|string
 */
function convertUnderline(string $str, bool $ucFirst = true)
{
    $str = ucwords(str_replace('_', ' ', $str));
    $str = str_replace(' ', '', lcfirst($str));
    return $ucFirst ? ucfirst($str) : $str;
}

/**
 * 将下划线命名转换为驼峰式命名
 * @param array $data
 * @param bool $ucFirst
 * @return array
 */
function arrayConvertUnderline(array $data, bool $ucFirst = true)
{

    $arr = [];

    foreach ($data as $k => $v) {
        $key = convertUnderline($k, $ucFirst);
        $arr[$key] = $v;
    }

    return $arr;
}

/**
 * 将list下划线命名转换为驼峰式命名
 * @param array $list
 * @param bool $ucFirst
 * @return array
 */
function listConvertUnderline(array $list, bool $ucFirst = true)
{

    foreach ($list as $k => $v) {
        $val = arrayConvertUnderline($v, $ucFirst);
        $list[$k] = $val;
    }

    return $list;
}

/**
 * 对象转list
 * @param $objList
 * @param array $keyList
 * @return array
 */
function objectToList($objList, $keyList = ['key', 'text'])
{
    $list = [];
    if (!$objList) {
        return [];
    }
    foreach ($objList as $k => $v) {
        $list[] = [
            $keyList[0] => $k,
            $keyList[1] => $v,
        ];
    }
    return $list;
}



/*
*  +----------------------------------------------------------*
* 产生随机字串，可用来自动生成密码 默认长度6位 字母和数字混合
*  +----------------------------------------------------------
* @param string $len 长度
* @param string $type 字串类型
* 0 字母 1 数字 其它 混合
* @param string $addChars 额外字符
* +----------------------------------------------------------
* @return string
* +----------------------------------------------------------
*/
function rand_string(int $len = 6, int $type = null, string $addChars = ''): string
{
    $str = '';
    switch ($type) {
        case 0:
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' . $addChars;
            break;
        case 1:
            $chars = str_repeat('0123456789', 3);
            break;
        case 2:
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . $addChars;
            break;
        case 3:
            $chars = 'abcdefghijklmnopqrstuvwxyz' . $addChars;
            break;
        case 4:
            $chars = "们以我到他会作时要动国产的一是工就年阶义发成部民可出能方进在了不和有大这主中人上为来分生对于学下级地个用同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所二起政三好十战无农使性前等反体合斗路图把结第里正新开论之物从当两些还天资事队批点育重其思与间内去因件日利相由压员气业代全组数果期导平各基或月毛然如应形想制心样干都向变关问比展那它最及外没看治提五解系林者米群头意只明四道马认次文通但条较克又公孔领军流入接席位情运器并飞原油放立题质指建区验活众很教决特此常石强极土少已根共直团统式转别造切九你取西持总料连任志观调七么山程百报更见必真保热委手改管处己将修支识病象几先老光专什六型具示复安带每东增则完风回南广劳轮科北打积车计给节做务被整联步类集号列温装即毫知轴研单色坚据速防史拉世设达尔场织历花受求传口断况采精金界品判参层止边清至万确究书术状厂须离再目海交权且儿青才证低越际八试规斯近注办布门铁需走议县兵固除般引齿千胜细影济白格效置推空配刀叶率述今选养德话查差半敌始片施响收华觉备名红续均药标记难存测士身紧液派准斤角降维板许破述技消底床田势端感往神便贺村构照容非搞亚磨族火段算适讲按值美态黄易彪服早班麦削信排台声该击素张密害侯草何树肥继右属市严径螺检左页抗苏显苦英快称坏移约巴材省黑武培著河帝仅针怎植京助升王眼她抓含苗副杂普谈围食射源例致酸旧却充足短划剂宣环落首尺波承粉践府鱼随考刻靠够满夫失包住促枝局菌杆周护岩师举曲春元超负砂封换太模贫减阳扬江析亩木言球朝医校古呢稻宋听唯输滑站另卫字鼓刚写刘微略范供阿块某功套友限项余倒卷创律雨让骨远帮初皮播优占死毒圈伟季训控激找叫云互跟裂粮粒母练塞钢顶策双留误础吸阻故寸盾晚丝女散焊功株亲院冷彻弹错散商视艺灭版烈零室轻血倍缺厘泵察绝富城冲喷壤简否柱李望盘磁雄似困巩益洲脱投送奴侧润盖挥距触星松送获兴独官混纪依未突架宽冬章湿偏纹吃执阀矿寨责熟稳夺硬价努翻奇甲预职评读背协损棉侵灰虽矛厚罗泥辟告卵箱掌氧恩爱停曾溶营终纲孟钱待尽俄缩沙退陈讨奋械载胞幼哪剥迫旋征槽倒握担仍呀鲜吧卡粗介钻逐弱脚怕盐末阴丰雾冠丙街莱贝辐肠付吉渗瑞惊顿挤秒悬姆烂森糖圣凹陶词迟蚕亿矩康遵牧遭幅园腔订香肉弟屋敏恢忘编印蜂急拿扩伤飞露核缘游振操央伍域甚迅辉异序免纸夜乡久隶缸夹念兰映沟乙吗儒杀汽磷艰晶插埃燃欢铁补咱芽永瓦倾阵碳演威附牙芽永瓦斜灌欧献顺猪洋腐请透司危括脉宜笑若尾束壮暴企菜穗楚汉愈绿拖牛份染既秋遍锻玉夏疗尖殖井费州访吹荣铜沿替滚客召旱悟刺脑措贯藏敢令隙炉壳硫煤迎铸粘探临薄旬善福纵择礼愿伏残雷延烟句纯渐耕跑泽慢栽鲁赤繁境潮横掉锥希池败船假亮谓托伙哲怀割摆贡呈劲财仪沉炼麻罪祖息车穿货销齐鼠抽画饲龙库守筑房歌寒喜哥洗蚀废纳腹乎录镜妇恶脂庄擦险赞钟摇典柄辩竹谷卖乱虚桥奥伯赶垂途额壁网截野遗静谋弄挂课镇妄盛耐援扎虑键归符庆聚绕摩忙舞遇索顾胶羊湖钉仁音迹碎伸灯避泛亡答勇频皇柳哈揭甘诺概宪浓岛袭谁洪谢炮浇斑讯懂灵蛋闭孩释乳巨徒私银伊景坦累匀霉杜乐勒隔弯绩招绍胡呼痛峰零柴簧午跳居尚丁秦稍追梁折耗碱殊岗挖氏刃剧堆赫荷胸衡勤膜篇登驻案刊秧缓凸役剪川雪链渔啦脸户洛孢勃盟买杨宗焦赛旗滤硅炭股坐蒸凝竟陷枪黎救冒暗洞犯筒您宋弧爆谬涂味津臂障褐陆啊健尊豆拔莫抵桑坡缝警挑污冰柬嘴啥饭塑寄赵喊垫丹渡耳刨虎笔稀昆浪萨茶滴浅拥穴覆伦娘吨浸袖珠雌妈紫戏塔锤震岁貌洁剖牢锋疑霸闪埔猛诉刷狠忽灾闹乔唐漏闻沈熔氯荒茎男凡抢像浆旁玻亦忠唱蒙予纷捕锁尤乘乌智淡允叛畜俘摸锈扫毕璃宝芯爷鉴秘净蒋钙肩腾枯抛轨堂拌爸循诱祝励肯酒绳穷塘燥泡袋朗喂铝软渠颗惯贸粪综墙趋彼届墨碍启逆卸航衣孙龄岭骗休借" . $addChars;
            break;
        default:
            // 默认去掉了容易混淆的字符oOLl和数字01，要添加请使用addChars参数
            $chars = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789' . $addChars;
            break;
    }
    if ($len > 10) {
        //位数过长重复字符串一定次数
        $chars = $type == 1 ? str_repeat($chars, $len) : str_repeat($chars, 5);
    }
    if ($type != 4) {
        $chars = str_shuffle($chars);
        $str = substr($chars, 0, $len);
    } else {
        // 中文随机字
        for ($i = 0; $i < $len; $i++) {
            $str .=  msubstr($chars, floor(mt_rand(0, mb_strlen($chars, 'utf-8') - 1)), 1);
        }
    }
    return $str;
}


/**
 * Undocumented function
 * 中文截取
 * @param [type] $str
 * @param integer $start
 * @param [type] $length
 * @param string $charset
 * @param boolean $suffix
 * @return void
 */
function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = false)
{

    if (function_exists("mb_substr")) {

        if ($suffix)

            return mb_substr($str, $start, $length, $charset) . "...";

        else

            return mb_substr($str, $start, $length, $charset);
    } elseif (function_exists('iconv_substr')) {

        if ($suffix)

            return iconv_substr($str, $start, $length, $charset) . "...";

        else

            return iconv_substr($str, $start, $length, $charset);
    }

    $re['utf-8'] = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef][x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";

    $re['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";

    $re['gbk'] = "/[x01-x7f]|[x81-xfe][x40-xfe]/";

    $re['big5'] = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";

    preg_match_all($re[$charset], $str, $match);

    $slice = join("", array_slice($match[0], $start, $length));

    if ($suffix) return $slice . "…";

    return $slice;
}



function getSendCode($phone, $ts = 600)
{
    $arr['code'] = rand_string(6, 1);
    $arr['times'] = (time() + $ts);
    $arr['ts'] = ($ts / 60);
    $arr['phone'] = $phone;
    return $arr;
}


function makeTree($data)
{

    return \App\Common\PHPTree::makeTree($data);
}


function voidToArray($list)
{
    return json_decode(json_encode($list), true);
}



function getPagination($total, $current_page = 1, $per_page = 20, $page_list = 5)
{
    $pages = array();
    //获取当前页码左右两侧的页码数
    $ceil_mean = ceil($page_list / 2);
    //获取最大页码
    $max = ceil($total / $per_page);
    //计算左侧页码
    //   $left = max($current_page - $ceil_mean, 1);
    //计算右侧页码
    //  $right = $left + $page_list - 1;
    //重新计算right,防止right超过最大页码数
    //    $right = min($max, $right);
    //    //重新计算left,确保显示的页数为page_list指定的页数
    //    $left = max($right - $page_list + 1, 1);

    //    for ($i = $left; $i <= $right; $i++) {
    //        $pages['page_list'][] = $i;
    //    }
    $pages['sizes'] = [];
    $arr = [10, 20, 50, 100, 200, 500, 1000];
    for ($i = 0; $i < count($arr); $i++) {
        if ($total > $arr[$i]) {
            $pages['sizes'][] = $arr[$i];
        } else {
            $pages['sizes'][] = $total;
            break;
        }
    }

    $pages['totalCount'] = $total;
    $pages['max_page'] = $max;
    $pages['current_page'] = min($current_page, $max);
    if ($pages['current_page'] >= $pages['max_page']) {
        $pages['next_page'] = 0;
    } else {
        $pages['next_page'] = $pages['current_page'] + 1;
    }

    return $pages;
}



/**
 * Undocumented function
 * 计算中奖概率
 * @param [type] $proArr
 * @return void
 */
function getRand(array $proArr)
{
    $result = '';
    //概率数组的总概率精度   
    $proSum = array_sum($proArr);

    if ($proSum === 0) {
        $result = array_rand($proArr);
    } else {
        //概率数组循环   
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);  //返回随机整数 

            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
    }

    unset($proArr);
    return $result;
}


/**
 * Undocumented function
 * 计算两个时间相差天数
 * @param integer $day1
 * @param integer $day2
 * @return void
 */
function diffDay(int $day1, int $day2)
{
    return abs(strtotime(date('Y-m-d', $day1)) - strtotime(date('Y-m-d', $day2))) / 86400;
}


function findNum($str = '')
{
    $str = trim($str);
    if (empty($str)) {
        return '';
    }
    $result = '';
    for ($i = 0; $i < strlen($str); $i++) {
        if (is_numeric($str[$i])) {
            $result .= $str[$i];
        }
    }
    return $result;
}


/**
 * Undocumented function
 * 图片合成
 * @param [type] $erCodeImg 二维码图片地址
 * @param [type] $bgImg  背景图片地址
 * @return void
 */
function synthesisPicture($erCodeImg, $bgImg, $uid, $aid)
{
    if (empty($erCodeImg) || empty($bgImg)) {
        return false;
    }
    $QR = imagecreatefromstring(file_get_contents($erCodeImg));
    $bg = imagecreatefromstring(file_get_contents($bgImg));

    $logo_width = imagesx($QR); //logo图片宽度   
    $logo_height = imagesy($QR); //logo图片高度   

    imagecopyresampled($bg, $QR, 304, 650, 0, 0, 280, 280, $logo_width, $logo_height);
    $name  = $uid . '.png';
    $url = EASYSWOOLE_ROOT . '/Public/Uploads/Tmp/' . $name;
    imagepng($bg, $url);


    $upload = new \App\Common\QiNiuUpload();
    $result = $upload->uplodeByBase64('data:image/png;base64,' . base64_encode(file_get_contents($url)), $name, $uid, $aid, false, 0);

    if (!$result) {
        return [
            'msg' => 1,
            'code' => $upload->getCode(),
            'data' => $upload->getData(),
        ];
    }

    return [
        'msg' => $upload->getError(),
        'code' => $upload->getCode(),
        'data' => $upload->getData(),
    ];
}


/**
 * Undocumented function
 * 计算距离
 * @param [type] $lat1 商家经度
 * @param [type] $lng1 商家纬度
 * @param [type] $lat2 用户纬度
 * @param [type] $lng2 用户经度
 * @return void
 */
function getDistance($lat1, $lng1, $lat2, $lng2)
{
    $earthRadius = 6367000;
    $lat1 = $lat1 * pi() / 180;
    $lng1 = $lng1 * pi() / 180;
    $lat2 = $lat2 * pi() / 180;
    $lng2 = $lng2 * pi() / 180;
    $calcLongitude = $lng2 - $lng1;
    $calcLatitude = $lat2 - $lat1;
    $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
    $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
    $calculatedDistance = $earthRadius * $stepTwo;
    return round($calculatedDistance);
}



/**
 * 判断文件或文件夹是否可写
 * @param    string $file 文件或目录
 * @return    bool
 */
function is_really_writable($file)
{
    if (DIRECTORY_SEPARATOR === '/') {
        return is_writable($file);
    }
    if (is_dir($file)) {
        $file = rtrim($file, '/') . '/' . md5(mt_rand());
        if (($fp = @fopen($file, 'ab')) === FALSE) {
            return FALSE;
        }
        fclose($fp);
        @chmod($file, 0777);
        @unlink($file);
        return TRUE;
    } elseif (!is_file($file) or ($fp = @fopen($file, 'ab')) === FALSE) {
        return FALSE;
    }
    fclose($fp);
    return TRUE;
}


/**
 * 删除文件夹
 * @param string $dirname 目录
 * @param bool $withself 是否删除自身
 * @return boolean
 */
function rmdirs($dirname, $withself = true)
{
    if (!is_dir($dirname))
        return false;
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dirname, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($files as $fileinfo) {
        $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
        $todo($fileinfo->getRealPath());
    }
    if ($withself) {
        @rmdir($dirname);
    }
    return true;
}


/**
 * 复制文件夹
 * @param string $source 源文件夹
 * @param string $dest 目标文件夹
 */
function copydirs($source, $dest)
{

    if (!is_dir($dest)) {
        mkdir($dest, 0755, true);
    }
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($iterator as $k => $item) {


        if ($item->isDir()) {
            $sontDir = $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            if (!is_dir($sontDir)) {
                mkdir($sontDir, 0755, true);
            }
        } else {
            copy($item, $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
        }
    }
}


/**
 * 字符串命名风格转换
 * type 0 将 Java 风格转换为 C 的风格 1 将 C 风格转换为 Java 的风格
 * @access public
 * @param  string  $name    字符串
 * @param  integer $type    转换类型
 * @param  bool    $ucfirst 首字母是否大写（驼峰规则）
 * @return string
 */
function parseName($name, $type = 0, $ucfirst = true)
{
    if ($type) {
        $name = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
            return strtoupper($match[1]);
        }, $name);

        return $ucfirst ? ucfirst($name) : lcfirst($name);
    }

    return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
}


/**
 * 获取url地址
 * @param string $value
 * @return string
 */
function getThumbUrlAttr(string $value)
{

    if (!empty($value)) {
        $info = \App\Http\Admin\Model\CoverModel::getInfoById($value);
        if (empty($info)) {
            return '';
        }

        $host = !empty($info['host']) ? $info['host'] . '/' : '';

        $path = $host . $info['path'];

        if ($info['is_private'] == 1) {
            $value = (new \App\Common\QiNiuUpload())->returnAuthPath($path);
        } else {
            $value = $path;
        }
    }
    return $value;
}


/**
 * Undocumented function
 * 获得多图片url地址
 * @param string $value
 * @return void
 */
function getImagesUrlAttr(string $value)
{
    if (empty($value)) {
        return [];
    }

    $src = [];

    $map['id'] = [explode(',', $value), 'in'];
    $list =  \App\Http\Admin\Model\CoverModel::getListByCommonMap($map, ['*'], ['id', 'ASC']);
    foreach ($list  as $v) {
        $host = !empty($v['host']) ? $v['host'] . '/' : '';
        $path = $host . $v['path'];
        $src[] = (new \App\Common\QiNiuUpload())->returnAuthPath($path);
    }

    return $src;
}
