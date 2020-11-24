<?php


namespace App\Http\Admin\Controller;

use App\Common\QiNiuUpload;
use App\Http\Admin\Model\CoverModel;


class Upload extends Admin
{
    protected $validateName = '\App\Http\Admin\Validate\UplodeValidate';

    //不需要验证权限
    protected $noNeedRule = ['getUplodeToken', 'getImagePath', 'getPrivateImagePath'];


    /**
     * Undocumented function
     * 获得token
     * @return void
     */
    public function getUplodeToken()
    {
        if ($this->verify($this->validateName, 'getUplodeToken') !== true) {

            $this->writeJson();

            return false;
        }


        $upload = new QiNiuUpload();

        $result = $upload->getToken($this->data['filename'], $this->data['is_private']);

        if (!$result) {
            $data = $upload->getData();

            return $this->success($upload->getError(), [
                'path' => $data['path'],
                'url'  => $data['src'],
                'cover_id'  => $data['cover_id']
            ], 201);
        }


        return $this->success('ok', [
            'token' => $result['token'],
            'hostUrl' => $result['hostUrl'],
            'storage' => $result['storage']
        ]);
    }



    /**
     * Undocumented function
     * 获得私有地址
     * @return void
     */
    public function getImagePath()
    {

        if ($this->verify($this->validateName, 'getImagePath') !== true) {

            $this->writeJson();

            return false;
        }

        $upload = new QiNiuUpload();

        $res = $upload->setOver($this->data);
        if (!$res) {
            return $this->error('图片保存失败！');
        }


        $src = $upload->returnAuthPath($this->data['img_path']);

        return $this->success('ok', [
            'path' => $this->data['img_path'],
            'url'  => $src,
            'cover_id' => $res
        ]);
    }


    public function getPrivateImagePath()
    {
        if ($this->verify($this->validateName, 'getPrivateImagePath') !== true) {

            $this->writeJson();

            return false;
        }

        $src = [];
        $img_path = [];
        $cover_id = [];
        $upload = new QiNiuUpload();
        if (is_array($this->data['ids'])) {
            $map['id'] = [$this->data['ids'], 'in'];
            $list =  CoverModel::getListByCommonMap($map);
            foreach ($list  as $v) {
                $host = !empty($v['host']) ? $v['host'] . '/' : '';
                $path = $host . $v['path'];
                $src[] = $upload->returnAuthPath($path);
                $cover_id[] = $v['id'];
                $img_path[] = $path;
            }
        } else {
            $info = CoverModel::getInfoById($this->data['ids']);

            $host = !empty($info['host']) ? $info['host'] . '/' : '';
            $path = $host . $info['path'];

            $src[] = $upload->returnAuthPath($path);

            $cover_id[] = $info['id'];

            $img_path[] = $path;
        }

        return $this->success('ok', [
            'path' => $img_path,
            'url'  => $src,
            'cover_id' => $cover_id
        ]);
    }
}
