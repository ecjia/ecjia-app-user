<?php

namespace Ecjia\App\User\Integrate;

use Ecjia\System\Plugin\AbstractPlugin;

/**
 * 会员融合插件抽象类
 *
 * Class IntegrateAbstract
 * @package Ecjia\App\User\Integrate
 */
abstract class UserIntegrateAbstract extends AbstractPlugin implements UserIntegrateInterface
{

    /**
     * 用户名已经存在
     */
    const ERR_USERNAME_EXISTS       = 1;

    /**
     * Email已经存在
     */
    const ERR_EMAIL_EXISTS          = 2;

    /**
     * 无效的user_id
     */
    const ERR_INVALID_USERID        = 3;

    /**
     * 无效的用户名
     */
    const ERR_INVALID_USERNAME      = 4;

    /**
     * 密码错误
     */
    const ERR_INVALID_PASSWORD      = 5;

    /**
     * email错误
     */
    const ERR_INVALID_EMAIL         = 6;

    /**
     * 用户名不允许注册
     */
    const ERR_USERNAME_NOT_ALLOW    = 7;

    /**
     * EMAIL不允许注册
     */
    const ERR_EMAIL_NOT_ALLOW       = 8;


    protected $cookie_domain;

    protected $cookie_path;

    /* 是否需要同步数据到商城 */
    protected $need_sync = true;

    protected $error = 0;


    public function getError()
    {
        return $this->error;
    }

    public function needSync()
    {
        return $this->need_sync;
    }

    /**
     * 获取插件的元数据
     *
     * @return \Royalcms\Component\Support\Collection
     */
    abstract public function getPluginMateData();

    /**
     *  用户登录函数
     *
     * @param   string  $username
     * @param   string  $password
     *
     * @return boolean
     */
    public function login($username, $password, $remember = null)
    {
        if ($this->checkUser($username, $password) > 0) {
            if ($this->need_sync) {
                $this->sync($username, $password);
            }
            $this->setSession($username);
            $this->setCookie($username, $remember);
        
            return true;
        } else {
            return false;
        }
        
    }
    
    
    /**
     *
     * 用户退出登录
     * 
     * @return void
     */
    public function logout()
    {
        //清除cookie
        $this->clearCookie(); 
        
        //清除session
        $this->clearSession(); 
    }

    
    public function clearCookie()
    {
        
    }
    
    public function clearSession()
    {
        
    }
    
    
    
}