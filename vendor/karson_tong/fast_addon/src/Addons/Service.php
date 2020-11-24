<?php

namespace EasySwoole\Addon\Addons;

use EasySwoole\HttpClient\HttpClient;
use EasySwoole\Component\Process\Exception;
use ZipArchive;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Extend\Base\Encrypt;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\ORM\DbManager;


/**
 * 插件服务
 * @package think\addons
 */
class Service 
{

    const RUNTIME_PATH = EASYSWOOLE_ROOT.'/Temp/Addons';

    public static function create() : Service{
        return new self();
    }

    
    /**
     * 安装插件
     *
     * @param   string  $name   插件名称
     * @param   boolean $force  是否覆盖
     * @param   array   $extend 扩展参数
     * @return  boolean
     * @throws  Exception
     * @throws  AddonException
     */
    public static function install($name, $force = false, $extend = [])
    {
      
        //先检查此插件是否存在
        if (!$name || (is_dir( EASYSWOOLE_ROOT . ADDON_PATH . $name) && !$force))
        {
            throw new Exception('Addon already exists');
        }
        
         // 远程下载插件
        $tmpFile = Service::download($name, $extend);

        // 解压插件
        $addonDir = Service::unzip($name);

        // 移除临时文件
        @unlink($tmpFile);
        
        try
        {
            // 检查插件是否完整
            Service::check($name);

            if (!$force)
            {
                Service::noconflict($name);
            }
        }
        catch (Exception $e)
        {
            @rmdirs($addonDir);
            throw new Exception($e->getMessage());
        }


        // 复制文件
        $sourceAssetsDir = self::getSourceAssetsDir($name);
       
        
        if (is_dir($sourceAssetsDir))
        { 
            $destAssetsDir = self::getDestAssetsDir($name);
            copydirs($sourceAssetsDir, $destAssetsDir);
        }
        foreach (self::getCheckDirs() as $k => $dir)
        {
   
            if (is_dir($addonDir .$name .DIRECTORY_SEPARATOR. $dir))
            {
                copydirs($addonDir .$name .DIRECTORY_SEPARATOR. $dir, EASYSWOOLE_ROOT .DIRECTORY_SEPARATOR. $dir);
            }
        }

        try
        {
            // 默认启用该插件
            $info = get_addon_info($name);
            if (!$info['state'])
            {
                $info['state'] = 1;
                set_addon_info($name, $info);
            }

            // 执行安装脚本
            $class = get_addon_class($name);
            if (class_exists($class))
            {
                $addon = new $class();
                $addon->install();
            }
        }
        catch (Exception $e)
        {
            throw new Exception($e->getMessage());
        }

        // 导入
        Service::importsql($name);

        return true;
        
    }



    /**
     * 卸载插件
     *
     * @param   string  $name
     * @param   boolean $force  是否强制卸载
     * @return  boolean
     * @throws  Exception
     */
    public static function uninstall($name, $force = false)
    {
        if (!$name || !is_dir(EASYSWOOLE_ROOT.ADDON_PATH . $name))
        {
            throw new Exception('Addon not exists');
        }

        if (!$force)
        {
            Service::noconflict($name);
        }

        // 移除插件基础资源目录
        $destAssetsDir = self::getDestAssetsDir($name);
        if (is_dir($destAssetsDir))
        {
            rmdirs($destAssetsDir);
        }

        // 移除插件全局资源文件
        if ($force)
        {
            $list = Service::getGlobalFiles($name);
            foreach ($list as $k => $v)
            {
                if(file_exists(EASYSWOOLE_ROOT .DIRECTORY_SEPARATOR. $v)) {
                    @unlink(EASYSWOOLE_ROOT .DIRECTORY_SEPARATOR. $v);
                }
            }
        }

        // 执行卸载脚本
        try
        {
            $class = get_addon_class($name);
            if (class_exists($class))
            {
                $addon = new $class();
                $addon->uninstall();

                rmdirs(EASYSWOOLE_ROOT.ADDON_PATH . $name);

            }
        }
        catch (Exception $e)
        {
            throw new Exception($e->getMessage());
        }

        // 移除插件目录
        rmdirs(ADDON_PATH . $name);

        return true;
    }



    /**
     * 升级插件
     * 
     * @param   string  $name   插件名称
     * @param   array   $extend 扩展参数
     */
    public static function upgrade($name, $extend = [])
    {
        $info = get_addon_info($name);
        if ($info['state'])
        {
            throw new Exception('Please disable addon first');
        }
        $config = get_addon_config($name);
        if ($config)
        {
            //备份配置
        }

        // 远程下载插件
        $tmpFile = Service::download($name, $extend);

        // 解压插件
        $addonDir = Service::unzip($name);

        // 移除临时文件
        @unlink($tmpFile);

        if ($config)
        {
            // 还原配置
            set_addon_config($name, $config);
        }
        
        // 导入
        Service::importsql($name);

        // 执行升级脚本
        try
        {
            $class = get_addon_class($name);
            if (class_exists($class))
            {
                $addon = new $class();

                if (method_exists($class, "upgrade"))
                {
                    $addon->upgrade();
                }
            }
        }
        catch (Exception $e)
        {
            throw new Exception($e->getMessage());
        }
        
        
        return true;
    }

    /**
     * 远程下载插件
     *
     * @param   string  $name   插件名称
     * @param   array   $extend 扩展参数
     * @return  string
     * @throws  AddonException
     * @throws  Exception
     */
    public static function download($name, $data = []) {
        $extend['id'] = $data['id']; 
        $extend['token'] = $data['token']; 
        $extend['app_str'] = $data['addons_str']; 

        $postParam = self::create()->writeJson($extend);

        $config = self::getServerConfig();

        $httpClient = new HttpClient($config['easyadmin_url'].'/api/plug/getDown');
        $httpClient->setHeader('User-Agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.116 Safari/537.36');
        $body = $httpClient->postJSON($postParam)->getBody();

        $arr = json_decode($body,true);
        if(empty($arr)) {
            throw new Exception("无法下载远程文件");
        }

        if($arr['code'] !== 200) {
            throw new Exception($arr['msg'],$arr['code']);
        }

        $result = self::create()->getDecryptData($arr);
        if(empty($result['url'])) {
            throw new Exception('下载地址错误！');
        }

        $addonTmpDir = self::RUNTIME_PATH . DIRECTORY_SEPARATOR;
        if (!is_dir($addonTmpDir))
        {
            @mkdir($addonTmpDir, 0755, true);
        }
        $tmpFile = $addonTmpDir . $name . ".zip";
      
        $ret = file_get_contents($result['url']);

        if(empty($ret)) {
            throw new Exception("文件下载失败");
        }

        if ($write = fopen($tmpFile, 'w'))
        {
            fwrite($write, $ret);
            fclose($write);
            return $tmpFile;
        }
        throw new Exception("没有权限写入临时文件");
    }


    public function getDecryptData($data) : array{
        $config = self::getServerConfig();
        $data['easyadmin_version'] = $config['easyadmin_version'];
        $obj = new Encrypt($config['easyadmin_appid'],$config['easyadmin_appsecret']);
        return json_decode($obj->decrypt($data['data'],$data['sig']),true);
    }

    /**
     * Undocumented function
     * 拼装参数
     * @param [type] $data
     * @return void
     */
    public function writeJson($extend) : string{
        $config = self::getServerConfig();
        $extend['ts'] = time() * 1000;
        $obj = new Encrypt($config['easyadmin_appid'],$config['easyadmin_appsecret']);
        $sig = $obj->getSign($extend);
        
        
        return json_encode([
            'sig' => $sig,
            'data'=>$obj->encrypt($extend, $sig)
        ],JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }


    /**
     * 解压插件
     *
     * @param   string  $name   插件名称
     * @return  string
     * @throws  Exception
     */
    public static function unzip($name)
    {
        $file = self::RUNTIME_PATH . DIRECTORY_SEPARATOR .  $name . '.zip';
        if(!file_exists($file)) {
            throw new Exception('There is no such file');
        }


        $dir =  EASYSWOOLE_ROOT . ADDON_PATH ;
        
        if (class_exists('ZipArchive'))
        {
            $zip = new ZipArchive;
            
            if ($zip->open($file) !== TRUE)
            {
                throw new Exception('Unable to open the zip file');
            }
            if (!$zip->extractTo($dir))
            {
                $zip->close();
                throw new Exception('Unable to extract the file');
            }
            $zip->close();
            return $dir;
        }
        throw new Exception("无法执行解压操作，请确保ZipArchive安装正确");
    }



      /**
     * 检测插件是否完整
     *
     * @param   string  $name   插件名称
     * @return  boolean
     * @throws  Exception
     */
    public static function check($name)
    {
        if (!$name || !is_dir( EASYSWOOLE_ROOT . ADDON_PATH . $name))
        {
            throw new Exception('Addon not exists');
        }
        $addonClass = get_addon_class($name);
        if (!$addonClass)
        {
            throw new Exception("插件主启动程序不存在");
        }
        $addon = new $addonClass();
        if (!$addon->checkInfo())
        {
            throw new Exception("配置文件不完整");
        }
        return true;
    }



     /**
     * 是否有冲突
     *
     * @param   string  $name   插件名称
     * @return  boolean
     * @throws  AddonException
     */
    public static function noconflict($name)
    {
        // 检测冲突文件
        $list = self::getGlobalFiles($name, true);
        if ($list)
        {
            //发现冲突文件，抛出异常
            throw new Exception("发现冲突文件".json_encode($list));
        }
        return true;
    }


    /**
     * 获取插件在全局的文件
     *
     * @param   string  $name   插件名称
     * @return  array
     */
    public static function getGlobalFiles($name, $onlyconflict = false)
    { 
        $list = [];
        $addonDir =  EASYSWOOLE_ROOT . ADDON_PATH . $name . DIRECTORY_SEPARATOR;
        // 扫描插件目录是否有覆盖的文件
        foreach (self::getCheckDirs() as $k => $dir)
        {
            $checkDir = EASYSWOOLE_ROOT .  DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR;
            
            if (!is_dir($checkDir))
                continue;
            //检测到存在插件外目录
            if (is_dir($addonDir . $dir))
            {
               
                //匹配出所有的文件
                $files = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($addonDir . $dir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST
                );
                
                foreach ($files as $fileinfo)
                {
                    if ($fileinfo->isFile())
                    {
                        $filePath = $fileinfo->getPathName();
                        $path = str_replace($addonDir, '', $filePath);
                        if ($onlyconflict)
                        {
                            $destPath = EASYSWOOLE_ROOT . $path;
                            if (is_file($destPath))
                            {
                                if (filesize($filePath) != filesize($destPath) || md5_file($filePath) != md5_file($destPath))
                                {
                                    $list[] = $path;
                                }
                            }
                        }
                        else
                        {
                            $list[] = $path;
                        }
                    }
                }
            }
        }
        return $list;
    }


    /**
     * 导入SQL
     * 
     * @param   string    $name   插件名称
     * @return  boolean
     */
    public static function importsql($name)
    {
        $sqlFile =  EASYSWOOLE_ROOT . ADDON_PATH . $name . DIRECTORY_SEPARATOR . 'install.sql';
        if (is_file($sqlFile))
        {
            $lines = file($sqlFile);
            $templine = '';
            foreach ($lines as $line)
            {
                if (substr($line, 0, 2) == '--' || $line == '' || substr($line, 0, 2) == '/*')
                    continue;

                $templine .= $line;
                if (substr(trim($line), -1, 1) == ';')
                {
                    $templine = str_ireplace('__PREFIX__', MYSQL_PREFIX, $templine);
                    $templine = str_ireplace('INSERT INTO ', 'INSERT IGNORE INTO ', $templine);
                    try
                    {
                        $queryBuild = new QueryBuilder();
                        // 支持参数绑定 第二个参数非必传
                        $queryBuild->raw($templine, []);
                        // 第二个参数 raw  指定true，表示执行原生sql
                        // 第三个参数 connectionName 指定使用的连接名，默认 default
                        $data = DbManager::getInstance()->query($queryBuild, true, 'default');
                    }
                    catch (\PDOException $e)
                    {
                        $e->getMessage();
                    }
                    $templine = '';
                }
            }
        }
        return true;
    }

    

    /**
     * 获取插件源资源文件夹
     * @param   string  $name   插件名称
     * @return  string
     */
    protected static function getSourceAssetsDir($name)
    {
        return  EASYSWOOLE_ROOT . ADDON_PATH . $name . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR;
    }

    /**
     * 获取插件目标资源文件夹
     * @param   string  $name   插件名称
     * @return  string
     */
    protected static function getDestAssetsDir($name)
    {
        $assetsDir = EASYSWOOLE_ROOT . str_replace("/", DIRECTORY_SEPARATOR, "/Public/assets/addons/{$name}/");
      
        if (!is_dir($assetsDir))
        {
            mkdir($assetsDir, 0755, true);
        }
        return $assetsDir;
    }

    /**
     * 获取检测的全局文件夹目录
     * @return  array
     */
    protected static function getCheckDirs()
    {
        return [
            'App'
        ];
    }


     /**
     * 获取远程服务器
     * @return  string
     */
    protected static function getServerConfig()
    {
        return \App\Http\Admin\Model\SystemConfigModel::getListByGroupName('easyadmin');
    }
}