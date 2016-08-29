<?php
defined('IN_ECJIA') or exit('No permission resources.');

class signin_module implements ecjia_interface {
	
	public function run(ecjia_api & $api) {
    
	    RC_Loader::load_app_class('integrate', 'user', false);

	    $user = integrate::init_users();
		RC_Loader::load_app_func('user','user');
		RC_Loader::load_app_func('cart','cart');
		
		$name = _POST('name');
		$password = _POST('password');
		
		$is_mobile = false;

		/* 判断是否为手机号*/
		if (is_numeric($name) && strlen($name) == 11 && preg_match( '/^1[3|4|5|7|8][0-9]\d{8}$/', $name)) {
			$db_user = RC_Loader::load_app_model('users_model', 'user');
			$user_count = $db_user->where(array('mobile_phone' => $name))->count();
			if ($user_count > 1) {
				return new ecjia_error('user_repeat', '用户重复，请与管理员联系！');
			}
			$check_user = $db_user->where(array('mobile_phone' => $name))->get_field('user_name');
			/* 获取用户名进行判断验证*/
			if (!empty($check_user)) {
				if ($user->login($check_user, $password)) {
					$is_mobile = true;
				}
			}
		}
		
		/* 如果不是手机号码*/
		if (!$is_mobile) {
			if (!$user->login($name, $password)) {
				EM_Api::outPut(6);
			}
		}
		
		$user_info = EM_user_info($_SESSION['user_id']);
		$out = array(
				'session' => array(
						'sid' => RC_Session::session_id(),
						'uid' => $_SESSION['user_id']
				),
		
				'user' => $user_info
		);
		
		update_user_info();
		recalculate_price();
		
		//修正咨询信息
		if($_SESSION['user_id'] > 0) {
			$device = _POST('device', array());
			$device_id = $device['udid'];
			$device_client = $device['client'];
			$db_term_relation = RC_Loader::load_model('term_relationship_model');
				
			$object_id = $db_term_relation->where(array(
					'object_type'	=> 'ecjia.feedback',
					'object_group'	=> 'feedback',
					'item_key2'		=> 'device_udid',
					'item_value2'	=> $device_id ))
					->get_field('object_id', true);
			//更新未登录用户的咨询
			$db_term_relation->where(array('item_key2' => 'device_udid', 'item_value2' => $device_id))->update(array('item_key2' => '', 'item_value2' => ''));
				
			if(!empty($object_id)) {
				$db = RC_Loader::load_app_model('feedback_model', 'feedback');
				$db->where(array('msg_id' => $object_id, 'msg_area' => '4'))->update(array('user_id' => $_SESSION['user_id'], 'user_name' => $_SESSION['user_name']));
				$db->where(array('parent_id' => $object_id, 'msg_area' => '4'))->update(array('user_id' => $_SESSION['user_id'], 'user_name' => $_SESSION['user_name']));
			}
			
			//修正关联设备号
			$result = ecjia_app::validate_application('mobile');
			if (!is_ecjia_error($result)) {
				if (!empty($device['udid']) && !empty($device['client']) && !empty($device['code'])) {
					$db_mobile_device = RC_Loader::load_app_model('mobile_device_model', 'mobile');
					$device_data = array(
							'device_udid'	=> $device['udid'],
							'device_client'	=> $device['client'],
							'device_code'	=> $device['code']
					);
					$db_mobile_device->where($device_data)->update(array('user_id' => $_SESSION['user_id']));
				}
			}
		}
		
		return $out;
	}
}


// end