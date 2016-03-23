<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 更新单条收货地址信息
 * @author royalwang
 *
 */
class update_module implements ecjia_interface {
	
	public function run(ecjia_api & $api) {
		
		EM_Api::authSession();
		
		RC_Loader::load_app_func('user', 'user');
		
 		$db_user_address = RC_Loader::load_app_model('user_address_model', 'user');
		
		$address = _POST('address', array());
		$address['address_id']     = _POST('address_id', 0);
		$address['consignee']      = $address['consignee'];
		$address['sign_building']  = $address['sign_building'];
		$address['default']       = $address['set_default'] == 'true' ? 1 : 0;
		
		if (empty($address['address_id'])) { 
			EM_Api::outPut(101);
		}
		/* 获取用户地址 */
		$user_address = $db_user_address->where(array('address_id' => $address['address_id'], 'user_id' => $_SESSION['user_id']))->get_field('address_id');
		
		if ($address['address_id'] != $user_address) {
		    EM_Api::outPut(13);
		}
		
		$address['user_id'] = $_SESSION['user_id'];
		
		if(!check_address_info($address)){
			EM_Api::outPut(101);
		}
		update_address($address);
		return array();
	}
}

function check_address_info($address){
	if (
		!empty($address['consignee']) &&
		!empty($address['country']) &&
// 		!empty($address['email']) &&
		!empty($address['tel'])
	) {
		return true;
	}
}


// end