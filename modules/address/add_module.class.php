<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 添加收货地址
 * @author royalwang
 *
 */
class add_module implements ecjia_interface {
	
	public function run(ecjia_api & $api) {
		
		EM_Api::authSession();
		
		RC_Loader::load_app_func('user', 'user');
		
		$db_user_address = RC_Loader::load_app_model('user_address_model', 'user');
		$count = $db_user_address->where(array('user_id' => $_SESSION['user_id']))->count();
		
		$address = _POST('address', array());
		
		$address['address_id']    = $address['id'];
		$address['consignee']     = $address['consignee'];
		$address['sign_building'] = $address['sign_building'];
		$address['user_id']       = $_SESSION['user_id'];
		$address['default']       = $count == 0 ? 1 : 0;
		
		unset($address['id']);
		
		if (!check_address_info($address)) {
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