<?php

namespace Extend\Base;


use EasySwoole\ORM\AbstractModel;


abstract class BaseModel extends AbstractModel
{

    /**
     * Undocumented function
     * 取值 status 转为 string
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    protected static function getStatusAttr($value, $data)
    {
        if (isset($data['status']) && is_numeric($value)) {
            return (string) $value;
        }

        return $value;
    }

    /**
     * Undocumented function
     * 扩展属性
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    protected static function getAttrsAttr($value, $data)
    {
        return !empty($value) ? json_decode($value, true) : [];
    }

    /**
     * Undocumented function
     * 缩略图私有地址
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    protected static function getThumbTextAttr($value, $data)
    {
        return !empty($data['thumb']) ? getThumbUrlAttr($data['thumb']) : '';
    }


    /**
     * Undocumented function
     * 缩略图私有地址
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    protected static function getPictureTextAttr($value, $data)
    {
        return !empty($data['picture']) ? getThumbUrlAttr($data['picture']) : '';
    }



    /**
     * Undocumented function
     * 缩略图私有地址
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    protected static function getVideoTextAttr($value, $data)
    {
        return !empty($data['video']) ? getThumbUrlAttr($data['video']) : '';
    }

    /**
     * Undocumented function
     * 多图轮播图私有地址
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    protected static function getImagesTextAttr($value, $data)
    {
        return !empty($data['images']) ? getImagesUrlAttr($data['images']) : [];
    }




    /**
     * Undocumented function
     * 头像私有地址
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    protected static function getAvatarTextAttr($value, $data)
    {
        return !empty($data['avatar']) ? getThumbUrlAttr($data['avatar']) : '';
    }



    protected static function setAvatarAttr($value, $data)
    {
        return !empty($value) ? $value : 0;
    }



    /**
     * Undocumented function
     * 多条数据
     * @param array $map
     * @param array $field
     * @param array $order
     * @return array
     */
    public static function getListByCommonMap(array $map, array $field = ['*'], array $order = ['id', 'DESC']): array
    {
        $list = self::create()->where($map)->field($field)->order(...$order)->all();
        return !empty($list) ? voidToArray($list) : [];
    }


    /**
     * Undocumented function
     * 多条数据
     * @param array $map
     * @param array $field
     * @param array $order
     * @return array
     */
    public static function getPageListByCommonMap(array $map, array $field = ['*'], int $page = 1, int $total = 10, array $order = ['id', 'DESC']): array
    {
        return  self::create()->where($map)->field($field)->limit((($page - 1) * $total), $total)->order(...$order)->all();
    }

    /**
     * 搜索参数分解拼装
     */
    public static function searchParam(array $data): array
    {
        $where = [];
        foreach ($data as $k => $v) {
            if ($v == 0 || !empty($v)) {
                $tmpK = $sk = str_replace(['|', ','], '.', $k);
                $tmpKArr = explode('.', $tmpK);

                if (count($tmpKArr) > 1) {
                    $sk = $tmpKArr[1];
                }
                switch ($sk) {
                    case 'title':
                        $where[$tmpK] = ['%' . $v . '%', 'like'];
                        break;
                    case 'vod_name':
                        $where[$tmpK] = ['%' . $v . '%', 'like'];
                        break;
                    case 'address':
                        $where[$tmpK] = ['%' . $v . '%', 'like'];
                        break;
                    case 'username':
                        $where[$tmpK] = ['%' . $v . '%', 'like'];
                        break;
                    case 'street':
                        $where[$tmpK] = ['%' . $v . '%', 'like'];
                        break;
                    case 'user_type':
                        $where['find_in_set(?,'.$tmpK.')'] = [[$v],'exp'];
                        break;
                    case 'platform':
                        $where['find_in_set(?,'.$tmpK.')'] = [[$v],'exp'];
                        break;
                    case 'create_time':
                        if (count($v) > 1)
                            $where[$tmpK] = [[strtotime($v[0]), strtotime($v[1])], 'between'];
                        break;
                    case 'last_login_time':
                        if (count($v) > 1)
                            $where[$tmpK] = [[strtotime($v[0]), strtotime($v[1])], 'between'];
                        break;
                    case 'date':
                        if (count($v) > 1)
                            $where[$tmpK] = [[date('Ymd', strtotime($v[0])), date('Ymd', strtotime($v[1]))], 'between'];
                        break;
                    case 'reserve_time':
                        if (count($v) > 1)
                            $where[$tmpK] = [[strtotime($v[0]), strtotime($v[1])], 'between'];
                        break;
                    case 'reserve_time_range':
                        $alias = '';
                        if (count($tmpKArr) > 1) {
                            $alias = $tmpKArr[0] . '.';
                        }
                        $where[$alias . 'reserve_start_time'] = [strtotime($v[0]), '>='];
                        $where[$alias . 'reserve_end_time'] = [strtotime($v[1]), '<='];
                        break;
                    default:
                        $where[$tmpK] = $v;
                        break;
                }
            }
        }

        return $where;
    }


    /**
     * 修改状态
     */
    public static function updateStatusById(int $id, int $status): bool
    {

        $map['id'] = $id;

        $arr['status'] = $status;

        return self::updateInfo($map, $arr);
    }


    /**
     * 修改状态 多条数据
     */
    public static function updateStatusByIds(array $ids, int $status): bool
    {

        $map['id'] = [$ids, 'in'];

        $arr['status'] = $status;

        return self::updateInfo($map, $arr);
    }

    /**
     * Undocumented function
     * 修改指定字段
     * @param integer $id
     * @param string $field
     * @param integer|null $value
     * @return boolean
     */
    public static function updateFieldById(int $id, string $field, ?int $value = null): bool
    {

        $map['id'] = $id;

        $arr[$field] = $value;

        return self::updateInfo($map, $arr);
    }


    /**
     * Undocumented function
     *
     * @param array $ids
     * @param string $field
     * @param integer|null $value
     * @return boolean
     */
    public static function updateFieldAllByIds(array $ids, string $field, ?int $value = null): bool
    {

        $map['id'] = [$ids, 'in'];

        $arr[$field] = $value;

        return self::updateInfo($map, $arr);
    }


    /**
     * Undocumented function
     * 修改排序
     * @param integer $id
     * @param integer $sort
     * @return boolean
     */
    public static function updateSortById(int $id, int $sort): bool
    {

        $map['id'] = $id;

        $arr['sort'] = $sort;

        return self::updateInfo($map, $arr);
    }


    /**
     * Undocumented function
     * 通过主键删除
     * @param integer $id
     * @return boolean
     */
    public static function deleteInfoById(int $id): bool
    {

        $b = self::create()->destroy($id);
        if (!$b) {
            return false;
        }



        return true;
    }

    /**
     * Undocumented function
     * 软删除 批量
     * @param array $ids
     * @return boolean
     */
    public static function deleteAllByIds(array $ids): bool
    {
        $map['id'] = [$ids, 'in'];
        $arr['status'] = 2;
        return self::updateInfo($map, $arr);
    }


    /**
     * Undocumented function
     * 修改数据
     * @param array $map
     * @param array $data
     * @return boolean
     */
    public static function updateInfo(array $map, array $data): bool
    {
        return self::create()->update($data, $map);
    }


    /**
     * Undocumented function
     * 添加数据
     * @param array $data
     * @return integer
     */
    public static function addInfo(array $data): int
    {
        return self::create($data)->save();
    }



    /**
     * Undocumented function
     * 添加数据
     * @param array $data
     * @return integer
     */
    public static function addInfoAll(array $data): bool
    {
        $res = self::create()->saveAll($data, false);
        return $res ? true : false;
    }


    /**
     * Undocumented function
     * 统计条数
     * @param array $map
     * @return integer
     */
    public static function getCount(array $map = []): int
    {
        return (int) self::create()->where($map)->count();
    }



    /**
     * Undocumented function
     * 查询最大值
     * @param array $map
     * @param string $field
     * @return void
     */
    public static function getMax(array $map, string $field = 'id')
    {

        return  self::create()->where($map)->max($field);
    }


    /**
     * Undocumented function
     * 查询最小值
     * @param array $map
     * @param string $field
     * @return void
     */
    public static function getMin(array $map, string $field = 'id')
    {
        return  self::create()->where($map)->min($field);
    }


    /**
     * Undocumented function
     * 统计数量
     * @param array $map
     * @param string $field
     * @return void
     */
    public static function getSum(array $map, string $field = 'id'): int
    {
        $res = self::create()->where($map)->sum($field);

        return !empty($res) ? $res : 0;
    }

    public static function getInfoByMapAndOrder(array $map, array $field = ['*'],array $order=['id','DESC']): ?array
    {
        $info = self::create()->where($map)->field($field)->order(...$order)->get();

        return !empty($info) ? $info->toArray() : null;
    }

    /**
     * Undocumented function
     * 根据条件查询单条数据
     * @param array $map
     * @param array $field
     * @return array|null
     */
    public static function getInfoByMap(array $map, array $field = ['*']): ?array
    {
        $info = self::create()->where($map)->field($field)->get();

        return !empty($info) ? $info->toArray() : null;
    }


    public static function getInfoById(int $id, array $field = ['*']): ?array
    {
        return self::getInfoByMap(['id' => $id], $field);
    }




    /**
     * Undocumented function
     * 编辑、添加
     * @param array $data
     * @param string $type
     * @return void
     */
    public static function saveData(array $data, string $type)
    {


        if ($type === 'edit') {
            $pk = self::create()->primaryKey;
            if (empty($data[$pk])) {
                return false;
            }
            $map[$pk] = $data[$pk];
            $arr = self::create()->assemble::package($data);
            return self::updateInfo($map, $arr);
        } else {
            $arr = self::create()->assemble::packageData($data);

            return self::addInfo($arr);
        }
    }



    /**
     * 获取code码
     * @return int
     */
    public function getCode()
    {
        return returnUtil()->getCode();
    }

    /**
     * 获取error信息
     * @return string
     */
    public function getError()
    {
        return returnUtil()->getError();
    }

    /**
     * 失败
     * @param string $msg
     * @param int $code
     * @return bool
     */
    protected function error($msg = '', $code = 500)
    {
        return returnUtil()->error($msg, $code);
    }

    /**
     * 成功
     * @param bool $data
     * @param int $code
     * @return bool
     */
    protected function success($data = true, $code = 200)
    {
        return returnUtil()->success($data, $code);
    }
}
