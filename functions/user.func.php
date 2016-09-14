<?php
/**
 * 用户方法集中营
 */

defined('IN_ECJIA') or exit('No permission resources.');
/**
 *返回用户列表数据
 *
 * @accesspublic
 * @param
 *
 * @return void
 */
function get_user_list($args = array()) {
	$db_user = RC_Model::model('user/users_model');
	
	$filter['keywords']		= empty($args['keywords'])		? ''		: trim($args['keywords']);
	$filter['rank']			= empty($args['rank'])			? 0			: intval($args['rank']);
	$filter['sort_by']		= empty($args['sort_by'])		? 'user_id' : trim($args['sort_by']);
	$filter['sort_order']	= empty($args['sort_order'])	? 'DESC'	: trim($args['sort_order']);
	$where = ' 1 ';
	if ($filter['keywords']) {
		$where .= " AND user_name LIKE '%" . mysql_like_quote($filter['keywords']) ."%' or email like '%".$filter['keywords'] ."%'";
	}
	if ($filter['rank']) {
		$where .= " AND user_rank = '$filter[rank]' ";
	}

	$count = $db_user->where($where)->count();
	if ($count != 0) {
		/* 实例化分页 */
		$page = new ecjia_page($count, 15, 6);
		/* 查询所有用户信息*/
		$data = $db_user->field('user_id, user_name, email, is_validated, user_money, frozen_money, rank_points, pay_points, reg_time')->where($where)->order(array($filter['sort_by'] => $filter['sort_order']))->limit($page->limit())->select();
		$user_list = array();
		foreach ($data as $rows) {
			$rows['reg_time']	= RC_Time::local_date(ecjia::config('time_format'), $rows['reg_time']);
			$user_list[]		= $rows;
		}
		return array('user_list' => $user_list, 'filter' => $filter, 'page' => $page->show(5), 'desc' => $page->page_desc());
	}
}


/**
 * 获取启用的支付方式下拉列表
 */
function get_payment($cod_fee = 0) {
    $payment_method = RC_Loader::load_app_class('payment_method', 'payment');
    return $payment_method->get_online_payment_list(false);
}

/**
 * 获取充值和提现申请列表
 * @param unknown $args
 */
function get_account_list($args = array()) {
	$dbview = RC_Model::model('user/user_account_user_viewmodel');
	$payment_method = RC_Loader::load_app_class('payment_method', 'payment');
	
	$filter['user_id']		= empty($args['user_id'])			? 0  : intval($args['user_id']);
	$filter['keywords']		= empty($args['keywords'])			? '' : trim($args['keywords']);
	$filter['process_type']	= isset($args['process_type'])		? intval($args['process_type']) : -1;
	$filter['payment']		= empty($args['payment'])			? '' : trim($args['payment']);
	$filter['is_paid']		= isset($args['is_paid'])			? intval($args['is_paid']) : -1;
	$filter['start_date']	= empty($args['start_date'])		? '' : $args['start_date'];
	$filter['end_date']		= empty($args['end_date'])			? '' : $args['end_date'];

	$filter['sort_by']		= empty($_REQUEST['sort_by'])		? 'add_time' : trim($_REQUEST['sort_by']);
	$filter['sort_order']	= empty($_REQUEST['sort_order'])	? 'DESC' : trim($_REQUEST['sort_order']);
	$db_user_account = RC_DB::table('user_account as ua')->leftJoin('users as u', RC_DB::raw('ua.user_id'), '=', RC_DB::raw('u.user_id'));
	$where = array();
	if ($filter['user_id'] > 0) {
		$where['ua.user_id'] = $filter['user_id'];
		$db_user_account->where(RC_DB::raw('ua.user_id'), $filter['user_id']);
	}
	if ($filter['process_type'] != -1) {
		$where['ua.process_type'] = $filter['process_type'];
		$db_user_account->where(RC_DB::raw('process_type'), $filter['process_type']);
	} 
	if ($filter['payment']) {
		$payment = $payment_method->payment_info_by_name($filter['payment']);
		$where['ua.payment'] = array();
		if(!empty($payment) && is_array($payment)) {
			foreach ($payment as $key => $value) {
				array_push($where['ua.payment'], $value['pay_name'], $value['pay_code']);
				$db_user_account->whereIn(RC_DB::raw('ua.payment'), array($value['pay_name'], $value['pay_code']));
			}
		}
	}
	if ($filter['is_paid'] != -1) {
		$where['ua.is_paid'] = $filter['is_paid'];
		$db_user_account->where(RC_DB::raw('is_paid'), $filter['is_paid']);
	}

	if ($filter['keywords']) {
		$where['u.user_name'] = array('like' => '%'.mysql_like_quote($filter['keywords']).'%');
		$db_user_account->whereIn(RC_DB::raw('u.user_name'), '%', '%'.mysql_like_quote($filter['keywords']).'%');
	}
	
	/*　时间过滤　*/
	$start_date = RC_Time::local_strtotime($args['start_date']);
	$end_date = RC_Time::local_strtotime($args['end_date']) + 86400;
	
	if (!empty($args['start_date']) && !empty($args['end_date'])) {
		$where['add_time'] = array('egt' => $start_date, 'elt' => $end_date);
		$db_user_account->where('add_time', '>=', $start_date)
			->where('add_time', '<=', $end_date);
	} else {
		if (!empty($args['start_date'])) {
			$where['add_time'] = array('egt' => $start_date);
			$db_user_account->where('add_time', '>=', $start_date);
		} elseif (!empty($args['end_date'])) {
			$where['add_time'] = array('elt' => $end_date);
			$db_user_account->where('add_time', '<=', $end_date);
		}
	}
	
//	$count = $dbview->join('users')->where($where)->count();
	$count = $db_user_account->count();

	/* 实例化分页 */
	$page = new ecjia_page($count, 15, 6);

	$list = array();
	if ($count != 0) {
		$payment_list = $payment_method->available_payment_list(false);
		$pay_name = array();
		if (!empty($payment_list) && is_array($payment_list)) {
			foreach ($payment_list as $key => $value) {
				$pay_name[$value['pay_code']] = $value['pay_name'];

			}
		}

//		$list = $dbview->join('users')->where($where)->order(array($filter['sort_by'] => $filter['sort_order']))->limit($page->limit())->select();
		$list = $db_user_account->orderBy($filter['sort_by'], $filter['sort_order'])->take(15)->skip($page->start_id-1)->select(RC_DB::raw('ua.*'), RC_DB::raw('u.user_name'))->get();

		if (!empty($list)) {
			foreach ($list AS $key => $value) {
				$list[$key]['surplus_amount']		= price_format(abs($value['amount']), false);
				$list[$key]['add_date']				= RC_Time::local_date(ecjia::config('time_format'), $value['add_time']);
				$list[$key]['process_type_name']	= RC_Lang::get('user::user_account.surplus_type.'.$value['process_type']);
				/* php 过滤html标签 */
				$list[$key]['payment']				= empty($pay_name[$value['payment']]) ? strip_tags($value['payment']) : strip_tags($pay_name[$value['payment']]);
			}
		}
	}
	return array('list' => $list, 'filter' => $filter, 'page' => $page->show(5), 'desc' => $page->page_desc());
}

/**
 * 插入会员账目明细
 *
 * @access  public
 * @param   array     $surplus  会员余额信息
 * @param   string    $amount   余额
 *
 * @return  int
 */
function insert_user_account($surplus, $amount) {
	$db = RC_Model::model('user/user_account_model');
	$data = array(
		'user_id'		=> $surplus['user_id'] ,
		'admin_user'	=> '' ,
		'amount'		=> $amount ,
		'add_time'		=> RC_Time::gmtime() ,
		'paid_time'		=> 0 ,
		'admin_note'	=> '' ,
		'user_note'		=> $surplus['user_note'] ,
		'process_type'	=> $surplus['process_type'] ,
		'payment'		=> $surplus['payment'] ,
		'is_paid'		=> 0
	);
	return $db->insert($data);
}


/**
 * 更新会员账目明细
 *
 * @access  public
 * @param   array     $id          帐目ID
 * @param   array     $admin_note  管理员描述
 * @param   array     $amount      操作的金额
 * @param   array     $is_paid     是否已完成
 *
 * @return  int
 */
function update_user_account($id, $amount, $admin_note, $is_paid) {
	$db = RC_Model::model('user/user_account_model');

	$data = array(
		'admin_user'	=> $_SESSION['admin_name'],
		'amount'		=> $amount,
		'add_time'		=> RC_Time::gmtime(),
		'paid_time'		=> RC_Time::gmtime(),
		'admin_note'	=> $admin_note,
		'is_paid'		=> $is_paid,
	);
	return $db->where(array('id' => $id))->update($data);
}

/**
 *  删除未确认的会员帐目信息
 *
 * @access  public
 * @param   int         $rec_id     会员余额记录的ID
 * @param   int         $user_id    会员的ID
 * @return  boolen
 */
function del_user_account($rec_id, $user_id) {
	$db = RC_Model::model('user/user_account_model');
	
	return $db->where(array('is_paid' => 0, 'id' => $rec_id, 'user_id' => $user_id))->delete();
}

/**
 * 根据会员id查询会员余额
 * @access  public
 * @param   int     $user_id        会员ID
 * @return  int
 */
function get_user_surplus($user_id) {
	$db_account_log = RC_Model::model('user/account_log_model');
	return $db_account_log->where(array('user_id' => $user_id))->sum('user_money');
}


/**
 * 查询会员余额的操作记录
 *
 * @access  public
 * @param   int     $user_id    会员ID
 * @param   int     $num        每页显示数量
 * @param   int     $start      开始显示的条数
 * @return  array
 */
function get_account_log($user_id, $num, $start, $process_type = '') {
	$db = RC_Model::model('user/user_account_model');
	$account_log = array();
	
	$where = array(
		'user_id' => $user_id,
		'process_type' => array(SURPLUS_SAVE, SURPLUS_RETURN)
	);
	if (!empty($process_type)) {
		$where['process_type'] = $process_type == 'deposit' ? 0 : 1;
	}
	$res = $db->where($where)->order(array('add_time' => 'desc'))->limit($start->limit())->select();
	
	if (!empty($res)) {
		RC_Loader::load_sys_func('global');
		$payment_db = RC_Model::model('payment/payment_model');
		foreach ($res as $key=>$rows) {
			$rows['add_time']         = RC_Time::local_date(ecjia::config('time_format'), $rows['add_time']);
			$rows['admin_note']       = nl2br(htmlspecialchars($rows['admin_note']));
			$rows['short_admin_note'] = ($rows['admin_note'] > '') ? RC_String::sub_str($rows['admin_note'], 30) : 'N/A';
			$rows['user_note']        = nl2br(htmlspecialchars($rows['user_note']));
			$rows['short_user_note']  = ($rows['user_note'] > '') ? RC_String::sub_str($rows['user_note'], 30) : 'N/A';
			$rows['pay_status']       = ($rows['is_paid'] == 0) ? __('未确认') : __('已完成');
			$rows['format_amount']    = price_format(abs($rows['amount']), false);
			
			/* 会员的操作类型： 冲值，提现 */
			if ($rows['process_type'] == 0) {
				$rows['type'] = __('充值');
			} else {
				$rows['type'] = __('提现');
			}

			/* 支付方式的ID */
			$where = array();
			$where['enabled'] = 1;
			if (substr($rows['payment'], 0 , 4) == 'pay_') {
				$where['pay_code'] = $rows['payment'];
			} else {
				$where['pay_name'] = $rows['payment'];
			}
			$payment = $payment_db->find($where);
			$rows['payment'] = $payment['pay_name'];
			$rows['pid'] = $pid = $payment['pay_id'];
			/* 如果是预付款而且还没有付款, 允许付款 */
			if (($rows['is_paid'] == 0) && ($rows['process_type'] == 0)) {
				$rows['handle'] = '<a href="user.php?act=pay&id='.$rows['id'].'&pid='.$pid.'">'.$GLOBALS['_LANG']['pay'].'</a>';
			}
			$account_log[] = $rows;
		}
		return $account_log;
	} else {
		return false;
	}
}

/**
 * 取得帐户明细
 * @param   int     $user_id    用户id
 * @param   string  $account_type   帐户类型：空表示所有帐户，user_money表示可用资金，
 *                  frozen_money表示冻结资金，rank_points表示等级积分，pay_points表示消费积分
 * @return  array
 */
function get_account_log_list($user_id, $account_type = '') {

	$db_account_log = RC_Model::model('user/account_log_model');
	/* 检查参数 */
	$where['user_id'] = $user_id;
	if (in_array($account_type, array('user_money', 'frozen_money', 'rank_points', 'pay_points'))) {
		$where[$account_type] = array('neq' => 0);
	}

	/* 查询记录总数，计算分页数 */
	$count = $db_account_log->where($where)->count();

	if ($count != 0) {
		/* 实例化分页 */
		$page = new ecjia_page($count, 15, 6);
		
		/* 查询记录 */
		$res = $db_account_log->where($where)->order(array('log_id' => 'DESC'))->limit($page->limit())->select();
		
		$arr = array();
		if (!empty($res)) {
			foreach ($res as $row) {
				$row['change_time'] = RC_Time::local_date(ecjia::config('time_format'), $row['change_time']);
				$arr[] = $row;
			}
		}
		return array('account' => $arr, 'page' => $page->show(5), 'desc' => $page->page_desc());
	}
}

/**
 * 获得账户变动金额
 * @param string$type 0,充值 1,提现
 * @return	array
 */
function get_total_amount ($start_date, $end_date, $type = 0) {

	$dbview = RC_Model::model('user/user_account_user_viewmodel');
	$dbview->view =array(
		'users' => array(
			'type'		=> Component_Model_View::TYPE_LEFT_JOIN,
			'alias'		=> 'u',
			'field'		=> 'IFNULL(SUM(amount), 0) |total_amount',
			'on'		=> 'ua.user_id = u.user_id'
		)
	);
	$end_date += 86400;
	$data = $dbview->find(array('process_type' => $type, 'is_paid' => 1, 'paid_time' => array('egt' => $start_date, 'lt' => $end_date)));

	$amount = $data['total_amount'];
	$amount = $type ? price_format(abs($amount)) : price_format($amount);
	return $amount;
}

/**
 *返回用户订单列表数据
 *
 * @accesspublic
 * @param
 *
 * @return void
 */
function get_user_order($args = array()) {
	$dbview = RC_Model::model('user/order_user_viewmodel');

	$filter['keywords']		= empty($_REQUEST['keywords'])		? '' : trim($_REQUEST['keywords']);
	$filter['start_date']	= empty($args['start_date'])		? '' : $args['start_date'];
	$filter['end_date']		= empty($args['end_date'])			? '' : $args['end_date'];
	$filter['sort_by']		= empty($_REQUEST['sort_by'])		? 'order_id' : trim($_REQUEST['sort_by']);
	$filter['sort_order']	= empty($_REQUEST['sort_order'])	? 'DESC'	 : trim($_REQUEST['sort_order']);

	$where = ' 1 ';
	if ($filter['keywords']) {
		$where .= " AND u.user_name LIKE '%" . mysql_like_quote($filter['keywords']) ."%' or o.order_sn LIKE '%" . mysql_like_quote($filter['keywords']) ."%'";
	}

	/*　时间过滤　*/
	if (!empty($args['start_date'])) {
		$where .= " AND add_time >= " . RC_Time::local_strtotime($args['start_date']);
	}
	if (!empty($args['end_date'])) {
		$where .= " AND add_time <= " . (RC_Time::local_strtotime($args['end_date']) +  86400 );
	}

	$count = $dbview->join('users')->where($where)->count();

	if ($count != 0) {
		/* 实例化分页 */
		$page = new ecjia_page($count, 15, 6);
		
		$dbview->view = array(
			'users' => array(
				'type'	=> Component_Model_View::TYPE_LEFT_JOIN,
				'alias'	=> 'u',
				'field'	=> 'o.order_id, o.order_sn, u.user_name, o.surplus, o.integral_money, o.add_time',
				'on'	=> 'o.user_id = u.user_id'
			)
		);
		
		$data = $dbview->where($where)->order(array($filter['sort_by'] => $filter['sort_order']))->limit($page->limit())->select();

		$order_list = array();
		foreach ($data as $rows) {
			$rows['add_time']	= RC_Time::local_date(ecjia::config('time_format'), $rows['add_time']);
			$order_list[]		= $rows;
		}
		
		return array('order_list' => $order_list, 'filter' => $filter, 'page' => $page->show(5), 'desc' => $page->page_desc());
	}
}

/**
 * 取得用户信息
 * @param   int     $user_id    用户id
 * @return  array   用户信息
 */
function get_user_info($user_id) {
	RC_Loader::load_app_func('common', 'goods');
	$db_users = RC_Model::model('user/users_model');
//	$user = $db_users->find(array('user_id' => $user_id));
	$user = RC_DB::table('users')->where('user_id', $user_id)->first();

	unset($user['question']);
	unset($user['answer']);

	/* 格式化帐户余额 */
	if ($user) {
		$user['formated_user_money']	 = price_format($user['user_money'], false);
		$user['formated_frozen_money']	 = price_format($user['frozen_money'], false);
	}
	return $user;
}

/**
 * 取得用户等级数组,按用户级别排序
 * @param   bool      $is_special      是否只显示特殊会员组
 * @return  array     rank_id=>rank_name
 */
function get_user_rank_list($is_special = false) {
	$db = RC_Model::model('user/user_rank_model');

	$rank_list = array();
	if ($is_special) {
		$where['special_rank'] = 1;
	} 
	
	$data = $db->field('rank_id, rank_name')->where($where)->order(array('min_points' => 'asc'))->select();
	if (!empty($data)) {
		foreach ($data as $row) {
			$rank_list[$row['rank_id']] = $row['rank_name'];
		}
	}
	return $rank_list;
}

/**
 * 记录帐户变动
 *
 * @param int $user_id
 *        	用户id
 * @param float $user_money
 *        	可用余额变动
 * @param float $frozen_money
 *        	冻结余额变动
 * @param int $rank_points
 *        	等级积分变动
 * @param int $pay_points
 *        	消费积分变动
 * @param string $change_desc
 *        	变动说明
 * @param int $change_type
 *        	变动类型：参见常量文件
 * @return void
 */
function change_account_log($user_id, $user_money = 0, $frozen_money = 0, $rank_points = 0, $pay_points = 0, $change_desc = '', $change_type = ACT_OTHER) {
	// 链接数据库
	$db_account_log = RC_Model::model('user/account_log_model');
	$db_users = RC_Model::model('user/users_model');
	/* 插入帐户变动记录 */
	$account_log = array (
		'user_id'		=> $user_id,
		'user_money'	=> $user_money,
		'frozen_money'	=> $frozen_money,
		'rank_points'	=> $rank_points,
		'pay_points'	=> $pay_points,
		'change_time'	=> RC_Time::gmtime(),
		'change_desc'	=> $change_desc,
		'change_type'	=> $change_type
	);

//	$db_account_log->insert ( $account_log );
	RC_DB::table('account_log')->insertGetId($account_log);

	/* 更新用户信息 */
	$step = $user_money.", frozen_money = frozen_money + ('$frozen_money')," .
	" rank_points = rank_points + ('$rank_points')," .
	" pay_points = pay_points + ('$pay_points')";

//	$db_users->inc('user_money' , 'user_id='.$user_id , $step);
	RC_DB::table('users')
			->where('user_id', $user_id)
			->increment('user_money', $step);

}

// TODO:以下从api移入
/**
 * 更新用户SESSION,COOKIE及登录时间、登录次数。
 *
 * @access public
 * @return void
 */
function update_user_info() {
	// 链接数据库
	$dbview = RC_Model::model('user/user_viewmodel');
	$db_users = RC_Model::model('user/users_model');
	$db_user_bonus = RC_Model::model('bonus/user_bonus_model');
	$db_bonus_type = RC_Model::model('bonus/bonus_type_model');
	$db_user_rank = RC_Model::model('user/user_rank_model');

	if (! $_SESSION['user_id']) {
		return false;
	}

	/* 查询会员信息 */
	$time = RC_Time::gmtime();

	$dbview->view = array(
		'user_bonus' => array(
			'type' => Component_Model_View::TYPE_LEFT_JOIN,
			'alias' => 'ub',
			'on' => 'ub.user_id = u.user_id AND ub.used_time = 0'
		),
		'bonus_type' => array(
			'type' => Component_Model_View::TYPE_LEFT_JOIN,
			'alias' => 'b',
			'on' => "b.type_id = ub.bonus_type_id AND b.use_start_date <= '$time' AND b.use_end_date >= '$time'"
		)
	);
	$row = $dbview->find('u.user_id = ' . $_SESSION[user_id] . '');
	if ($row) {
		/* 更新SESSION */
		$_SESSION['last_time'] = $row['last_login'];
		$_SESSION['last_ip'] = $row['last_ip'];
		$_SESSION['login_fail'] = 0;
		$_SESSION['email'] = $row['email'];

		/* 判断是否是特殊等级，可能后台把特殊会员组更改普通会员组 */
		if ($row['user_rank'] > 0) {
			$special_rank = $db_user_rank->where('rank_id = "' . $row[user_rank] . '"')->get_field('special_rank');
			if ($special_rank === '0' || $special_rank === null) {
				$data = array(
						'user_rank' => '0'
				);
				$db_users->where('user_id = ' . $_SESSION[user_id] . '')->update($data);
				$row['user_rank'] = 0;
			}
		}

		/* 取得用户等级和折扣 */
		if ($row['user_rank'] == 0) {
			// 非特殊等级，根据等级积分计算用户等级（注意：不包括特殊等级）
			$row = $db_user_rank->field('rank_id, discount')->find('special_rank = "0" AND min_points <= "' . intval($row['rank_points']) . '" AND max_points > "' . intval($row['rank_points']) . '"');
			if ($row) {
				$_SESSION['user_rank'] = $row['rank_id'];
				$_SESSION['discount'] = $row['discount'] / 100.00;
			} else {
				$_SESSION['user_rank'] = 0;
				$_SESSION['discount'] = 1;
			}
		} else {
			// 特殊等级
			$row = $db_user_rank->field('rank_id, discount')->find('rank_id = "' . $row[user_rank] . '"');
			if ($row) {
				$_SESSION['user_rank'] = $row['rank_id'];
				$_SESSION['discount'] = $row['discount'] / 100.00;
			} else {
				$_SESSION['user_rank'] = 0;
				$_SESSION['discount'] = 1;
			}
		}
	}

	/* 更新登录时间，登录次数及登录ip */
	$data = array(
		'visit_count' => visit_count + 1,
		'last_ip' => RC_Ip::client_ip(),
		'last_login' => RC_Time::gmtime()
	);
	$db_users->where('user_id = ' . $_SESSION[user_id] . '')->update($data);
}


/**
 *  添加或更新指定用户收货地址
 *
 * @access  public
 * @param   array       $address
 * @return  bool
 */
function update_address($address) {
	$db_user = RC_Model::model('users_model', 'user');
	$db_user_address = RC_Model::model('user/user_address_model');

	$address_id = intval($address['address_id']);
	unset($address['address_id']);

	if ($address_id > 0) {
		$address['district'] = empty($address['district']) ? '' : $address['district'];
		/* 更新指定记录 */
		$db_user_address->where(array('address_id' => $address_id, 'user_id' => $address['user_id']))->update($address);
	} else {
		/* 插入一条新记录 */
		$address_id = $db_user_address->insert($address);
	}

	if (isset($address['default']) && $address['default'] > 0 && isset($address['user_id'])) {
		$db_user->where(array('user_id' => $address['user_id']))->update(array('address_id' => $address_id));
	}

	return true;
}

function EM_user_info($user_id) {
	$db_collect_goods = RC_Model::model('goods/collect_goods_model');
// 	$db_order_info = RC_Model::model('order_info_model', 'orders');
	$db_user_rank = RC_Model::model('user/user_rank_model');
	$db_orderinfo_view = RC_Model::model('orders/order_info_viewmodel');
	
	RC_Loader::load_app_func('order', 'orders');
	$user_info = user_info($user_id);
	$collection_num = $db_collect_goods->where(array('user_id' => $user_id))->order(array('add_time' => 'desc'))->count();
	$await_pay = $db_orderinfo_view->join(array('order_info'))->where(array('oi.user_id' => $user_id, EM_order_query_sql('await_pay', 'oi.')))->count('*');
	$await_ship = $db_orderinfo_view->join(array('order_info'))->where(array('oi.user_id' => $user_id, EM_order_query_sql('await_ship', 'oi.')))->count('*');
	$shipped = $db_orderinfo_view->join(array('order_info'))->where(array('oi.user_id' => $user_id, EM_order_query_sql('shipped', 'oi.')))->count('*');
	$finished = $db_orderinfo_view->join(array('order_info'))->where(array('oi.user_id' => $user_id, EM_order_query_sql('finished', 'oi.')))->count('*');
	/* 取得用户等级 */
	if ($user_info['user_rank'] == 0) {
		// 非特殊等级，根据等级积分计算用户等级（注意：不包括特殊等级）
		$row = $db_user_rank->field('rank_id, rank_name')->find(array('special_rank' => 0 , 'min_points' => array('elt' => intval($user_info['rank_points'])) , 'max_points' => array('gt' => intval($user_info['rank_points']))));
	} else {
		// 特殊等级
		$row = $db_user_rank->field('rank_id, rank_name')->find(array('rank_id' => $user_info[user_rank]));
	}

	if (!empty($row)) {
		$user_info['user_rank_name'] = $row['rank_name'];
	} else {
		$user_info['user_rank_name'] = '非特殊等级';
	}
	$row = $db_user_rank->find(array('special_rank' => 0 , 'min_points' => 0));

	if ($user_info['user_rank_name'] == $row['rank_name']) {
		$level = 0;
	} else {
		$level = 1;
	}

	$bonus_list = em_get_user_bouns_list($user_id);
	$uid = sprintf("%09d", $user_id);//格式化uid字串， d 表示把uid格式为9位数的整数，位数不够的填0
	
	$dir1 = substr($uid, 0, 3);//把uid分段
	$dir2 = substr($uid, 3, 2);
	$dir3 = substr($uid, 5, 2);
	
	$filename = md5($user_info['user_name']);
	$avatar_path = RC_Upload::upload_path().'/data/avatar/'.$dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2)."_".$filename.'.jpg';
	
	if(!file_exists($avatar_path)) {
		$avatar_img = '';
	} else {
		$avatar_img = RC_Upload::upload_url().'/data/avatar/'.$dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2)."_".$filename.'.jpg';
	}
	$user_info['user_name'] = preg_replace('/<span(.*)span>/i', '', $user_info['user_name']);
	
	return array(
		'id'				=> $user_info['user_id'],
		'name'				=> $user_info['user_name'],
		'rank_name'			=> $user_info['user_rank_name'],
		'rank_level' 		=> $level,
		'collection_num' 	=> $collection_num,
		'email'				=> $user_info['email'],
		'mobile_phone'		=> $user_info['mobile_phone'],
		'avatar_img'		=> $avatar_img,
			
		'order_num' => array(
			'await_pay' 	=> $await_pay,
			'await_ship' 	=> $await_ship,
			'shipped' 		=> $shipped,
			'finished' 		=> $finished
		),
		'formated_user_money' 	=> price_format($user_info['user_money'], false),
		'user_points' 			=> $user_info['pay_points'],
		'user_bonus_count' 		=> count($bonus_list),
		'bonus_list' 			=> $bonus_list
	);
}

/**
 *用户钱包，暂时只返回可用的红包
 */
function em_get_user_bouns_list($user_id) {
	$db = RC_Model::model('bonus/user_bonus_type_viewmodel');
	$db->view = array(
		'bonus_type' 	=> array(
			'type' 	=> Component_Model_View::TYPE_LEFT_JOIN,
			'alias'	=> 'bt',
			'field' => 'ub.bonus_id, ub.order_id, bt.type_name, bt.type_money, bt.min_goods_amount, bt.use_start_date, bt.use_end_date',
			'on'   	=> 'ub.bonus_type_id = bt.type_id'
		)
	);

	$rows = $db->where(array('ub.user_id' => $user_id))->select();

	$arr = array();

	$cur_date = RC_Time::gmtime();
	if (!empty($rows)) {
		foreach ($rows as $row) {
			/* 先判断是否被使用，然后判断是否开始或过期 */
			if (empty($row['order_id'])) {
				/* 没有被使用 */
				if ($row['use_start_date'] > $cur_date) {
					unset($row);
					continue;
					$row['status'] = __('未开始');
				} else if ($row['use_end_date'] < $cur_date) {
					$row['status'] = __('已过期');
					$row['bonus_status'] = 2;
					$row['formatted_bonus_status'] = __('已过期');
				} else {
					$row['status'] = __('未使用');
					$row['bonus_status'] = 0;
					$row['formatted_bonus_status'] = __('未使用');
				}
			} else {
				/*已使用的*/
				$row['status'] = __('已使用');
				$row['bonus_status'] = 1;
				$row['formatted_bonus_status'] = __('已使用');
			}
			unset($row['order_id']);
			$row['formated_min_goods_amount'] = price_format($row['min_goods_amount'],false);
			$row['formated_use_start_date']   = RC_Time::local_date(ecjia::config('date_format'), $row['use_start_date']);
			$row['formated_use_end_date']     = RC_Time::local_date(ecjia::config('date_format'), $row['use_end_date']);
			
			$row['bonus_id']		= $row['bonus_id'];
			$row['bonus_name']		= $row['type_name'];
			$row['bonus_amount'] 	= $row['type_money'];
			$row['formatted_bonus_amount'] 		= price_format($row['type_money']);
			$row['request_amount'] 				= $row['min_goods_amount'];
			$row['formatted_request_amount'] 	= price_format($row['min_goods_amount']);
			$row['start_date']	= RC_Time::local_time($row['use_start_date']);
			$row['end_date']	= RC_Time::local_time($row['use_end_date']);
			$row['formatted_start_date']   = RC_Time::local_date(ecjia::config('date_format'), $row['use_start_date']);
			$row['formatted_end_date']     = RC_Time::local_date(ecjia::config('date_format'), $row['use_end_date']);
			$arr[] = $row;
		}
	}
	return $arr;
}

// end