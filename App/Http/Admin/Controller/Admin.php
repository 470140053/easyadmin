<?php


namespace App\Http\Admin\Controller;

use Extend\Base\BaseController;

abstract class Admin extends BaseController
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


    protected $userModel = \App\Http\Admin\Model\AuthUserModel::class;


    protected $authModel = \App\Http\Admin\Controller\Auth::class;


    protected $isIp = true;


    protected $logActionRoute = [];


    public function packageMap(array $map, array $data, bool $flag = true): array
    {
        $alias = '';
        if (!empty($this->model::create()->alias)) {
            $alias = $this->model::create()->alias . '.';
        }

        $modelColumns = $this->model::create()->schemaInfo();
        if (!isset($map[$alias . 'status']) && $flag === true) {
            if (!empty($modelColumns->getColumns()['status'])) {
                $map[$alias . 'status'] = [[0, 1], 'in'];
            }
        }

        return $map;
    }



    /**
     * Undocumented function
     * 分页数据
     * @return void
     */
    public function index()
    {

        //验证数据
        if (method_exists($this, '_indexVerify')) {
            if ($this->_indexVerify() === false) {
                return false;
            }
        } else if (!empty($this->validateName)) {
            if ($this->verify($this->validateName, 'lists') !== true) {
                $this->writeJson();
                return false;
            }
        }

        $map = [];
        $map = $this->packageMap($map, $this->data);



        if (!empty($this->data['search'])) {
            $map = $this->model::searchParam($this->data['search']);
        }

        //生成筛选条件
        if (method_exists($this, '_indexWhere')) {
            $map = (array) $this->_indexWhere($map);
        }

        $page = !empty($this->data['page']) ? $this->data['page'] : 1;

        $total = !empty($this->data['total']) ? $this->data['total'] : $this->total_number;

        //生成页面数据
        if (method_exists($this, '_indexData')) {
            list($list, $totalCount) = $this->_indexData($map, $page, $total);
        } else {
            list($list, $totalCount) = $this->model::getListByPage($map, $page, $total);
        }


        $returnData = [];
        if (method_exists($this, '_getIndexReturnData')) {
            $returnData = $this->_getIndexReturnData($list);
        }


        return $this->success('ok', array_merge([
            'list'          =>  $list,
            'totalCount'    =>  $totalCount,
        ], $returnData));
    }



    /**
     * 分类列表 - 不需要分页 直接返回tree
     */
    public function getList()
    {


        $map = [];

        if (!empty($this->data['search'])) {
            $map = $this->model::searchParam($this->data['search']);
        }

        //生成筛选条件
        if (method_exists($this, '_getListWhere')) {
            $map = (array)$this->_getListWhere($map);
        }

        $order = ['id', 'DESC'];
        $modelColumns = $this->model::create()->schemaInfo();
        if (!empty($modelColumns->getColumns()['sort'])) {
            $order = ['sort', 'ASC'];
        }
        //生成页面数据
        $list = $this->model::getListByCommonMap($map, ['*'], $order);

        $returnData = [];
        if (method_exists($this, '_getListReturnData')) {
            $returnData = $this->_getListReturnData($list);
        }

        return $this->success('ok', array_merge([
            'list'          =>  !empty($this->data['isTree']) ? makeTree($list) : $list,
            'pidKey'        => 'pid',
            'idKey'         => $this->model::create()->primaryKey,
            'pidNumber'     =>  '0',
        ], $returnData));
    }



    /**
     * 分类列表 - 不需要分页 直接返回tree
     */
    public function getCategory()
    {
        $map = [];

        if (!empty($this->data['search'])) {
            $map = $this->model::searchParam($this->data['search']);
        }



        //生成筛选条件
        if (method_exists($this, '_getCategoryWhere')) {
            $map = (array)$this->_getCategoryWhere($map);
        }

        $order = ['id', 'DESC'];
        $modelColumns = $this->model::create()->schemaInfo();
        if (!empty($modelColumns->getColumns()['sort'])) {
            $order = ['sort', 'ASC'];
        }

        if (method_exists($this, '_getCategoryField')) {
            $field = (array)$this->_getCategoryField();
        } else {
            $field = ['*'];
        }

        //生成页面数据
        $list = $this->model::getListByCommonMap($map, $field, $order);

        $returnData = [];
        if (method_exists($this, '_getCategoryReturnData')) {
            $returnData = $this->_getCategoryReturnData($list);
        }

        return $this->success('ok', array_merge([
            'list'          =>  !empty($this->data['isTree']) ? makeTree($list) : $list,
            'pidKey'        => 'pid',
            'idKey'         => $this->model::create()->primaryKey,
            'pidNumber'     =>  '0',
        ], $returnData));
    }

    /**
     * 获取详情
     * @return bool
     */
    public function getInfo()
    {

        $info = [];
        
        if (!empty($this->data[$this->model::create()->primaryKey])) {
            //获取信息
            if (method_exists($this, '_getInfo')) {
                $info = $this->_getInfo($this->data[$this->model::create()->primaryKey]);
            } else {
                $map[$this->model::create()->primaryKey] = $this->data[$this->model::create()->primaryKey];
                
                $info = $this->model::getInfoByMap($map);
            }
        }


        $returnData = [];
        if (method_exists($this, '_getReturnData')) {
            $returnData = $this->_getReturnData($info);
        }




        return $this->success('ok', array_merge([
            'info'    => $info
        ], $returnData));
    }



    public function export()
    {
        $map = [];
        if (!empty($this->data['search'])) {
            $map = $this->model::searchParam($this->data['search']);
        }
        if (!empty($this->data['check'])) {
            $map = $this->model::searchParam($this->data['check']);
        }
        //生成筛选条件
        if (method_exists($this, '_exportWhere')) {
            $map = (array) $this->_exportWhere($map);
        }

        //生成页面数据
        if (method_exists($this, '_exportData')) {
            $list = $this->_exportData($map);
        } else {
            $list = $this->model::getExportList($map);
        }


        $returnData = [];
        if (method_exists($this, '_getExportReturnData')) {
            $returnData = $this->_getExportReturnData($list);
        }

        return $this->success('ok', array_merge([
            'list'          =>  $list,
        ], $returnData));
    }

    /**
     * 添加
     * @return bool|void
     */
    public function add()
    {
        //验证数据
        if (method_exists($this, '_addVerify')) {
            if ($this->_addVerify() === false) {
                return false;
            }
        } else if (!empty($this->validateName)) {
            if ($this->verify($this->validateName, 'add') !== true) {
                $this->writeJson();
                return false;
            }
        }

        $data = $this->data;

        if (method_exists($this, '_addBefore')) {
            $data = $this->_addBefore($data);
            if (!$data) {
                return false;
            }
        }

        if (method_exists($this, '_saveData')) {
            $id = $this->_saveData($data, 'add');
        } else {
            $id = $this->model::saveData($data, 'add');
        }

        if ($id) {
            return  $this->success('添加成功！');
        } else {
            return  $this->error('添加失败！');
        }

    }

    /**
     * 修改
     * @return bool
     */
    public function edit()
    {

        //验证数据
        if (method_exists($this, '_editVerify')) {
            if ($this->_editVerify() === false) {
                return false;
            }
        } else if (!empty($this->validateName)) {
            if ($this->verify($this->validateName, 'edit') !== true) {
                $this->writeJson();
                return false;
            }
        }

        $data = $this->data;

        if (method_exists($this, '_editBefore')) {
            $data = $this->_editBefore($data);
            if (!$data) {
                return false;
            }
        }

        if (method_exists($this, '_saveData')) {
            $id = $this->_saveData($data, 'edit');
        } else {
            $id = $this->model::saveData($data, 'edit');
        }

        if ($id) {
            return  $this->success('修改成功！');
        } else {
            return  $this->error('修改失败！');
        }
    }


    /**
     * 状态操作
     */
    public function status()
    {
        //验证数据
        if (method_exists($this, '_statusVerify')) {
            if ($this->_statusVerify() === false) {
                return false;
            }
        } else if (!empty($this->validateName)) {
            if ($this->verify($this->validateName, 'status') !== true) {
                $this->writeJson();
                return false;
            }
        }

        $data = $this->data;

        if (method_exists($this, '_status')) {
            $result = $this->_status($data);
        } else {
            if (is_array($data[$this->model::create()->primaryKey])) {
                $result = $this->model::updateStatusByIds($data[$this->model::create()->primaryKey], $data['status']);
            } else {
                $result = $this->model::updateStatusById($data[$this->model::create()->primaryKey], $data['status']);
            }
        }

        if ($result) {
            return  $this->success('操作成功！');
        } else {
            return  $this->error('操作失败！');
        }

    }



    /**
     * 修改排序
     */
    public function sortField()
    {

        //验证数据
        if (method_exists($this, '_statusVerify')) {
            if ($this->_statusVerify() === false) {
                return false;
            }
        } else if (!empty($this->validateName)) {
            if ($this->verify($this->validateName, 'sortField') !== true) {
                $this->writeJson();
                return false;
            }
        }

        $data = $this->data;

        if (method_exists($this, '_sort')) {
            // $result = $this->_status($data[$this->model::create()->primaryKey],$data['sort']);
        } else {
            $result = $this->model::updateSortById($data[$this->model::create()->primaryKey], $data['sort']);
        }

        if ($result) {

            return $this->success('操作成功！');
        } else {
            return  $this->error('操作失败！');
        }
    }



    /**
     * 指定字段修改状态
     */
    public function appointField()
    {

        //验证数据
        if (method_exists($this, '_statusVerify')) {
            if ($this->_statusVerify() === false) {
                return false;
            }
        } else if (!empty($this->validateName)) {
            if ($this->verify($this->validateName, 'appointField') !== true) {
                $this->writeJson();
                return false;
            }
        }

        $data = $this->data;

        if (method_exists($this, '_sort')) {
            // $result = $this->_status($data[$this->model::create()->primaryKey],$data['sort']);
        } else {
            if (is_array($data['field'])) {
                $result = $this->model::updateFieldByIds($data[$this->model::create()->primaryKey], $data['field'], $this->data['value']);
            } else {
                $result = $this->model::updateFieldById($data[$this->model::create()->primaryKey], $data['field'], $this->data['value']);
            }
        }

        if ($result) {

            return $this->success('操作成功！');
        } else {
            return  $this->error('操作失败！');
        }
    }


    /**
     * 删除
     */
    public function del()
    {

        //验证数据
        if (method_exists($this, '_delVerify')) {
            if ($this->_delVerify() === false) {
                return false;
            }
        } else if (!empty($this->validateName)) {
            if ($this->verify($this->validateName, 'del') !== true) {
                $this->writeJson();
                return false;
            }
        }


        $id = $this->data[$this->model::create()->primaryKey];

        //删除前动作
        if (method_exists($this, '_delBefore')) {
            $data = $this->_delBefore($id);
            if ($this->response()->isEndResponse()) {
                return $data;
            }
        }

        if (method_exists($this, '_del')) {
            $result = $this->_del($id);
        } else {
            if (is_array($id)) {
                $result = $this->model::deleteAllByIds($id);
            } else {
                $result = $this->model::deleteInfoById($id);
            }
        }

        if ($result) {

            return $this->success('操作成功！');
        } else {
            return $this->error('操作失败！');
        }
    }

    /**
     * Undocumented function
     * 验证ip
     * @param [type] $ip
     * @return void
     */
    protected function __validateIpRoster($ip)
    {
        //白名单
        //先判断白名单是否设置，如果设置了白名单，只用配置白名单就可以了，反之，匹配黑名单

        //黑名单
        //判断此IP是否在黑名单中存在，存在返回false

        return true;
    }


    /**
     * Undocumented function
     * 记录操作日志
     * @return void
     */
    protected function __setLog()
    {
        if (!empty($this->logActionRoute)) {
            $pathInfo = $this->request()->getServerParams()['path_info'];
            $uri = explode('/', $pathInfo);
            $action = strtolower(array_pop($uri));
            $controller = strtolower(array_pop($uri));

            $arr = [];
            $controllerTitle = '';
            $actionTitle = '';
            foreach ($this->logActionRoute as $k => $v) {
                if (strtolower($k) === $controller) {
                    $controllerTitle = $v . '/';
                }

                if (strtolower($k) === $action) {
                    $actionTitle = $v;
                }
            }

            if (empty($actionTitle)) {
                return false;
            }

            $data = $this->data;
            $user = [];
            if (!empty($data['user'])) {
                $user = $data['user'];
                unset($data['user']);
            }


            $arr['title']       = $controllerTitle . $actionTitle;
            $arr['class']       = $this->request()->getUri()->getPath();
            $arr['useragent']   = $this->request()->getHeader('user-agent')[0];
            $arr['url']         = $this->request()->getServerParams()['path_info'];
            $arr['admin_id']    = !empty($user['id']) ? $user['id'] : 0;
            $arr['username']    = !empty($user['username']) ? $user['username'] : '';
            $arr['content']     = json_encode($this->data);
            $arr['ip']          = !empty($this->data['realIp']) ? ip2long($this->data['realIp']) : 0;

            \App\Http\Admin\Model\AdminLogModel::addInfo($arr);
        }

        return true;
    }
}
