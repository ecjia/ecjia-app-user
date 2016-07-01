<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 修改密码
 * @author royalwang
 *
 */
class password_module implements ecjia_interface {
	
	public function run(ecjia_api & $api) {
		EM_Api::authSession();
 		RC_Loader::load_app_class('integrate', 'user', false);
 		$user = integrate::init_users();
 		
 		$old_password = _POST('password', '');
 		$new_password = _POST('new_password', '');
 		$code		  = _POST('code', '');
		$user_id	  = _POST('uid', $_SESSION['user_id']);
	    
		if (strlen($new_password) < 6) {
	    	$result = new ecjia_error('password_shorter', __('- 登录密码不能少于 6 个字符。'));
	    	EM_Api::outPut($result);
	    }
	
	    $user_info = $user->get_profile_by_id($user_id); //论坛记录
	   
	    if (($user_info && (!empty($code) && md5($user_info['user_id'] . ecjia::config('hash_code') . $user_info['reg_time']) == $code)) || ($_SESSION['user_id']>0 && $_SESSION['user_id'] == $user_id && $user->check_user($_SESSION['user_name'], $old_password))) {
			if ($user->edit_user(array('username'=> (empty($code) ? $_SESSION['user_name'] : $user_info['user_name']), 'old_password'=>$old_password, 'password'=>$new_password), empty($code) ? 0 : 1)) {
	        	$db = RC_Loader::load_app_model('users_model', 'user');
	        	$db->where(array('user_id' => $user_id))->update(array('ec_salt' => 0));
	            $user->logout();
	            RC_Model::model('session_model')->delete(array('user_id' => $user_id));
	            return array();
	        } else {
	        	$result = new ecjia_error('edit_password_failure', __('您输入的原密码不正确！'));
	        }
	    } else {
	    	$result = new ecjia_error('edit_password_failure', __('您输入的原密码不正确！'));
	    }
	    
		if (is_ecjia_error($result)) {
			EM_Api::outPut($result);
		}
	}
}

// end