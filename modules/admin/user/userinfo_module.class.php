<?php
defined('IN_ECJIA') or exit('No permission resources.');

/**
 * 管理员信息
 * @author will
 *
 */
class userinfo_module extends api_admin implements api_interface {
    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {
    		
		$this->authadminSession();
		$ecjia = RC_Loader::load_app_class('api_admin', 'api');
		$db = RC_Loader::load_model('admin_user_model');
		$role_db = RC_Loader::load_model('role_model');
		
		$result = $db->find(array('user_id' => $_SESSION['admin_id']));
		
		if (isset($_SESSION['adviser_id']) && !empty($_SESSION['adviser_id'])) {
			$adviser_info = RC_Model::model('achievement/adviser_model')->find(array('id' => $_SESSION['adviser_id']));
			$result['user_name'] = $adviser_info['username'];
			$result['email']	 = $adviser_info['email'];
		}
		
		$userinfo = array(
			'id' 		=> $result['user_id'],
			'username'	=> $result['user_name'],
			'email'		=> $result['email'],
			'last_login' 	=> RC_Time::local_date(ecjia::config('time_format'), $result['last_login']),
			'last_ip'		=> RC_Ip::area($result['last_ip']),
			'role_name'		=> $role_db->where(array('role_id' => $result['role_id']))->get_field('role_name'),
			'avator_img'	=> RC_Uri::admin_url('statics/images/admin_avatar.png'),
		);
		EM_Api::outPut($userinfo);
		
	}
}


// end