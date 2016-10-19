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
		
		if (!$_SESSION['admin_id']) {
		    return new ecjia_error(100, 'Invalid session' );
		}
		
		$result = RC_DB::table('staff_user')->where('user_id', $_SESSION['admin_id'])->first();
		
		if (isset($_SESSION['adviser_id']) && !empty($_SESSION['adviser_id'])) {
			$adviser_info = RC_Model::model('achievement/adviser_model')->find(array('id' => $_SESSION['adviser_id']));
			$result['user_name'] = $adviser_info['username'];
			$result['email']	 = $adviser_info['email'];
		}
		
		$staff_group = RC_DB::table('staff_group')->where('group_id', $_SESSION['group_id'])->pluck('group_name');
		$userinfo = array(
			'id' 		=> $result['user_id'],
			'username'	=> $result['mobile'],
			'email'		=> $result['email'],
			'last_login' 	=> RC_Time::local_date(ecjia::config('time_format'), $result['last_login']),
			'last_ip'		=> RC_Ip::area($result['last_ip']),
			'role_name'		=> $staff_group,
			'avator_img'	=> RC_Upload::upload_url($result['avatar']),
		);
		
		return $userinfo;
	}
}


// end