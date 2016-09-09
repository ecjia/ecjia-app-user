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
 			return new ecjia_error(101, '参数错误');
 		}
 		RC_Loader::load_app_func('user', 'user');
 		$result = del_user_account($id, $user_id);
 		if ($result) {
 			return array();
 		} else {
 			return new ecjia_error(8, 'fail');
 		}
	}
}

// end