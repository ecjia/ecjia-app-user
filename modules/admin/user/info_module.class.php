<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 用户信息
 * @author royalwang
 *
 */
class info_module extends api_admin implements api_interface {
    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {
		$this->authadminSession();
		
		if ($_SESSION['admin_id'] <= 0 && $_SESSION['staff_id'] <= 0) {
		    return new ecjia_error(100, 'Invalid session');
		}
		
		$user_id = $this->requestData('user_id', 0);
		$mobile	 = $this->requestData('mobile');
		if (empty($user_id) && empty($mobile)) {
			return new ecjia_error(101, '错误的参数提交');
		}
		
		$user_info = RC_Api::api('user', 'user_info', array('user_id' => $user_id, 'mobile' => $mobile));
		
		if (is_ecjia_error($user_info)) {
			return $user_info;
		}
		
		/* 获取可使用的红包数量*/
		$bonus_count = RC_Model::model('bonus/user_bonus_type_viewmodel')->join('bonus_type')->where(array('ub.user_id' => $user_id, 'use_start_date' => array('lt' => RC_Time::gmtime()), 'use_end_date' => array('gt' => RC_Time::gmtime()), 'ub.order_id' => 0))->count("*");
		
		return array(
			'id'			=> $user_info['user_id'],
			'name'			=> $user_info['user_name'],
			'rank_name' 	=> $user_info['user_rank_name'],
			'email'			=> $user_info['email'],
			'mobile_phone'	=> $user_info['mobile_phone'],
			'user_money'	=> $user_info['user_money'],
			'formatted_user_money'	=> $user_info['formated_user_money'],
			'user_points'			=> $user_info['pay_points'],
			'user_bonus_count'		=> $bonus_count,
			'reg_time'		=> RC_Time::local_date(ecjia::config('time_format'), $user_info['reg_time']),
			'last_login'	=> RC_Time::local_date(ecjia::config('time_format'), $user_info['last_login']),
			'address'		=> $user_info['address'],
			'avatar_img'	=> $user_info['avatar_img'],
		);
	}
}

// end