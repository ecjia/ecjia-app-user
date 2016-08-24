<?php
defined('IN_ECJIA') or exit('No permission resources.');

/**
 * 管理员登录
 * @author will
 *
 */
class signout_module implements ecjia_interface {
	
	public function run(ecjia_api & $api) {
		
		EM_Api::authSession();
		RC_Cookie::delete('ECJAP[admin_id]');
		RC_Cookie::delete('ECJAP[admin_pass]');
		
		RC_Session::destroy();
		
		return array();
	}
}


// end