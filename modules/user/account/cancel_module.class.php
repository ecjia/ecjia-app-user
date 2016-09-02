<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 取消申请
 * @author royalwang
 *
 */
class cancel_module extends api_front implements api_interface {
    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {	
    	$this->authSession();	
 		
 		$id = $this->requestData('account_id' , 0);
 		$user_id = $_SESSION['user_id'];
 		
 		if ($id <= 0 || $user_id == 0) {
 			EM_Api::outPut(101);
 		}
 		RC_Loader::load_app_func('user', 'user');
 		$result = del_user_account($id, $user_id);
 		if ($result) {
 			EM_Api::outPut(array());
 		} else {
 			EM_Api::outPut(8);
 		}
	}
}

// end