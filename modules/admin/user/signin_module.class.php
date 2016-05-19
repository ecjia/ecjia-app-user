<?php
defined('IN_ECJIA') or exit('No permission resources.');

/**
 * 管理员登录
 * @author will
 *
 */
class signin_module implements ecjia_interface {
	
	public function run(ecjia_api & $api) {
		
		$username = _POST('username');
		$password = _POST('password');
		$device = _POST('device', array());
		if (empty($username) || empty($password)) {
			$result = new ecjia_error('login_error', __('您输入的帐号信息不正确。'));
			EM_Api::outPut($result);
		}
		
		$db_admin_user = RC_Loader::load_sys_model('admin_user_model');
		$ec_salt = $db_admin_user->where(array('user_name' => $username))->get_field('ec_salt');
	
		/*收银台请求处理*/
		if (!empty($device) && is_array($device) && $device['code'] == '8001') {
			$device_id = RC_Model::model('mobile/mobile_device_model')->where(array('device_udid' => $device['device_udid'], 'device_client' => $device['device_client'], 'device_code' => $device['device_code']))->get_field('id');
			$admin_id = $db_admin_user->where(array('user_name' => $username))->get_field('user_id');
			$adviser_id = RC_Model::model('achievement/adviser_model')->where(array('admin_id' => $admin_id))->get_field('id');
			if ($device_id > 0) {
				RC_Session::set('device_id', $device_id);
			}
			if ($adviser_id > 0) {
				RC_Session::set('adviser_id', $adviser_id);
			}
			if (empty($adviser_id) || $adviser_id == '0') {
				$result = new ecjia_error('login_error', __('您不是收银员。'));
				EM_Api::outPut($result);
			}
		}
		
		/* 检查密码是否正确 */
		if (!empty($ec_salt)) {
			$row = $db_admin_user->field('user_id, user_name, email, password, last_login, action_list, last_login, suppliers_id, ec_salt, ru_id, role_id')
						->find(array('user_name' => $_POST['username'], 'password' => md5(md5($password).$ec_salt)));
		} else {
			$row = $db_admin_user->field('user_id, user_name, email, password, last_login, action_list, last_login, suppliers_id, ec_salt, ru_id, role_id')
						->find(array('user_name' => $_POST['username'], 'password' => md5($password)));
		}
		
		if ($row) {
			// 登录成功
			/* 设置session信息 */
			RC_Session::set('admin_id', $row['user_id']);
			RC_Session::set('admin_name', $row['user_name']);
			RC_Session::set('action_list', $row['action_list']);
			RC_Session::set('last_check_order', $row['last_login']);// 用于保存最后一次检查订单的时间
			RC_Session::set('suppliers_id', $row['suppliers_id']);
			RC_Session::set('action_list', $row['action_list']);
			if (!empty($row['ru_id'])) {
				RC_Session::set('ru_id', $row['ru_id']);
			}
			
			if (empty($row['ec_salt'])) {
				$ec_salt = rand(1, 9999);
				$new_possword = md5(md5($_POST['password']) . $ec_salt);
				$data = array(
						'ec_salt'	=> $ec_salt,
						'password'	=> $new_possword
				);
				$db_admin_user->where(array('user_id' => $_SESSION['admin_id']))->update($data);
			}
		
			if ($row['action_list'] == 'all' && empty($row['last_login'])) {
				$_SESSION['shop_guide'] = true;
			}
		
			$data = array(
					'last_login' 	=> RC_Time::gmtime(),
					'last_ip'		=> RC_Ip::client_ip(),
			);
			$db_admin_user->where(array('user_id' => $_SESSION['admin_id']))->update($data);
		
			$out = array(
					'session' => array(
						'sid' => RC_Session::session_id(),
						'uid' => $_SESSION['admin_id']
					),
			);
			$role_db = RC_Loader::load_model('role_model');
			$role_name = $role_db->where(array('role_id' => $row['role_id']))->get_field('role_name');
			$out['userinfo'] = array(
					'id' 			=> $row['user_id'],
					'username'		=> $row['user_name'],
					'email'			=> $row['email'],
					'last_login' 	=> RC_Time::local_date(ecjia::config('time_format'), $row['last_login']),
					'last_ip'		=> RC_Ip::area($row['last_ip']),
					'role_name'		=> !empty($role_name) ? $role_name : '',
					'avator_img'	=> RC_Uri::admin_url('statics/images/admin_avatar.png'),
			);
			
			//修正关联设备号
			$result = ecjia_app::validate_application('mobile');
			if (!is_ecjia_error($result)) {
				$device = _POST('device', array());
				if (!empty($device['udid']) && !empty($device['client']) && !empty($device['code'])) {
					$db_mobile_device = RC_Loader::load_app_model('mobile_device_model', 'mobile');
					$device_data = array(
							'device_udid'	=> $device['udid'],
							'device_client'	=> $device['client'],
							'device_code'	=> $device['code']
					);
					$db_mobile_device->where($device_data)->update(array('user_id' => $_SESSION['admin_id'], 'is_admin' => 1));
				}
			}
			
			EM_Api::outPut($out);
			
		} else {
			$result = new ecjia_error('login_error', __('您输入的帐号信息不正确。'));
			EM_Api::outPut($result);
		}
	    
	}
}


// end