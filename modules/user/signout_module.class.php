<?php
defined('IN_ECJIA') or exit('No permission resources.');

class signout_module implements ecjia_interface {
	
	public function run(ecjia_api & $api) {
		
		EM_Api::authSession();
		RC_Loader::load_app_class('integrate', 'user', false);
		$user = integrate::init_users();
		$user->logout();
		
		return array();
	}
}



// end