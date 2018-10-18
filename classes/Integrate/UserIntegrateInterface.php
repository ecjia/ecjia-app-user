<?php

namespace Ecjia\App\User\Integrate;


interface UserIntegrateInterface
{
    
    
    public function compilePassword($cfg);
    
    
    
    public function checkUser($username, $password = null);
    
    
    
    public function checkEmail($email);
    
    
    /**
     *  设置指定用户SESSION
     *
     * @access  public
     * 
     * @return void
     */
    public function setSession($username);
    
    
    public function clearSession();
    
    
    /**
     *  设置cookie
     *
     * @return void
     */
    public function setCookie($username, $remember = null);
    
    
    public function clearCookie();
    
    
    
    
}