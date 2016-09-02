<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 密码找回校验验证码
 * @author will.chen
 *
 */
class forget_password_module extends api_front implements api_interface {
    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {	
    	$this->authSession();	
		
		$type = _POST('type');
		$value = _POST('value');
		$code = _POST('code');
		
		if (empty($type) || empty($value) || empty($code)) {
        	EM_Api::outPut(101);
        }
        $db = RC_Loader::load_app_model('users_model', 'user');
        if ($type == 'mobile') {
        	$user_count = $db->where(array('mobile_phone' => $value))->count();
        	//如果用户数量大于1
        	if ($user_count > 1) {
        		return new ecjia_error('mobile_repeat_error', __('手机号重复，请与管理员联系！'));
        	}
        	$userinfo = $db->find(array('mobile_phone' => $value));
        }
        if ($type == 'email') {
        	$userinfo = $db->find(array('email' => $value));
        }
        
        if (empty($userinfo)) {
        	return new ecjia_error('user_error', __('用户信息错误！'));
        }
        
        /* 判断code是否正确*/
        if ($code != $_SESSION['forget_code']) {
        	return new ecjia_error('code_error', __('验证码错误！'));
        }
        
        /* 判断code有效期*/
        $time = RC_Time::gmtime();
        if ($_SESSION['forget_expiry'] < $time) {
        	return new ecjia_error('code_expiry_error', __('验证码过期，请重新获取验证码！'));
        }
        
        RC_Session::set('forget_code_validated', 1);
        return array();
	}
}

// end