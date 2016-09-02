<?php
defined('IN_ECJIA') or exit('No permission resources.');

/**
 * 管理员登录
 * @author will
 *
 */
class signout_module extends api_admin implements api_interface {
    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {
    		
		$this->authadminSession();
		RC_Cookie::delete('ECJAP[admin_id]');
		RC_Cookie::delete('ECJAP[admin_pass]');
		
		RC_Session::destroy();
		
		return array();
	}
}


// end