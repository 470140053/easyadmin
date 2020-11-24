<?php

namespace App\Storage;

/**
 * 基础类
 */
class Base
{

    /**
     * 重置data
     * @param $data
     * @param bool $isSet
     * @return array|mixed|string
     */
    public function resetData($data, bool $isSet = true)
    {
        if ($isSet) {
            return is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $data;
        } else {
            return !is_array($data) ? json_decode($data, true) : $data;
        }
    }
}
