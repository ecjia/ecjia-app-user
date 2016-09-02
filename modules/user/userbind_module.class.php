<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 用户注册绑定请求
 * @author will.chen
 *
 */
class userbind_module extends api_front implements api_interface {
    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {	
    	$this->authSession();	
    	
		$type = $this->requestData('type');
		$value = $this->requestData('value');
		
		$type_array = array('mobile');
		//判断值是否为空，且type是否是在此类型中
		if ( empty($type) || empty($value) || !in_array($type, $type_array)) {
			EM_Api::outPut(101);
		}
		
		$db_user = RC_Loader::load_app_model('users_model', 'user');
		//设置session用于校验校验码
		$code = rand(100000, 999999);
		RC_Loader::load_app_class('integrate', 'user', false);
		$user = integrate::init_users();
		if ($user->check_user($value)) {
			return array('registered' => 1);
		}
		$mobile_phone = $db_user->find(array('mobile_phone' => $value));
		if (!empty($mobile_phone)) {
			return array('registered' => 1);
		}
		
		if ($type == 'mobile') {
			//发送短信
			$tpl_name = 'sms_register_validate';
			$tpl   = RC_Api::api('sms', 'sms_template', $tpl_name);
			ecjia_api::$view_object->assign('code', $code);
			ecjia_api::$view_object->assign('mobile', $value);
			ecjia_api::$view_object->assign('shopname', ecjia::config('shop_name'));
			ecjia_api::$view_object->assign('service_phone', ecjia::config('service_phone'));
			$time = RC_Time::gmtime();
			ecjia_api::$view_object->assign('time', RC_Time::local_date(ecjia::config('date_format'), $time));
			
			$content = ecjia_api::$controller->fetch_string($tpl['template_content']);
			$options = array(
					'mobile' 		=> $value,
					'msg'			=> $content,
					'template_id' 	=> $tpl['template_id'],
			);
			
			$response = RC_Api::api('sms', 'sms_send', $options);
			
			if ($response === true) {
				$_SESSION['bind_code'] = $code;
				$_SESSION['bindcode_lifetime'] = RC_Time::gmtime();
				$_SESSION['bind_value'] = $value;
				$_SESSION['bind_type'] = $type;
				return array('registered' => 0);
			} else {
				$result = new ecjia_error('sms_error', __('短信发送失败！'));//$response['description']
				EM_Api::outPut($result);
			}
		}
		
		
	}
}


// end