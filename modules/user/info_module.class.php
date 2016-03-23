<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 用户信息
 * @author royalwang
 *
 */
class info_module implements ecjia_interface {
	
	public function run(ecjia_api & $api) {

 		EM_Api::authSession();
 		
		RC_Loader::load_app_func('user', 'user');
		
		$user_info = EM_user_info($_SESSION['user_id']);
		return $user_info;
	}
}

// end