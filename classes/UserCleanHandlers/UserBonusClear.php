<?php
/**
 * Created by PhpStorm.
 * User: royalwang
 * Date: 2018/12/12
 * Time: 14:04
 */

namespace Ecjia\App\User\UserCleanHandlers;

use Ecjia\App\User\UserCleanAbstract;

class UserBonusClear extends  UserCleanAbstract
{

    /**
     * 代号标识
     * @var string
     */
    protected $code = 'user_bonus_clear';

    /**
     * 名称
     * @var string
     */
    protected $name = '账户红包';


    /**
     * 数据描述及输出显示内容
     */
    public function handlePrintData()
    {

    }

    /**
     * 获取数据统计条数
     *
     * @return mixed
     */
    public function handleCount()
    {

    }


    /**
     * 执行清除操作
     *
     * @return mixed
     */
    public function handleClean()
    {

    }

    /**
     * 返回操作日志编写
     *
     * @return mixed
     */
    public function handleAdminLog()
    {

    }


}