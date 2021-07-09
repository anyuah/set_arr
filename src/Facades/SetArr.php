<?php
/*
 * @Description: 数组处理
 * @Author: Chen YongHuan
 * @Date: 2021/7/1
 */

namespace Cyh\SetArr\Facades;

use Illuminate\Support\Facades\Facade;
class SetArr extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'set_arr';
    }

