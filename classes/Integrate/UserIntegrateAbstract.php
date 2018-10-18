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
    
    protected $cookie_domain;
    
    
    protected $cookie_path;

    /* 是否需要同步数据到商城 */
    public $need_sync = true;



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
        if ($this->check_user($username, $password) > 0) {
            if ($this->need_sync) {
                $this->sync($username,$password);
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