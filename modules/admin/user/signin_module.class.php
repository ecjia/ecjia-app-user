<?php
defined('IN_ECJIA') or exit('No permission resources.');

/**
 * 管理员登录
 * @author will
 *
 */
class signin_module implements ecjia_interface {
	
	public function run(ecjia_api & $api) {
		
		$username	= _POST('username');
		$password	= _POST('password');
		$device		= _POST('device', array());
		if (empty($username) || empty($password)) {
			$result = new ecjia_error('login_error', __('您输入的帐号信息不正确。'));
			EM_Api::outPut($result);
		}
		$device['code'] = '8001';
		$device['client'] = 'android';
		$device['udid'] = '5f3434e351a1c2aaf0e27292851bc1f18bcc0a84';
		/* 收银台请求判断处理*/
		if (!empty($device) && is_array($device) && $device['code'] == '8001') {
			$adviser_info = RC_Model::model('achievement/adviser_model')->find(array('username' => $username));
			if (empty($adviser_info)) {
				$result = new ecjia_error('login_error', __('您输入的帐号信息不正确。'));
				return $result;
			}
			$admin_info = RC_Model::model('admin_user_model')->field(array('user_name', 'ec_salt'))->find(array('user_id' => $adviser_info['admin_id']));
			$username	= $admin_info['user_name'];
			$ec_salt	= $admin_info['ec_salt'];
		} else {
			$ec_salt = RC_Model::model('admin_user_model')->where(array('user_name' => $username))->get_field('ec_salt');
		}
		
	
		/* 检查密码是否正确 */
		if (!empty($ec_salt)) {
			$row = RC_Model::model('admin_user_model')->field('user_id, user_name, email, password, last_login, action_list, last_login, suppliers_id, ec_salt, ru_id, role_id')
						->find(array('user_name' => $username, 'password' => md5(md5($password).$ec_salt)));
		} else {
			$row = RC_Model::model('admin_user_model')->field('user_id, user_name, email, password, last_login, action_list, last_login, suppliers_id, ec_salt, ru_id, role_id')
						->find(array('user_name' => $username, 'password' => md5($password)));
		}
		
		if ($row) {
			// 登录成功
			/* 设置session信息 */
			RC_Session::set('admin_id',		$row['user_id']);
			RC_Session::set('admin_name',	$row['user_name']);
			RC_Session::set('action_list',	$row['action_list']);
			RC_Session::set('last_check_order', $row['last_login']);// 用于保存最后一次检查订单的时间
			RC_Session::set('suppliers_id', $row['suppliers_id']);
			RC_Session::set('action_list',	$row['action_list']);
			if (!empty($row['ru_id'])) {
				RC_Session::set('ru_id', $row['ru_id']);
			}
			
			/* 获取device_id*/
			$device_id = RC_Model::model('mobile/mobile_device_model')->where(array('device_udid' => $device['device_udid'], 'device_client' => $device['device_client'], 'device_code' => $device['device_code']))->get_field('id');
			RC_Session::set('device_id',	$device_id);
				
			if ($device['code'] == '8001') {
				RC_Session::set('adviser_id',	$adviser_info['id']);
				RC_Session::set('ru_id', 		$adviser_info['seller_id']);
				RC_Session::set('admin_name',	$adviser_info['username']);
			}
			
			if (empty($row['ec_salt'])) {
				$ec_salt = rand(1, 9999);
				$new_possword = md5(md5($_POST['password']) . $ec_salt);
				$data = array(
						'ec_salt'	=> $ec_salt,
						'password'	=> $new_possword
				);
				RC_Model::model('admin_user_model')->where(array('user_id' => $_SESSION['admin_id']))->update($data);
			}
		
			if ($row['action_list'] == 'all' && empty($row['last_login'])) {
				$_SESSION['shop_guide'] = true;
			}
		
			$data = array(
					'last_login' 	=> RC_Time::gmtime(),
					'last_ip'		=> RC_Ip::client_ip(),
			);
			RC_Model::model('admin_user_model')->where(array('user_id' => $_SESSION['admin_id']))->update($data);
		
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
			
			if ($device['code'] == '8001') {
				$out['userinfo']['username'] = $adviser_info['username'];
				$out['userinfo']['email']	 = $adviser_info['email'];
			}
			
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