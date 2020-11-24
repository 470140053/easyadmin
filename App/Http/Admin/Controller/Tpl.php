<?php

namespace App\Http\Admin\Controller;

use App\Http\Admin\Model\TemplatesModel;

class Tpl  extends Admin
{
    protected $validateName = '\App\Http\Admin\Validate\TplValidate';

    //不需要验证权限
    protected $noNeedRule = ['getInfo', 'getCategory'];

    protected $model = TemplatesModel::class;


    protected function _addBefore($data)
    {

        $result = TemplatesModel::getInfoByAlias($data['alias'], ['id']);
        if (!empty($result)) {
            $this->error('此别名已经存在了！');
            return false;
        }

        return $data;
    }


    protected function _editBefore($data)
    {
        $result = TemplatesModel::getInfoByAlias($data['alias'], ['id'], $data['id']);
        if (!empty($result)) {
            $this->error('此别名已经存在了！');
            return false;
        }

        return $data;
    }



    public function copy()
    {
        if ($this->verify($this->validateName, 'copy') !== true) {
            $this->writeJson();
            return false;
        }

        $field = ['title', 'alias', 'template_type', 'index_url', 'ajax_url', 'template_content', 'status', 'sort', 'is_footer'];
        $info = TemplatesModel::getInfoById($this->data['id'], $field);
        if (empty($info)) {
            return $this->error('数据错误！');
        }

        $res =  TemplatesModel::addInfo($info);
        if (!$res) {
            return $this->error('复制数据添加失败！');
        }

        return $this->success('复制数据添加成功！');
    }



    public function getAlias() {
        if(empty($this->data['alias'])) {
            return $this->error('别名不能为空！');
        }
        $info = TemplatesModel::getInfoByAlias($this->data['alias'], ['*']);
        return $this->success('ok',[
            'info'  => $info
        ]);
    }
}
