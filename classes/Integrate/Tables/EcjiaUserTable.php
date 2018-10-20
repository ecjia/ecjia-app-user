<?php

namespace Ecjia\App\User\Integrate\Tables;

use Ecjia\App\User\Integrate\UserField;

class EcjiaUserTable extends UserField
{
    
    public function __construct()
    {
        
        $this->table       = 'users';
        
        $this->field_id 		= 'user_id';
        
        $this->field_name 		= 'user_name';
        
        $this->field_pass 		= 'password';
        
        $this->field_email 		= 'email';
        
        $this->field_gender 	= 'sex';
        
        $this->field_birthday	= 'birthday';
    
        $this->field_reg_date 	= 'reg_time';
    }
    
}