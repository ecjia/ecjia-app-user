<?php
defined('IN_ECJIA') or exit('No permission resources.');

class signup_module extends api_front implements api_interface {
    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {	
    	
    	$this->authSession();	
		if (ecjia::config('shop_reg_closed')) {
			return new ecjia_error(11, '用户名或email已使用');
		}
		
		RC_Loader::load_app_class('integrate', 'user', false);
		$username = $this->requestData('name');
		$password = $this->requestData('password');
		$email = $this->requestData('email');
		$fileld = $this->requestData('field', array());//post的json格式：{"0":{"value":"15247258752","id":5}}
		$device = $this->requestData('device', array());
		$device_client = $device['client'];
		$mobile = $this->requestData('mobile');
		
		$other = array();
		$filelds = array();
		
		foreach ($fileld as $val) {
			$filelds[$val['id']] = $val['value'];
		}
		$other['msn'] = isset($filelds[1]) ? $filelds[1] : '';
		$other['qq'] = isset($filelds[2]) ? $filelds[2] : '';
		$other['office_phone'] = isset($filelds[3]) ? $filelds[3] : '';
		$other['home_phone'] = isset($filelds[4]) ? $filelds[4] : '';
		$other['mobile_phone'] = isset($filelds[5]) ? $filelds[5] : '';
		
		/* 随机生成6位随机数 + 请求客户端类型作为用户名*/
		$code = '';
		$charset 		= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$charset_len = strlen($charset)-1;
		for ($i = 0; $i < 6; $i++) {
			$code .= $charset[rand(1, $charset_len)];
		}
		/* 判断是否为手机*/
		if (is_numeric($username) && strlen($username) == 11 && preg_match( '/^1[3|4|5|7|8][0-9]\d{8}$/', $username)) {
			/* 设置用户手机号*/
			$other['mobile_phone'] = $username;
			
			$username = $device_client.'_'.$code;
			$user = integrate::init_users();
			if ($user->check_user($username)) {
				$username = $username. rand(0,9);
			}
		}
		
		if (empty($email)) {
			$email = $device_client.'_'.$code.'@mobile.com';
		}
		
		
		$other['mobile_phone'] = empty($mobile) ? $other['mobile_phone'] : $mobile;
		if (is_numeric($other['mobile_phone']) && strlen($other['mobile_phone']) == 11 && preg_match( '/^1[3|4|5|7|8][0-9]\d{8}$/', $other['mobile_phone'])) {
			$db_user = RC_Model::model('user/users_model');
			$mobile_count = $db_user->where(array('mobile_phone' => $other['mobile_phone']))->count();
			if ($mobile_count > 0 ) {
				return new ecjia_error(11, '用户名或email已使用');
			}
		} else {
			$other['mobile_phone'] = '';
		}
		
		if (register($username, $password, $email, $other) === false) {
			return new ecjia_error(11, '用户名或email已使用');
		} else {
			$db = RC_Model::model('user/reg_extend_info_model');
			$db_reg_fields = RC_Model::model('user/reg_fields_model');
			
			/*把新注册用户的扩展信息插入数据库*/
	        $fields_arr = $db_reg_fields->field('id')->where(array('type' => 0 , 'display' => 1))->order(array('dis_order' => 'asc' ,'id' => 'asc'))->select();
	        
			$extend_field_str = '';    //生成扩展字段的内容字符串
			foreach ($fields_arr AS $val) {
				$extend_field_index = $val['id'];
				if(!empty($filelds[$extend_field_index])) {
					$temp_field_content = strlen($filelds[$extend_field_index]) > 100 ? mb_substr($filelds[$extend_field_index], 0, 99) : $filelds[$extend_field_index];
	 				$extend_field_str .= " ('" . $_SESSION['user_id'] . "', '" . $val['id'] . "', '" . $temp_field_content . "'),";
				}
			}
			
	 		$extend_field_str = substr($extend_field_str, 0, -1);
			//插入注册扩展数据
	 		if ($extend_field_str) {
				$data = array(
					    'user_id'      => $_SESSION['user_id'],
					    'reg_field_id' => $val['id'],
					    'content'      => $temp_field_content
				);
				$db->insert($data);  
 			}
			RC_Loader::load_app_func('user', 'user');
			$user_info = EM_user_info($_SESSION['user_id']);
			
			$out = array(
					'session' => array(
					    'sid' => RC_Session::session_id(),
					    'uid' => $_SESSION['user_id']
					),
			
					'user' => $user_info
			);
			
			//修正咨询信息
			if($_SESSION['user_id'] > 0) {
				$device = $this->requestData('device', array());
				$device_id = $device['udid'];
				$device_client = $device['client'];
				$db_term_relation = RC_Loader::load_model('term_relationship_model');
			
				$object_id = $db_term_relation->where(array(
						'object_type'	=> 'ecjia.feedback',
						'object_group'	=> 'feedback',
						'item_key2'		=> 'device_udid',
						'item_value2'	=> $device_id ))
						->get_field('object_id', true);
				//更新未登录用户的咨询
				$db_term_relation->where(array('item_key2' => 'device_udid', 'item_value2' => $device_id))->update(array('item_key2' => '', 'item_value2' => ''));
			
				if(!empty($object_id)) {
					$db = RC_Model::model('feedback/feedback_model');
					$db->where(array('msg_id' => $object_id, 'msg_area' => '4'))->update(array('user_id' => $_SESSION['user_id'], 'user_name' => $_SESSION['user_name']));
					$db->where(array('parent_id' => $object_id, 'msg_area' => '4'))->update(array('user_id' => $_SESSION['user_id'], 'user_name' => $_SESSION['user_name']));
				}
				
				//修正关联设备号
				$result = ecjia_app::validate_application('mobile');
				if (!is_ecjia_error($result)) {
					if (!empty($device['udid']) && !empty($device['client']) && !empty($device['code'])) {
						$db_mobile_device = RC_Model::model('mobile/mobile_device_model');
						$device_data = array(
								'device_udid'	=> $device['udid'],
								'device_client'	=> $device['client'],
								'device_code'	=> $device['code']
						);
						$db_mobile_device->where($device_data)->update(array('user_id' => $_SESSION['user_id']));
					}
				}
			}
			
			return $out;
		}
//		$api->init_session();
//		$api->init_users();
// 		require(EC_PATH . '/includes/init.php');
		
// 		include_once(EC_PATH . '/includes/lib_order.php');
// 		include_once(EC_PATH . '/includes/lib_passport.php');
//      RC_Loader::load_sys_func('order');
//      RC_Loader::load_sys_func('passport');
// 		_dump(RC_Uri::admin_path(),1);die;
//      RC_Loader::load_app_func('passport' ,'user');
		/*把新注册用户的扩展信息插入数据库*/
// 		$sql = 'SELECT id FROM ' . $GLOBALS['ecs']->table('reg_fields') . ' WHERE type = 0 AND display = 1 ORDER BY dis_order, id';   //读出所有自定义扩展字段的id
// 		$fields_arr = $GLOBALS['db']->getAll($sql);

//插入注册扩展数据
//	 			$sql = 'INSERT INTO '. $ecs->table('reg_extend_info') . ' (`user_id`, `reg_field_id`, `content`) VALUES' . $extend_field_str;
//	 			$GLOBALS['db']->query($sql);
	}
}

/**
 * 用户注册，登录函数
 *
 * @access public
 * @param string $username
 *            注册用户名
 * @param string $password
 *            用户密码
 * @param string $email
 *            注册email
 * @param array $other
 *            注册的其他信息
 *            
 * @return bool $bool
 */
function register($username, $password, $email, $other = array())
{
    $db_user = RC_Model::model('user/users_model');

    /* 检查注册是否关闭 */
    if (ecjia::config('shop_reg_closed', ecjia::CONFIG_EXISTS)) {
    	return new ecjia_error(99999, '该网店暂停注册');
    }
    /* 检查username */
    if (empty($username)) {
        return new ecjia_error(200, '用户名不能为空');
    } else {
        if (preg_match('/\'\/^\\s*$|^c:\\\\con\\\\con$|[%,\\*\\"\\s\\t\\<\\>\\&\'\\\\]/', $username)) {
			return new ecjia_error(201, '用户名含有敏感字符');
        }
    }
    
    /* 检查email */
    if (empty($email)) {
		return new ecjia_error(203, 'email不能为空');
    } else {
        if (!is_email($email)) {
        	return new ecjia_error(204, '不是合法的email地址');
        }
    }
    
    if (admin_registered($username)) {
		return new ecjia_error(202, '用户名 已经存在');
    }

    RC_Loader::load_app_class('integrate', 'user', false);
    $user = &integrate::init_users();
    if (!$user->add_user($username, $password, $email)) {
//        if ($user->error == ERR_INVALID_USERNAME) {
//            ecjia::$error->add(sprintf($GLOBALS['_LANG']['username_invalid'], $username));
//        } elseif ($GLOBALS['user']->error == ERR_USERNAME_NOT_ALLOW) {
//            ecjia::$error->add(sprintf(RC_Lang::lang('username_not_allow'), $username));
//        } elseif ($GLOBALS['user']->error == ERR_USERNAME_EXISTS) {
//            ecjia::$error->add(sprintf(RC_Lang::lang('username_exist'), $username));
//        } elseif ($GLOBALS['user']->error == ERR_INVALID_EMAIL) {
//            ecjia::$error->add(sprintf(RC_Lang::lang('email_invalid'), $email));
//        } elseif ($GLOBALS['user']->error == ERR_EMAIL_NOT_ALLOW) {
//            ecjia::$error->add(sprintf(RC_Lang::lang('email_not_allow'), $email));
//        } elseif ($GLOBALS['user']->error == ERR_EMAIL_EXISTS) {
//            ecjia::$error->add(sprintf(RC_Lang::lang('email_exist'), $email));
//        } else {
//            ecjia::$error->add('UNKNOWN ERROR!');
//        }
        
        // 注册失败
        return false;
    } else {
        // 注册成功
        /* 设置成登录状态 */
        $user->set_session($username);
        $user->set_cookie($username);  
        /* 注册送积分 */
        if (ecjia::config('register_points' , ecjia::CONFIG_EXISTS)) {
        	$options = array(
        			'user_id'		=> $_SESSION['user_id'],
        			'rank_points'	=> ecjia::config('register_points'),
        			'pay_points'	=> ecjia::config('register_points'),
        			'change_desc'	=> RC_Lang::lang('register_points')
        	);
        	$result = RC_Api::api('user', 'account_change_log',$options);
        }
        
        /* 推荐处理 */
        $affiliate = unserialize(ecjia::config('affiliate'));
        if (isset($affiliate['on']) && $affiliate['on'] == 1) {
            // 推荐开关开启
            $up_uid = get_affiliate();
            empty($affiliate) && $affiliate = array();
            $affiliate['config']['level_register_all'] = intval($affiliate['config']['level_register_all']);
            $affiliate['config']['level_register_up'] = intval($affiliate['config']['level_register_up']);
            if ($up_uid) {
                if (!empty($affiliate['config']['level_register_all'])) {
                    if (!empty($affiliate['config']['level_register_up'])) {
                        $rank_points = $db_user->field('rank_points')->find("user_id = '$up_uid'");
                        $rank_points = $rank_points['rank_points'];
                        if ($rank_points + $affiliate['config']['level_register_all'] <= $affiliate['config']['level_register_up']) {
                        	$options = array(
                        			'user_id'		=> $up_uid,
                        			'rank_points'	=> $affiliate['config']['level_register_all'],
                        			'change_desc'	=> sprintf(RC_Lang::lang('register_affiliate'), $_SESSION['user_id'], $username)
                        	);
                        	$result = RC_Api::api('user', 'account_change_log', $options);
                        }
                    } else {
                    	$options = array(
                    			'user_id'		=> $up_uid,
                    			'rank_points'	=> $affiliate['config']['level_register_all'],
                    			'change_desc'	=> RC_Lang::lang('register_affiliate')
                    	);
                    	$result = RC_Api::api('user', 'account_change_log', $options);
                    }
                }
                
                // 设置推荐人
                $data = array(
                    'parent_id' => $up_uid
                );
                $db_user->where(array('user_id' => $_SESSION['user_id']))->update($data);
            }
        }
        
        // 定义other合法的变量数组
        $other_key_array = array(
            'msn',
            'qq',
            'office_phone',
            'home_phone',
            'mobile_phone'
        );
        $update_data['reg_time'] = RC_Time::local_strtotime(RC_Time::local_date('Y-m-d H:i:s'));
        if ($other) {
            foreach ($other as $key => $val) {
                // 删除非法key值
                if (!in_array($key, $other_key_array)) {
                    unset($other[$key]);
                } else {
                    $other[$key] = htmlspecialchars(trim($val)); // 防止用户输入javascript代码
                }
            }
            $update_data = array_merge($update_data, $other);
        }

        $db_user->where(array('user_id' => $_SESSION['user_id']))->update($update_data);
        
        RC_Loader::load_app_func('user', 'user');
        update_user_info(); // 更新用户信息
        RC_Loader::load_app_func('cart','cart');
        recalculate_price(); // 重新计算购物车中的商品价格
        
        return true; 
        //             log_account_change($_SESSION['user_id'], 0, 0, ecjia::config('register_points'), ecjia::config('register_points'), RC_Lang::lang('register_points'));
        //             log_account_change($up_uid, 0, 0, $affiliate['config']['level_register_all'], 0, sprintf(RC_Lang::lang('register_affiliate'), $_SESSION['user_id'], $username));
        //             log_account_change($up_uid, 0, 0, $affiliate['config']['level_register_all'], 0, RC_Lang::lang('register_affiliate'));
        
    }
}


/**
 * 判断超级管理员用户名是否存在
 * 
 * @param string $adminname
 *            超级管理员用户名
 * @return boolean
 */
function admin_registered ($adminname) {
    $db = RC_Loader::load_model('admin_user_model');
    $res = $db->where(array('user_name' => $adminname))->count();
    return $res;
}


/**
 * 获取推荐uid
 *
 * @access  public
 * @param   void
 *
 * @return int
 **/
function get_affiliate() {
	$db = RC_Model::model('user/users_model');
    if (!empty($_COOKIE['ECJIA[affiliate_uid]'])) {
        $uid = intval($_COOKIE['ECJIA[affiliate_uid]']);
        $user_id = $db->where(array('user_id' => $uid))->get_field('user_id');
        if ($user_id) {
            return $uid;
        } else {
            setcookie('ECJIA[affiliate_uid]', '', 1);
        }
    }
    return 0;
}

// end