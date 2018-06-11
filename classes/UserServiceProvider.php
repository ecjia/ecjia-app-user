<?php

namespace Ecjia\App\User;

use Royalcms\Component\App\AppServiceProvider;

class UserServiceProvider extends  AppServiceProvider
{
    
    public function boot()
    {
        $this->package('ecjia/app-user');
    }
    
    public function register()
    {
        
    }
    
    
    
}