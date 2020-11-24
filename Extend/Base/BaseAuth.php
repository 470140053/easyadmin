<?php

namespace Extend\Base;

abstract class BaseAuth
{

    // 权限开关 
    protected $authOn = true;

    protected $GroupModel = '';

    protected $RuleModel = '';


    /**
     * 验证
     */
    public function check(string $name, array $queryParam, int $uid, int $type = 1, string $relation = 'or')
    {

        if (!$this->authOn) {

            return true;
        }

        // 获取用户需要验证的所有有效规则列表
        $authList = $this->getAuthList($uid, $type);

        $nameArr = [];

        if (is_string($name)) {

            $name = strtolower($name);

            if (strpos($name, ',') !== false) {

                $nameArr = explode(',', $name);
            } else {

                $nameArr = [$name];
            }
        }

        //保存验证通过的规则名
        $list = [];


        foreach ($authList as $auth) {

            $auth = strtolower($auth);

            $query = preg_replace('/^.+\?/U', '', $auth);

            if ($query != $auth) {

                parse_str($query, $param); //解析规则中的param

                $intersect = array_intersect_assoc($queryParam, $param);

                $auth = preg_replace('/\?.*$/U', '', $auth);

                if (in_array($auth, $nameArr) && $intersect == $param) {

                    //如果节点相符且url参数满足
                    $list[] = $auth;
                }
            } else {
                if (in_array($auth, $nameArr)) {

                    $list[] = $auth;
                }
            }
        }

        if ('or' == $relation && !empty($list)) {

            return true;
        }


        $diff = array_diff($nameArr, $list);

        if ('and' == $relation && empty($diff)) {

            return true;
        }


        return false;
    }


    //规则列表
    protected function getAuthList(int $uid)
    {


        $groupList = $this->GroupModel::getGroupAndRuleListByUid($uid);

        $ids = [];

        foreach ($groupList as $v) {

            $ids = array_merge($ids, $v['rules']);
        }

        $ids = array_unique($ids);

        if (empty($ids)) {

            return [];
        }

        $map['id'] = [$ids, 'in'];

        $map['status'] = 1;

        $authList = $this->RuleModel::getListByCommonMap($map, ['name']);

        if (empty($authList)) {

            return [];
        }

        $rules = [];

        foreach ($authList as $f) {

            $rules[] = strtolower($f['name']);
        }

        $rules = array_unique($rules);

        return $rules;
    }
}
