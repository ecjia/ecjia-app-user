<?php
defined('IN_ECJIA') or exit('No permission resources.');

/**
 * 管理员登录
 * @author will
 *
 */
class signin_module extends api_admin implements api_interface {
    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {
		$this->authadminSession();
		
		$username	= $this->requestData('username');
		$password	= $this->requestData('password');
		$device		= $this->requestData('device', array());
		if (empty($username) || empty($password)) {
			$result = new ecjia_error('login_error', __('您输入的帐号信息不正确。'));
			return $result;
		}

		$db_user = RC_DB::table('staff_user')->where('mobile', $mobile)->first();RC_Model::model('user/admin_user_model');
		/* 收银台请求判断处理*/
		if (!empty($device) && is_array($device) && $device['code'] == '8001') {
			$adviser_info = RC_Model::model('achievement/adviser_model')->find(array('username' => $username));
			if (empty($adviser_info)) {
				$result = new ecjia_error('login_error', __('您输入的帐号信息不正确。'));
				return $result;
			}
			$admin_info = RC_DB::table('staff_user')->where('user_id', $adviser_info['admin_id'])->first();
			$username	= $admin_info['mobile'];
			$salt	    = $admin_info['salt'];
		} else {
			$salt = RC_DB::table('staff_user')->where('mobile', $username)->pluck('salt');
		}
		
	
		/* 检查密码是否正确 */
		$db_staff_user = RC_DB::table('staff_user')->selectRaw('user_id, mobile, name, store_id, nick_name, email, last_login, last_ip, action_list, avatar');
		if (!empty($salt)) {
// 			$row = $db_user->field('user_id, user_name, email, password, last_login, action_list, last_login, suppliers_id, salt, seller_id, role_id, ru_id')
// 						->find(array('user_name' => $username, 'password' => md5(md5($password).$salt)));
			
			$db_staff_user->where('mobile', $username)->where('password', md5(md5($password).$salt) );
		} else {
// 			$row = $db_user->field('user_id, user_name, email, password, last_login, action_list, last_login, suppliers_id, salt, seller_id, role_id, ru_id')
// 						->find(array('user_name' => $username, 'password' => md5($password)));
		    $db_staff_user->where('mobile', $username)->where('password', md5($password) );
		}
		$row = $db_staff_user->first();
		
		if ($row) {
			// 登录成功
			/* 设置session信息 */
			$_SESSION['admin_id']	= $row['user_id'];
			$_SESSION['admin_name']	= $row['mobile'];
			$_SESSION['action_list']	= $row['action_list'];
			$_SESSION['last_check_order']	= $row['last_login'];
			
			if (!empty($row['store_id'])) {
				$_SESSION['seller_id']	= $row['store_id'];
			}
			
			/* 获取device_id*/
			$device_id = RC_Model::model('mobile/mobile_device_model')->where(array('device_udid' => $device['udid'], 'device_client' => $device['client'], 'device_code' => $device['code']))->get_field('id');
			$_SESSION['device_id']	= $row['device_id'];

			
			if ($device['code'] == '8001') {
				$_SESSION['adviser_id']	= $row['user_id'];
				$_SESSION['seller_id']	= $row['store_id'];
				$_SESSION['admin_name']	= $row['mobile'];
			}
			
			if (empty($row['salt'])) {
				$salt = rand(1, 9999);
				$new_possword = md5(md5($this->requestData('password')) . $salt);
				$data = array(
						'salt'	=> $salt,
						'password'	=> $new_possword
				);
// 				$db_user->where(array('user_id' => $_SESSION['admin_id']))->update($data);
				RC_DB::table('staff_user')->where('user_id', $_SESSION['admin_id'])->update($data);
			}
		
			if ($row['action_list'] == 'all' && empty($row['last_login'])) {
				$_SESSION['shop_guide'] = true;
			}
		
			$data = array(
					'last_login' 	=> RC_Time::gmtime(),
					'last_ip'		=> RC_Ip::client_ip(),
			);
// 			$db_user->where(array('user_id' => $_SESSION['admin_id']))->update($data);
			RC_DB::table('staff_user')->where('user_id', $_SESSION['admin_id'])->update($data);
		
			$out = array(
					'session' => array(
						'sid' => RC_Session::session_id(),
						'uid' => $_SESSION['admin_id']
					),
			);
			$db_role = RC_Loader::load_model('role_model');
			$role_name = $db_role->where(array('role_id' => $row['role_id']))->get_field('role_name');
			
			$out['userinfo'] = array(
					'id' 			=> $row['user_id'],
					'username'		=> $row['mobile'],
					'email'			=> $row['email'],
					'last_login' 	=> RC_Time::local_date(ecjia::config('time_format'), $row['last_login']),
					'last_ip'		=> RC_Ip::area($row['last_ip']),
					'role_name'		=> !empty($role_name) ? $role_name : '',
					'avator_img'	=> RC_Upload::upload_url($row['avatar']),
			);
			
			if ($device['code'] == '8001') {
				$out['userinfo']['username'] = $adviser_info['username'];
				$out['userinfo']['email']	 = $adviser_info['email'];
			}
			
			//修正关联设备号
			$result = ecjia_app::validate_application('mobile');
			if (!is_ecjia_error($result)) {
				$device = $this->requestData('device', array());
				if (!empty($device['udid']) && !empty($device['client']) && !empty($device['code'])) {
					$db_mobile_device = RC_Model::model('mobile/mobile_device_model');
					$device_data = array(
							'device_udid'	=> $device['udid'],
							'device_client'	=> $device['client'],
							'device_code'	=> $device['code']
					);
					$db_mobile_device->where($device_data)->update(array('user_id' => $_SESSION['admin_id'], 'is_admin' => 1));
				}
			}
			
			return $out;
		} else {
			$result = new ecjia_error('login_error', __('您输入的帐号信息不正确。'));
			return $result;
		}
	    
	}
}


// end