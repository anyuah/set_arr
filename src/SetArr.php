<?php
/*
 * @Description: 数组处理
 * @Author: Chen YongHuan
 * @Date: 2021/7/1
 */

namespace Cyh\SetArr;

class SetArr
{
    /**
     * 把数据的健 =>用元素中的某个值代替，该元素多个相同只获取最后一个
     *
     * @param $results 需要格式化的数据
     * @param $field   返回字段值
     * @return
     */
    function static new_result($results = [], $key = 'id', $field = '')
    {
        $results_new = [];
        if(!empty($results)) {
            foreach($results as $val) {
                $results_new[$val[$key]] = $field ? $val[$field] : $val;
            }
        }
        return $results_new;
    }

    /**
     * 把数据的健 =>用元素中的某个值代替，该元素多个相同则放在同一数组下
     * @param $results 需要格式化的数据
     * @param $key 字段
     * @return
     */
    function static new_results_arr($results = [], $key = 'id')
    {
        $results_new = [];
        if(!empty($results)) {
            foreach($results as $val) {
                $results_new[$val[$key]][] = $val;
            }
        }
        return $results_new;
    }

    /**
     * 替换/增加 二位数组属性值
     *
     * @param array $data 需替换的二维数组
     * @param array $data $key[替换的健] => $val['替换的值']   (可多个健值)
     * @param array $data 需要删除的二维数组的健值  例：['id','key'...]
     */
    function static set_key_field($results = [], $data = [], $del_key = [])
    {
        if(empty($results) || empty($data) || !is_array($results) || !is_array($data))
            return $results;

        foreach($results as &$val) {
            if(!is_array($val)) {
                continue;
            }

            if(!empty($del_key)) {
                foreach($del_key as $del) {
                    if(!empty($val[$del]))
                        unset($val[$del]);
                }
            }

            foreach($data as $data_key => $data_val) {
                $val[$data_key] = $data_val;
            }
        }

        return $results;
    }

    /**
     * isset 也能检测null数据
     *
     * @param $value 检测的变量
     *
     * @return
     */
    function static issets($value)
    {
        if(!isset($value) && $value !== null)
            return false;

        return true;
    }

    /**
     * 对象数据转数组
     *
     * @param $data array|object 数据
     */
    function static results_array(&$data)
    {
        $data = is_object($data) ? $data->toArray() : $data;

        return $data;
    }

    /**
     * @param array  $data_mac     例子 ['ab' => 2,'id' => 3]
     * @param string $template_str 例子 {$data[id]}{$data[ab]}
     *                             例子结果 '23'
     *
     * @return null|string|string[]
     */
    function static results_preg_replace(array $data_mac, string $template_str)
    {

        $a = preg_replace_callback('/{\$data\[(.*?)\]}/', function($data_seach) use ($data_mac) {
            return isset($data_mac[$data_seach[1]]) ? $data_mac[$data_seach[1]] : '';
        }, $template_str);

        return $a;
    }

    /**
     * 数组里的所有字符串转数组
     * @param array $data 例子 ['1','2']
     *
     * @return array
     */
    function static results_int(array $data)
    {
        $data = !empty($data) ? array_map(function($value) {
            return (int)$value;
        }, $data) : $data;

        return $data;
    }

    /**
     * 数据中获取所有的上级
     * @param array|object $data
     * @return array|object
     */
    function static results_pids(array $data)
    {
        if(empty($data))
            return $data;

        is_object($data) && $data = $data->toArray();
        $data = new_result($data);

        //整理组织架构上级
        {
            //获取组织架构上级
            foreach($data as $key => $value) {
                if(!empty($value['pid'])) {
                    if(!empty($data[$value['pid']]['pids'])) {
                        $data[$key]['pids'] = $data[$value['pid']]['pids'] . ',' . $value['pid'];
                    }
                    else {
                        $data[$key]['pids'] = (string)$value['pid'];
                    }
                }
                else {
                    $data[$key]['pids'] = '';
                }
            }

            //返回所用上级 字符串转成 数组
            foreach($data as $key => $value) {
                $data[$key]['pids'] = !empty($data[$key]['pids']) ? explode(',', $value['pids']) : [];

                //字符串转整
                $data[$key]['pids'] = array_map(function($val) {
                    return abs($val);
                }, $data[$key]['pids']);
            }
        }

        return $data;
    }

    /**
     * 强制转化为非负整数
     *
     * @param mixed $value , 被转换的数据
     *
     * @return int
     */
    function static absint($value)
    {
        if(!is_scalar($value))
            return 0;

        return abs((int)$value);
    }

    /**
     * 根据条件获取二维数组的元素的某个键值的集合
     * @param array  $results 数组  例   [['id' => 1,'name' => '名称1','level' => 'A'],['id' => 2,'name' => '名称2','level' => 'B']]
     * @param array  $args    条件，限单个条件 例 ['id' => [1,2]] or ['id' => '1,2']
     * @param string $field   键名,限单个 例  ‘name’
     * @param string $type    返回类型, 0，数组 [‘名称1’,‘名称2’] ；1，字符串 ‘名称1,名称2’
     * @return array|mixed
     */
    function static get_value_field($results = [], $args = [], $field = '', $type = 0)
    {
        if(empty($results) || empty($args) || empty($field))
            return '';

        $data_key   = array_keys($args);
        $data_key   = $data_key[0] ?? '';
        $data_value = array_values($args);
        $data_value = $data_value[0] ? is_array($data_value[0]) ? $data_value[0] : explode(',', $data_value[0]) : [];

        $return = [];
        foreach($results as $key => $val) {
            if(!empty($val[$data_key]) && in_array($val[$data_key], $data_value)) {
                if(empty($val[$field]))
                    continue;
                $return[] = $val[$field];
            }
        }
        return $type == 1 ? implode(',', $return) : $return;
    }

    /**
     * 把一维数组的值转为int类型
     * @param array $data 数组  例   ['1','2']
     * @return array|mixed  [1,2]
     */
    function static set_value_int($data)
    {
        if(empty($data))
            return [];

        foreach($data as &$val) {
            $val = (int)$val;
        }
        return $data;
    }

    /**
     * 根据键过滤数组
     * @param array $data 数组  例   [1=>[id=>2,name=>ddd],2=>[id=>3,name=>ddd],3=>[id=>4,name=>ddd12]]
     * @param array $keys 要过滤的键  例   [1,3]
     * @return array|mixed  [[id=>2,name=>ddd],[id=>4,name=>ddd12]]
     */
    function static array_filter_keys($data = [], $keys = [])
    {
        if(empty($keys) || empty($data))
            return [];

        $results = array_filter($data, function($val, $key) use ($keys) {
            if(in_array($key, $keys)) {
                return true;
            }
        }, ARRAY_FILTER_USE_BOTH);
        return array_values($results);
    }

    /**
     * 删除数组中指定对应值的数据
     * @param array $arr 数组
     * @param mixed $val 数组得值
     * @return array
     */
    function static unset_array_value(array &$arr,$val)
    {
        $key = array_search($val,$arr);
        if($key!==false){
            unset($arr[$key]);
        }
        return $arr;
    }

    /**
     * 给二维数组添加元素
     * @param array $data 二维数组 如[['a' => 1,'aa' => 2],['b' => 1, 'bb' => 2]]
     * @param array $addArr 添加的元素,一维数组 如 $addArr = [‘line_id’ => 5]
     * @return array 如[['a' => 1,'aa' => 2,‘line_id’ => 5],['b' => 1, 'bb' => 2,‘line_id’ => 5]]
     */
    function static set_array_ele($data = [],$addArr = []){
        array_walk($data, function (&$value, $key, $addArr) {
            $value = array_merge($value, $addArr);
        },$addArr);
        return $data;
    }
}
