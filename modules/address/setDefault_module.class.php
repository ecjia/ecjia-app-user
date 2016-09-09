<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 设置默认收货地址
 * @author royalwang
 *
 */
class setDefault_module extends api_front implements api_interface {
    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {	
    	
    	$this->authSession();	
		$address_id = $this->requestData('address_id', 0);
		if (empty($address_id)) {
			return new ecjia_error(101, '参数错误');
		}
		
		$db_user_address = RC_Loader::load_app_model('user_address_model','user');
		$db_users = RC_Loader::load_app_model('users_model', 'user');
		
		$arr = $db_user_address->find(array('address_id' => $address_id, 'user_id' => $_SESSION['user_id']));
		if (empty($arr)) {
			return new ecjia_error(8, 'fail');
		}
		
		/* 保存到session */
// 		$_SESSION['flow_consignee'] = rc_addslashes($arr);
		$db_users->where(array('user_id' => $_SESSION['user_id']))->update(array('address_id' => $address_id));
		return array();
	}
}


// end