<?php
/**
 * Created by PhpStorm.
 * User: royalwang
 * Date: 2018/10/19
 * Time: 9:51 AM
 */

namespace Ecjia\App\User\Integrate;

use ecjia_error;
use ecjia_config;
use RC_Hook;

/**
 * Class UserManager
 * @package Ecjia\App\User\Integrate
 *
 */
class UserManager
{
    /**
     * @var UserIntegrateAbstract
     */
    protected static $instance;

    public function __construct()
    {
        self::init_users();
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

    /**
     * 获取所有可用的插件
     */
    public function integrate_list()
    {
        return $this->plugin()->getEnableList();
    }

    /**
     * @return \Ecjia\App\User\Integrate\IntegratePlugin
     */
    public function plugin()
    {
        return royalcms('ecjia.integrate.plugin');
    }


    /**
     * 用户注册
     *
     * @param string $username 注册用户名
     * @param string $password 用户密码
     * @param string $email 注册email
     *
     * @return bool|\ecjia_error $bool
     */
    public function register($username, $password, $email)
    {
        /* 检查注册是否关闭 */
        if (ecjia_config::has('shop_reg_closed')) {
            return new ecjia_error('99999', '该网店暂停注册');
        }

        /* 检查username */
        if (empty($username)) {
            return new ecjia_error('200', '用户名不能为空');
        }

        if (preg_match('/\'\/^\\s*$|^c:\\\\con\\\\con$|[%,\\*\\"\\s\\t\\<\\>\\&\'\\\\]/', $username)) {
            return new ecjia_error('201', '用户名含有敏感字符');
        }

        /* 检查email */
        if (empty($email)) {
            return new ecjia_error('203', 'email不能为空');
        }

        if (!is_email($email)) {
            return new ecjia_error('204', '不是合法的email地址');
        }


        if (! self::$instance->addUser($username, $password, $email)) {
            if (self::$instance->getError() == (self::$instance)::ERR_INVALID_USERNAME) {

                return new ecjia_error('username_invalid', sprintf("用户名 %s 含有敏感字符", $username));

            } elseif (self::$instance->getError() == (self::$instance)::ERR_USERNAME_NOT_ALLOW) {

                return new ecjia_error('username_not_allow', sprintf("用户名 %s 不允许注册", $username));

            } elseif (self::$instance->getError() == (self::$instance)::ERR_USERNAME_EXISTS) {

                return new ecjia_error('username_exist', sprintf("用户名 %s 已经存在", $username));

            } elseif (self::$instance->getError() == (self::$instance)::ERR_INVALID_EMAIL) {

                return new ecjia_error('email_invalid', sprintf("%s 不是合法的email地址", $email));

            } elseif (self::$instance->getError() == (self::$instance)::ERR_EMAIL_NOT_ALLOW) {

                return new ecjia_error('email_not_allow', sprintf("Email %s 不允许注册", $email));

            } elseif (self::$instance->getError() == (self::$instance)::ERR_EMAIL_EXISTS) {

                return new ecjia_error('email_exist', sprintf("%s 已经存在", $email));

            } else {

                return new ecjia_error('unknown_error', '未知错误！');

            }

        } else {
            // 注册成功
            /* 设置成登录状态 */
            self::$instance->setSession($username);
            self::$instance->setCookie($username);

            /**
             * 用户注册成功后做一些事
             */
            RC_Hook::do_action('user_register_success_do_something', $username);

            /**
             * 用户登录成功后做一些事
             */
            RC_Hook::do_action('user_login_success_do_something', $username);

            return true;
        }
    }


    /**
     * 登录函数
     *
     * @param string $username 注册用户名
     * @param string $password 用户密码
     *
     * @return bool|\ecjia_error $bool
     */
    public function login($username, $password)
    {
        /* 检查username */
        if (empty($username)) {
            return new ecjia_error('200', '用户名不能为空');
        }

        if (preg_match('/\'\/^\\s*$|^c:\\\\con\\\\con$|[%,\\*\\"\\s\\t\\<\\>\\&\'\\\\]/', $username)) {
            return new ecjia_error('201', '用户名含有敏感字符');
        }

        if (! self::$instance->login($username, $password)) {
            return new ecjia_error('login_failure', '登录失败');
        }

        /**
         * 用户登录成功后做一些事
         */
        RC_Hook::do_action('user_login_success_do_something', $username);

        return true;
    }



}