<?php


namespace App\Common;


use App\Http\Admin\Model\QiniuConfigModel;

use App\Http\Admin\Model\CoverModel;

use App\Common\Imgcompress;

use Qiniu\Storage\UploadManager;
use Qiniu\Storage\BucketManager;
use Qiniu\Auth;




class QiNiuUpload {


    protected $data = [];

    protected $msg = '';

    protected $code = 400;

    protected $prefix = [
        'img'     => ['png','jpg','jpeg','gif','webp'],
        'video'   => ['mp4','webm'],
    ];

    public static function create() :QiNiuUpload{
        return new self();
    }


    public static function getConfig() {
        $map['status'] = 1;
        $res = QiniuConfigModel::getInfoByMap($map);
        if(empty($res)) {
            self::create()->code = 500;
            self::create()->msg = '七牛配置错误！';
        }

        return $res;
    }


    /**
     * Undocumented function
     * 检查图片是否存在
     * @param [type] $hostUrl
     * @param [type] $name
     * @return void
     */
    public  function getOver($hostUrl,$name){

        $map['host'] = $hostUrl;
        $map['name'] = $name;
        $result = CoverModel::getInfoByMap($map);

        
        if(!empty($result)) {

            $path = $result['host'] .'/'. $result['path'];

            $this->code = 200;

            $this->msg = '上传成功！';

            $this->data = [
                'name' => $name,

                'path'=>$path,

                'cover_id'=>$result['id'],


                'src'=> $this->returnAuthPath($path)

            ];

            return true;
        }

        return false;
    }


    /**
     * Undocumented function
     * 存储图片进库
     * @param array $data
     * @return void
     */
    public  function setOver(array $data){
        return  CoverModel::addInfo($data);
    }

    

    /**
     * 返回错误信息
     */
    public function getError() : string {

        return $this->msg;
    }


    public function getCode() : int {
        return $this->code;
    }

    /**
     * 获得返回内容
     */
    public function getData() : array {

        return $this->data;
    }


    /**
     * Undocumented function
     * 生成私有链接
     * @param string $baseUrl
     * @return string
     */
    public function returnAuthPath(string $baseUrl) :string {

        $config =  self::getConfig() ;

        $auth = new Auth($config['access_key'],$config['secret_key']);
    
        return $auth->privateDownloadUrl($baseUrl);
    }



    /**
     * Undocumented function
     * 获得token
     * @param integer $uid
     * @param string $fileName
     * @param integer $isPrivate
     * @return void
     */
    public function getToken(string $fileName,int $isPrivate=0) {
        $config =  self::getConfig();

        if($isPrivate == 1) {
            $storage = $config['storage'];

            $hostUrl = $config['host_url'];

        }else{
            $storage = $config['com_storage'];

            $hostUrl = $config['com_host_url'];
        }

        $res = $this->getOver($hostUrl,$fileName);
        if($res === true) {
            return false;
        }

        $auth = new Auth($config['access_key'],$config['secret_key']);

        $token = $auth->uploadToken($storage);

        return [
            'token' => $token,
            'hostUrl' => $hostUrl,
            'storage' => $storage
        ];
    
    }



    /**
     * Undocumented function
     * base64上传图片
     * @param string $file 图片主体
     * @param string $name 图片名称
     * @param boolean $compress true-压缩 false-不压缩
     * @param integer $isPrivate 0-公用 1-私有
     * @return void
     */
    public function uplodeByBase64(string $file='', string $name='',bool $compress=true,int $isPrivate=1,$prefix='img'){
        $reg = '/^(data:\s*image\/(\w+);base64,)/';
        if(!preg_match($reg, $file, $fileResult)) {
            $this->code = 400;

            $this->msg = '上传失败，原因：此图片不是base64！';

            return false;
        }

        if(!in_array(strtolower($fileResult[2]),$this->prefix[$prefix])) {
            $this->code = 400;

            $this->msg = '上传失败，原因：只能上传'.implode(',',$this->prefix[$prefix]).'的图片！';

            return false;
        }

        $config =  self::getConfig();

        if($isPrivate == 1) {
            $storage = $config['storage'];

            $hostUrl = $config['host_url'];

        }else{
            $storage = $config['com_storage'];

            $hostUrl = $config['com_host_url'];
        }

        $result = $this->getOver($hostUrl,$name);
        if($result === true) {
            return false;
        }

        //生成路径的文件名
        $path = md5($name.time()).'.'.$fileResult[2];
        //File主体解码
        $getFile = base64_decode(str_replace($fileResult[1], '', $file));

     

        //是否压缩
        if($compress && strtolower($fileResult[2]) !== 'png') {
            //设置临时路径
            $basePutUrl = 'Public/Uploads/Tmp/';
            
            //临时路径
            $tmpPath = $basePutUrl.$path;

            $compressPath = $basePutUrl.'compress_'.$path;

            if(!is_dir($basePutUrl)){
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir($basePutUrl, 0755,true);
            }

           
            //写入文件
            $putResult =  file_put_contents($tmpPath, $getFile, FILE_APPEND);

            if(!$putResult) {
                $this->code = 400;
                $this->msg = '上传失败，原因：压缩时文件写入失败！';

                return false;

            }

            //压缩最大宽度
            $width=500;
            //压缩最大高度
            $heigth=0;
            //压缩质量
            $level=9;
            //开始压缩
            $res = (new Imgcompress())->set($tmpPath, $compressPath)->compress($level)->resize($width,$heigth)->get();

           
            $getFile = file_get_contents($compressPath);

            if(empty($getFile)) {   
                $this->code = 400;
                $this->msg = '上传失败，原因：压缩失败！';

                return false;
            }
           
            if(file_exists($tmpPath)) {
                
                //删除文件
                unlink($tmpPath);
    
            }

        }

        $auth = new Auth($config['access_key'],$config['secret_key']);

        $token = $auth->uploadToken($storage);

        // 构建 UploadManager 对象
        $upManager = new UploadManager();
        
        
        if($prefix=='video'){
            list($ret, $error) = $upManager->putFile($token,$path, $getFile);
        }else{
            list($ret, $error) = $upManager->put($token,$path, $getFile);
        }
       

        if(!empty($compressPath) && file_exists($compressPath)) {
        
            //删除文件
            unlink($compressPath);

        }


        if($error){
            $this->code = 400;
            $this->msg = '上传失败，原因：'.$error.'！';

            return false;
        }

        $res = $this->setOver([
            'path'  => $ret['key'],
            'host'  => $hostUrl,
            'type'  => 1,
            'name'  => $name,
            'hash'  => $ret['hash'],
            'is_private'  => $isPrivate,
            'create_time'  => time(),
        ]);

        if(!$res) {
            $this->code = 400;
            $this->msg = '上传失败，原因：文件保存失败！';

            return false;
        }


        return  $this->getOver($hostUrl,$name);
        

    }




    public function deleteUrl(int $id) {
        
        $info = CoverModel::getInfoById($id);
        if(empty($info)) {
            return false;
        }

        $config =  self::getConfig();

        if($info['is_private'] == 1) {
            $storage = $config['storage'];

            $hostUrl = $config['host_url'];

        }else{
            $storage = $config['com_storage'];

            $hostUrl = $config['com_host_url'];
        }

        $auth = new Auth($config['access_key'],$config['secret_key']);

        $bucketMgr = new BucketManager($auth);

        return $bucketMgr->delete($storage,$info['path']);  
    }
}