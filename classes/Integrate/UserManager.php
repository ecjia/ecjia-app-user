<?php
/**
 * Created by PhpStorm.
 * User: royalwang
 * Date: 2018/10/19
 * Time: 9:51 AM
 */

namespace Ecjia\App\User\Integrate;


class UserManager
{

    protected static $instance;

    public function __construct()
    {

    }

    /**
     * 初始化会员数据整合类
     *
     * @return mixed
     */
    public static function init_users()
    {
        if (is_null(self::$instance)) {
            return self::$instance;
        }

        self::$instance = with(new IntegratePlugin())->defaultChannel();

        return self::$instance;
    }






}