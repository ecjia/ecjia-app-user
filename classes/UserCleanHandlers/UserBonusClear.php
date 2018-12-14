<?php
/**
 * Created by PhpStorm.
 * User: royalwang
 * Date: 2018/12/12
 * Time: 14:04
 */

namespace Ecjia\App\User\UserCleanHandlers;

use Ecjia\App\User\UserCleanAbstract;
use RC_DB;

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
        $count = $this->handleCount();

        return <<<HTML

<span class="controls-info">账户内可用红包<span class="ecjiafc-red ecjiaf-fs3">{$count}</span>个</span>

HTML;
    }

    /**
     * 获取数据统计条数
     *
     * @return mixed
     */
    public function handleCount()
    {
        $user_bonus_count = RC_DB::table('user_bonus')->where('user_id', $this->user_id)->where('used_time', 0)->count();

        return $user_bonus_count;

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

    /**
     * 是否允许删除
     *
     * @return mixed
     */
    public function handleCanRemove()
    {
        return !empty($this->handleCount()) ? true : false;
    }


}