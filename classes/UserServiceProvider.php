<?php

namespace Ecjia\App\User;

use Royalcms\Component\App\AppParentServiceProvider;

class UserServiceProvider extends  AppParentServiceProvider
{
    
    public function boot()
    {
        $this->package('ecjia/app-user', null, dirname(__DIR__));
    }
    
    public function register()
    {
        
    }
    
    
    
}