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
	
		$address = _POST('address', array());
		
		$address['user_id']       = $_SESSION['user_id'];
		$address['consignee']     = isset($address['consignee']) ? trim($address['consignee']) : '';
		$address['country']       = isset($address['country']) ? intval($address['country']) : '';
		$address['province']      = isset($address['province']) ? intval($address['province']) : '';
		$address['city']      	  = isset($address['city']) ? intval($address['city']) : '';
		$address['district']      = isset($address['district']) ? intval($address['district']) : '';
		$address['email']         = !empty($address['email']) ? trim($address['email']) : '';
		$address['mobile']        = isset($address['mobile']) ? trim($address['mobile']) : '';
		$address['address']       = isset($address['address']) ? trim($address['address']) : '';
		$address['best_time']     = isset($address['best_time']) ? trim($address['best_time']) : '';
		$address['default']       = (isset($address['set_default']) && $address['set_default'] == 'true') ? 1 : 0;
		$address['sign_building'] = isset($address['sign_building']) ? trim($address['sign_building']) : '';
		$address['tel'] 		  = isset($address['tel']) ? trim($address['tel']) : '';
		
		$result = RC_Api::api('user', 'address_manage', $address);
	
		if (is_ecjia_error($result)) {
			return $result;
		}
		return array();
	}
	
	
}

// end