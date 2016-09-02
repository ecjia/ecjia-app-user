<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 第三方登录
 * @author will.chen
 *
 */
class snsbind_module extends api_front implements api_interface {
    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {
    		
    	$this->authSession();	
		$name = $this->requestData('name');
		$open_id = $this->requestData('id');
		$type = $this->requestData('type');
		
		$login_type = array('weixin', 'qq');
		
		if (!in_array($type, $login_type) || empty($open_id) || empty($name)) {
			EM_Api::outPut(101);
		}
		$db = RC_Loader::load_app_model('users_model', 'user');
		$result = check_user($open_id, $type);
		
		$info_user_id = $type .'_'.$open_id; //  加个标识！！！防止 其他的标识 一样  // 以后的ID 标识 将以这种形式 辨认
		$name = str_replace("'" , "" , $name); 
		
		
		RC_Loader::load_app_class('integrate', 'user', false);
		$user = integrate::init_users();
		// 没有当前数据
		if(empty($result)) {
			$infoname = $name;
			// 重名处理
			if($user->check_user($name)) {
				$num = rand(10000, 9999);
				$infoname = $name."<span style=display:none;>$type$num</span>";
			}
			$email = substr($open_id, 0, 10).rand(10, 99);
			$data = array(
					'email' 		=> $email.'@'.$type.'.com',
					'user_name' 	=> $infoname,
					'aite_id'		=> $info_user_id,
					'password' 		=> md5(RC_Time::gmtime()),
					'reg_time' 		=> RC_Time::gmtime(),
					'last_ip'		=> RC_Ip::client_ip(),
			);
			$result = $db->insert($data);
			if ($result) {
				$out = action_login($data['user_name'], $info_user_id);
				if ($out) {
					return $out;
				} else {
					EM_Api::outPut(6);
				}
			} else {
				EM_Api::outPut(8);
			}
		} else {
			if($result['aite_id'] == $open_id) {
				$data = array(
						'aite_id' => $info_user_id,
				);
				$db->where(array('user_id' => $result['user_id']))->update($data);
			}
			$out = action_login($result['user_name'], $info_user_id);
			if ($out) {
				$update_name = preg_replace('/<span(.*)span>/i', '', $result['user_name']);
				if ($update_name != $name) {
					if($user->check_user($name)) {
						$num = rand(10000,9999);
						$infoname = $name."<span style=display:none;>$type$num</span>";
					
					}
					$data = array(
							'user_name' => $name."<span style=display:none;>$type$num</span>",
					);
					$db->where(array('user_id' => $result['user_id']))->update($data);
				}
				return $out;
			} else {
				EM_Api::outPut(6);
			}
			
		}
	}
}

/*检测用户名是否存在*/
function check_user($openid, $type){
	$db = RC_Loader::load_app_model('users_model', 'user');
	
	$info_user_id = $type .'_'.$openid; //  加个标识！！！防止 其他的标识 一样  // 以后的ID 标识 将以这种形式 辨认
	$row = $db->field('user_name,password,aite_id')->find(array('aite_id' => $info_user_id));
	
	return $row;
}

function action_login($user_name, $open_id){
	RC_Loader::load_app_class('integrate', 'user', false);
	$user = integrate::init_users();
	RC_Loader::load_app_func('user', 'user');
	RC_Loader::load_app_func('cart', 'cart');
	
	$db = RC_Loader::load_app_model('users_model', 'user');
	$row = $db->find(array('aite_id' => $open_id));
	if (empty($row)) {
		return false;
	}
	$user->set_session($row['user_name']);
	$user->set_cookie($row['user_name']);
	$user_info = EM_user_info($_SESSION['user_id']);
	
	$user_info['name'] = _POST('name');//仿制昵称会改变。。
	$out = array(
			'session' => array(
					'sid' => RC_Session::session_id(),
					'uid' => $_SESSION['user_id']
			),
			'user' => $user_info
	);
	define('SESS_ID', RC_Session::session_id());
	update_user_info();
	recalculate_price();
	return $out;
}
// end