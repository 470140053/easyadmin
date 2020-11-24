<?php

namespace App\Http\Admin\Controller;

use App\Http\Admin\Model\AdminLogModel;

class Log  extends Admin
{
    protected $validateName = '\App\Http\Admin\Validate\AdminLogValidate';

    //不需要验证权限
    protected $noNeedRule = [];

    protected $model = AdminLogModel::class;

    protected function _indexWhere($map)
    {
        if (empty($this->data['user']['super']) || $this->data['user']['super'] !== 1) {
            $map['admin_id'] = $this->data['user']['id'];
        }

        return $map;
    }

    /**
     * Undocumented function
     * 清空
     * @return void
     */
    public function clearAll()
    {
        $map['id'] = [0, '>'];
        if (empty($this->data['user']['super']) || $this->data['user']['super'] !== 1) {
            $map['admin_id'] = $this->data['user']['id'];
        }
        $res = AdminLogModel::deleteInfoByMap($map);
        if (!$res) {
            return $this->error('清空失败！');
        }
        return $this->success('清空成功！');
    }
}
