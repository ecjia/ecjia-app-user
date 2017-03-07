<?php
defined('IN_ECJIA') or exit('No permission resources.');

class mobile_reward extends ecjia_front {

	public function __construct() {	
		parent::__construct();	
		
  		/* js与css加载路径*/
  		$this->assign('front_url', RC_App::apps_url('statics/front', __FILE__));
  		$this->assign('title', '新人有礼');
	}
	
	public function init() {
		$token = isset($_GET['token']) ? trim($_GET['token']) : '';
		
		$mobile_signup_reward_notice = ecjia::config('mobile_signup_reward_notice');
		
		$mobile_signup_reward_notice = nl2br($mobile_signup_reward_notice);

		$this->assign('mobile_signup_reward_notice', $mobile_signup_reward_notice);
		$this->assign('token', $token);
		
		
		$this->display('reward.dwt');
	}
	
	public function recieve() {
		$token = isset($_POST['token']) ? trim($_POST['token']) : '';
		if ( RC_Session::session_id() != $token) {
			RC_Session::destroy();
			RC_Session::init(null, $token);
		}
		if (!isset($_SESSION['user_id']) || !$_SESSION['user_id']) {
			ecjia_front::$controller->showmessage('您还未登录，请先登录！', ecjia::MSGSTAT_ERROR | ecjia::MSGTYPE_JSON);
		}
		
		/* 新人有理的红包id*/
		$bonus_id = ecjia::config('mobile_signup_reward');
		if (!$bonus_id) {
			ecjia_front::$controller->showmessage('活动未开始！', ecjia::MSGSTAT_ERROR | ecjia::MSGTYPE_JSON);
		}
		
		$user_bonus = RC_Model::model('bonus/user_bonus_model')->where(array('user_id' => $_SESSION['user_id'], 'bonus_type_id' => $bonus_id))->find();
		if (!empty($user_bonus)) {
			ecjia_front::$controller->showmessage('你已领取过！', ecjia::MSGSTAT_ERROR | ecjia::MSGTYPE_JSON);
		}
		
// 		$user_info = RC_Model::model('user/users_model')->where(array('user_id' => $_SESSION['user_id']))->find();
// 		$reg_time = $user_info['reg_time']+2592000;//默认30天时间
// 		if (empty($user_info['reg_time']) || $reg_time < RC_Time::gmtime()) {
// 			ecjia_front::$controller->showmessage('您已过了领取时间！', ecjia::MSGSTAT_ERROR | ecjia::MSGTYPE_JSON);
// 		}
		
		RC_Model::model('bonus/user_bonus_model')->insert(array(
						'user_id'		=> $_SESSION['user_id'], 
						'bonus_type_id' => $bonus_id,
		));
		
		ecjia_front::$controller->showmessage('发放成功！', ecjia::MSGSTAT_SUCCESS | ecjia::MSGTYPE_JSON, array('url' => 'ecjiaopen://app?open_type=main'));
		
	}
	
	
	
}

// end