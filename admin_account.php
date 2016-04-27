<?php
/**
 * ECJIA 会员充值提现管理
*/

defined('IN_ECJIA') or exit('No permission resources.');

class admin_account extends ecjia_admin {
	private $db_payment;
	private $db_user_account;
	private $db_users;
	private $db_pay_log;
	private $db_view;
	public function __construct() {
		parent::__construct();

		RC_Lang::load('user_account');
		RC_Loader::load_app_func('user');
		RC_Loader::load_app_func('common','goods');
		RC_Loader::load_app_func('global');
		assign_adminlog();
		
		$this->db_payment		= RC_Loader::load_app_model('payment_model', 'payment');
		$this->db_user_account	= RC_Loader::load_app_model('user_account_model', 'user');
		$this->db_users			= RC_Loader::load_app_model('users_model', 'user');
		$this->db_pay_log		= RC_Loader::load_app_model('pay_log_model', 'orders');
		$this->db_view			= RC_Loader::load_app_model('user_account_user_viewmodel');
		
		/* 加载所需js */
		RC_Script::enqueue_script('jquery-validate');
		RC_Script::enqueue_script('jquery-form');
		
		/* 列表页 js/css */
		RC_Script::enqueue_script('smoke');
		RC_Script::enqueue_script('bootstrap-editable.min', RC_Uri::admin_url('statics/lib/x-editable/bootstrap-editable/js/bootstrap-editable.min.js'));
		RC_Style::enqueue_style('bootstrap-editable', RC_Uri::admin_url('statics/lib/x-editable/bootstrap-editable/css/bootstrap-editable.css'));
		
		RC_Script::enqueue_script('jquery-chosen');
		RC_Style::enqueue_style('chosen');
		/* 编辑页 js/css */
		RC_Script::enqueue_script('jquery-uniform');
		RC_Style::enqueue_style('uniform-aristo');
		RC_Script::enqueue_script('admin_account', RC_App::apps_url('statics/js/admin_account.js', __FILE__));
		
		$account_jslang = array(
			'keywords_required'	=> __('请先输入关键字！'),
			'username_required'	=> __('请输入会员名称！'),
			'amount_required'	=> __('请输入金额！')
		);
		RC_Script::localize_script('admin_account', 'account_jslang', $account_jslang);
		
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('充值提现申请'), RC_Uri::url('user/admin_account/init')));
	}

	/**
	 * 充值提现申请列表
	 */
	public function init() {
		$this->admin_priv('surplus_manage');
		
		RC_Loader::load_app_func('global');
		
		ecjia_screen::get_current_screen()->remove_last_nav_here();
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('充值和提现申请')));
		ecjia_screen::get_current_screen()->add_help_tab(array(
			'id'		=> 'overview',
			'title'		=> __('概述'),
			'content'	=>
			'<p>' . __('欢迎访问ECJia智能后台充值提现申请列表页面，系统中所有的充值提现申请都会显示在此列表中。') . '</p>'
		));
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
			'<p><strong>' . __('更多信息:') . '</strong></p>' .
			'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:充值和提现申请" target="_blank">关于充值提现申请帮助文档</a>') . '</p>'
		);
		
		/* 指定会员的ID为查询条件 */
		$user_id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
		
		/* 获得支付方式列表 */
		$payment = get_payment();
		
		$list = get_account_list($_REQUEST);

		$this->assign('ur_here',		RC_Lang::lang('09_user_account'));
		$this->assign('id',				$user_id);
		$this->assign('payment',		$payment);
		$this->assign('action_link',	array('text' => RC_Lang::lang('surplus_add'), 'href' => RC_Uri::url('user/admin_account/add')));
		$this->assign('list',			$list);
		$this->assign('form_action',	RC_Uri::url('user/admin_account/init'));
		$this->assign('batch_action',	RC_Uri::url('user/admin_account/batch_remove'));
		$this->assign_lang();
		$this->display('admin_account_list.dwt');
	}
	
	/**
	 * 添加充值提现
	 */
	public function add() {
		$this->admin_priv('surplus_manage');
	
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('添加申请')));
		ecjia_screen::get_current_screen()->add_help_tab(array(
			'id'		=> 'overview',
			'title'		=> __('概述'),
			'content'	=>
			'<p>' . __('欢迎访问ECJia智能后台添加充值提现申请页面，可以在此添加充值提现申请。') . '</p>'
		));
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
			'<p><strong>' . __('更多信息:') . '</strong></p>' .
			'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:充值和提现申请#.E6.B7.BB.E5.8A.A0.E7.94.B3.E8.AF.B7" target="_blank">关于添加充值提现申请帮助文档</a>') . '</p>'
		);
		
		$ur_here  = RC_Lang::lang('surplus_add');
		$id		 = isset($_GET['id']) ? intval($_GET['id']) : 0;
		
		/* 获得支付方式列表, 不包括“货到付款” */
		$payment = get_payment();

		/* 模板赋值 */
		$this->assign('ur_here',		$ur_here);
		$this->assign('payment',		$payment);
		$this->assign('action_link',	array('href' => RC_Uri::url('user/admin_account/init'), 'text' => RC_Lang::lang('09_user_account')));
		$this->assign('form_action',	RC_Uri::url('user/admin_account/insert'));
		$this->assign_lang();
		$this->display('admin_account_edit.dwt');
	}

	/**
	 * 添加充值提现申请
	 */
	public function insert() {
		/* 权限判断 */
		$this->admin_priv('surplus_manage', ecjia::MSGTYPE_JSON);
		
		/* 初始化变量 */
		$id				= isset($_POST['id'])				? intval($_POST['id'])				: 0;
		$is_paid		= !empty($_POST['is_paid'])			? intval($_POST['is_paid'])			: 0;
		$amount			= !empty($_POST['amount'])			? floatval($_POST['amount'])		: 0;
		$process_type	= !empty($_POST['process_type'])	? intval($_POST['process_type'])	: 0;
		$username		= !empty($_POST['username'])		? trim($_POST['username'])			: '';
		$admin_note		= !empty($_POST['admin_note'])		? trim($_POST['admin_note'])		: '';
		$user_note		= !empty($_POST['user_note'])		? trim($_POST['user_note'])			: '';
		$payment		= !empty($_POST['payment'])			? trim($_POST['payment'])			: '';
		$amount_count = $amount;

		/* 验证参数有效性  */
		if (!is_numeric($amount) || empty($amount) || $amount <= 0 || strpos($amount, '.') > 0) {
			$this->showmessage(RC_Lang::lang('js_languages/deposit_amount_error'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		
		$user_id = $this->db_users->where(array('user_name' => $username))->get_field('user_id');
		/* 此会员是否存在 */
		if ($user_id == 0) {
			$this->showmessage(RC_Lang::lang('username_not_exist'), ecjia_admin::MSGTYPE_JSON | ecjia_admin::MSGSTAT_ERROR);
		}
		
		if (empty($payment)) {
			$this->showmessage(__('请选择支付方式'), ecjia_admin::MSGTYPE_JSON | ecjia_admin::MSGSTAT_ERROR);
		}
		
		/* 退款，检查余额是否足够 */
		if ($process_type == 1) {
			$user_account = get_user_surplus($user_id);
			/* 如果扣除的余额多于此会员拥有的余额，提示 */
			if ($amount > $user_account) {
				$this->showmessage(RC_Lang::lang('surplus_amount_error'), ecjia_admin::MSGTYPE_JSON | ecjia_admin::MSGSTAT_ERROR);
			}
		}

		/* 入库的操作 */
		if ($process_type == 1) {
				$amount = (-1) * $amount;
			}

			$data = array(
				'user_id'		=> $user_id,
				'admin_user'	=> $_SESSION['admin_name'],
				'amount'		=> $amount,
				'add_time'		=> RC_Time::gmtime(),
				'admin_note'	=> $admin_note,
				'user_note'		=> $user_note,
				'process_type'	=> $process_type,
				'payment'		=> $payment,
				'is_paid'		=> $is_paid,
			);
			if ($is_paid == 1) {
				$data['pay_time']		= RC_Time::gmtime();
			}
			
			$this->db_user_account->insert($data);
	
		
		/* 更新会员余额数量 */
		if ($is_paid == 1) {
			$change_desc = $amount > 0 ? RC_Lang::lang('surplus_type_0') : RC_Lang::lang('surplus_type_1');
			$change_type = $amount > 0 ? ACT_SAVING : ACT_DRAWING;
			change_account_log($user_id , $amount , 0 , 0 , 0 , $change_desc , $change_type);
		}
		
		/* 如果是预付款并且未确认，向pay_log插入一条记录 */
		if ($process_type == 0 && $is_paid == 0) {
			/* 取支付方式信息 */
			$payment_info = array();
			$payment_info = $this->db_payment->find(array('pay_name' => $payment ,'enabled' => '1'));
			
			RC_Loader::load_app_func('order', 'orders');
			/* 计算支付手续费用 */
			$pay_fee	= pay_fee($payment_info['pay_id'], $amount, 0);
			$total_fee	= $pay_fee + $amount;
		
			/* 插入 pay_log */
			$data = array(
				'order_id'		=> $id,
				'order_amount'	=> $total_fee,
				'order_type'	=> PAY_SURPLUS,
				'is_paid'		=> 0,
			);
			$this->db_pay_log->insert($data);
		}
		
		if ($process_type == 0) {
			
			ecjia_admin::admin_log('充值金额是 '.price_format($amount_count).'，'.'会员名称是 '.$username, 'add', 'pay_apply');
		} elseif ($process_type == 1) {
			
			ecjia_admin::admin_log('提现金额是 '.price_format($amount_count).'，'.'会员名称是 '.$username, 'add', 'withdraw_apply');
		}

		/* 提示信息 */
		$links[0]['text'] = RC_Lang::lang('back_list');
		$links[0]['href'] = RC_Uri::url('user/admin_account/init');
		$links[1]['text'] = RC_Lang::lang('continue_add');
		$links[1]['href'] = RC_Uri::url('user/admin_account/add');
		
		$this->showmessage(RC_Lang::lang('add_success'), ecjia_admin::MSGTYPE_JSON | ecjia_admin::MSGSTAT_SUCCESS, array('links' => $links, 'pjaxurl' => RC_Uri::url('user/admin_account/init'))); 
	}
	
	/**
	 * 编辑充值提现申请
	 */
	public function edit() {
		$this->admin_priv('surplus_manage');
	
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('编辑申请')));
		ecjia_screen::get_current_screen()->add_help_tab(array(
			'id'		=> 'overview',
			'title'		=> __('概述'),
			'content'	=>
			'<p>' . __('欢迎访问ECJia智能后台编辑充值提现申请页面，可以在此页面编辑充值提现申请信息。') . '</p>'
		));
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
			'<p><strong>' . __('更多信息:') . '</strong></p>' .
			'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:充值和提现申请" target="_blank">关于编辑充值提现申请帮助文档</a>') . '</p>'
		);
		
		$ur_here	= RC_Lang::lang('surplus_edit');
		$id			= isset($_GET['id']) ? intval($_GET['id']) : 0;
	
		/* 查询当前的预付款信息 */
		$account = array();
		$account = $this->db_user_account->find(array('id' => $id));
		$account['add_time'] = RC_Time::local_date(ecjia::config('time_format'), $account['add_time']);
		
		$user_name = $this->db_users->where(array('user_id' => $account['user_id']))->get_field('user_name');
		$account['user_note']	= htmlspecialchars($account['user_note']);
		$account['payment']		= strip_tags($account['payment']);
		$account['amount']		= abs($account['amount']);

		/* 模板赋值 */
		$this->assign('ur_here',		$ur_here);
		$this->assign('surplus',		$account);
		$this->assign('user_name',		$user_name);
		$this->assign('id',				$id);
		$this->assign('action_link',	array('text' => RC_Lang::lang('09_user_account'), 'href' => RC_Uri::url('user/admin_account/init')));
		$this->assign('form_action',	RC_Uri::url('user/admin_account/update'));
		/* 页面显示 */
		$this->assign_lang();
		$this->display('admin_account_check.dwt');
	}
	
	/**
	 * 更新充值提现申请
	 */
	public function update() {

		/* 权限判断 */
		$this->admin_priv('surplus_manage', ecjia::MSGTYPE_JSON);
		
		$id				= isset($_POST['id'])			? intval($_POST['id'])			: 0;	
		$admin_note		= !empty($_POST['admin_note'])	? trim($_POST['admin_note'])	: '';
		$user_note		= !empty($_POST['user_note'])	? trim($_POST['user_note'])		: '';

		/* 更新数据表 */
		$data = array(
			'admin_note'	=> $admin_note,
			'user_note'		=> $user_note
		);
		$this->db_user_account->where(array('id' => $id))->update($data);
		
		$info = $this->db_view->where(array('id' => $id))->find();
		
		if ($info['process_type'] == 0) {
			ecjia_admin::admin_log('会员名称是 '.$info['user_name'].'，'.'金额是 '.price_format($info['amount']), 'edit', 'pay_apply');
		} elseif ($info['process_type'] == 1) {
			$amount = (-1) * $info['amount']; 
			ecjia_admin::admin_log('会员名称是 '.$info['user_name'].'，'.'金额是 '.price_format($amount), 'edit', 'withdraw_apply');
		}

		/* 提示信息 */
		$links[0]['text'] = RC_Lang::lang('back_list');
		$links[0]['href'] = RC_Uri::url('user/admin_account/init');

		$this->showmessage(RC_Lang::lang('edit_success'), ecjia_admin::MSGTYPE_JSON | ecjia_admin::MSGSTAT_SUCCESS, array('links' => $links));
	}
	
	/**
	 * 审核会员余额页面
	 */
	public function check() {

		$this->admin_priv('surplus_manage');
	
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('到款审核')));
		ecjia_screen::get_current_screen()->add_help_tab(array(
			'id'		=> 'overview',
			'title'		=> __('概述'),
			'content'	=>
			'<p>' . __('欢迎访问ECJia智能后台到款审核页面，可以在此页面编辑到款审核信息。') . '</p>'
		));
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
			'<p><strong>' . __('更多信息:') . '</strong></p>' .
			'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:充值和提现申请#.E5.88.B0.E6.AC.BE.E5.AE.A1.E6.A0.B8" target="_blank">关于到款审核帮助文档</a>') . '</p>'
		);
		
		$ur_here = RC_Lang::lang('check');
		$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

		/* 查询当前的预付款信息 */
		$account = array();
		$account = $this->db_user_account->find(array('id' => $id));
		$account['add_time'] = RC_Time::local_date(ecjia::config('time_format'), $account['add_time']);
		
		$user_name = $this->db_users->where(array('user_id' => $account['user_id']))->get_field('user_name');
		$account['user_note']	= htmlspecialchars($account['user_note']);
		
		$payment_name = $this->db_payment->where(array('pay_code'=>$account['payment']))->get_field('pay_name');
		
		$account['payment']	= empty($payment_name) ? strip_tags($account['payment']) : strip_tags($payment_name);
		$account['amount']	= abs($account['amount']);
		
		/* 模板赋值 */
		$this->assign('ur_here',		$ur_here);
		$this->assign('surplus',		$account);
		$this->assign('user_name',		$user_name);
		$this->assign('id',				$id);
		$this->assign('action_link',	array('text' => RC_Lang::lang('09_user_account'), 'href' => RC_Uri::url('user/admin_account/init')));
		$this->assign('check_action',	RC_Uri::url('user/admin_account/action'));
		$this->assign('is_check',		1);
		/* 页面显示 */
		$this->assign_lang();
		$this->display('admin_account_check.dwt');
	}
	
	/**
	 * 更新会员余额的状态
	 */
	public function action() {

		/* 检查权限 */
		$this->admin_priv('surplus_manage', ecjia::MSGTYPE_JSON);
		
		/* 初始化 */
		$id			= isset($_POST['id'])			? intval($_POST['id'])			: 0;
		$is_paid	= isset($_POST['is_paid'])		? intval($_POST['is_paid'])		: 0;
		$admin_note	= isset($_POST['admin_note'])	? trim($_POST['admin_note'])	: '';

		/* 查询当前的预付款信息 */
		$account	= array();
		$account	= $this->db_user_account->find(array('id' => $id));
		$amount		= $account['amount'];
		
		$info = $this->db_user_account->where(array('id' => $id))->find();
		if ($is_paid == 0) {
			$stats = '设为未确认';
		} elseif ($is_paid == 1) {
			$stats = '设为已完成';
		} elseif ($is_paid == 2) {
			$stats = '设为取消';
		}
		
		$user_name = $this->db_users->where(array('user_id' => $info['user_id']))->get_field('user_name');
		
		/* 如果是退款申请, 并且已完成,更新此条记录,扣除相应的余额 */
		if ($is_paid == '1') {
			if ($account['process_type'] == '1') {
				$user_account = get_user_surplus($account['user_id']);
				$fmt_amount   = str_replace('-', '', $amount);
				
				/* 如果扣除的余额多于此会员拥有的余额，提示 */
				if ($fmt_amount > $user_account) {
					$this->showmessage(RC_Lang::lang('surplus_amount_error'), ecjia_admin::MSGTYPE_JSON | ecjia_admin::MSGSTAT_ERROR);
				}
				
				update_user_account($id, $amount, $admin_note, 1);
				
				/* 更新会员余额数量 */
				change_account_log($account['user_id'], $amount, 0, 0, 0, RC_Lang::lang('surplus_type_1'), ACT_DRAWING);
			} else {
				/* 如果是预付款，并且已完成, 更新此条记录，增加相应的余额 */
				update_user_account($id, $amount, $admin_note, 1);
				
				/* 更新会员余额数量 */
				change_account_log($account['user_id'], $amount, 0, 0, 0, RC_Lang::lang('surplus_type_0'), ACT_SAVING);
			}
			
		} else {
			/* 否则更新信息 */
			$data = array(
				'admin_user'	=> $_SESSION['admin_name'],
				'admin_note'	=> $admin_note,
				'is_paid'		=> $is_paid,
			);
			$this->db_user_account->where(array('id' => $id))->update($data);
		}
		
		if ($info['process_type'] == 1) {
			$amount = (-1) * $amount;
			ecjia_admin::admin_log($stats.'，'.'会员名称是 '.$user_name.'，'.'金额是 '.price_format($amount), 'setup', 'withdraw_apply');
		} elseif ($info['process_type'] == 0) {
			ecjia_admin::admin_log($stats.'，'.'会员名称是 '.$user_name.'，'.'金额是 '.price_format($amount), 'setup', 'pay_apply');
		}
		/* 提示信息 */
		$links[0]['text'] = RC_Lang::lang('back_list');
		$links[0]['href'] = RC_Uri::url('user/admin_account/init');
		$this->showmessage(RC_Lang::lang('attradd_succed'), ecjia_admin::MSGTYPE_JSON | ecjia_admin::MSGSTAT_SUCCESS , array('links' => $links));

	}
	
	/**
	 * ajax删除一条信息
	 */
	public function remove() {

		$db_view = RC_Loader::load_app_model('user_account_viewmodel');
		/* 检查权限 */
		$this->admin_priv('surplus_manage', ecjia::MSGTYPE_JSON);
		
		$id = @intval($_REQUEST['id']);
		$data = $db_view->join('user_account')->field('ua.amount,ua.process_type')->find(array('ua.id' => $id));
		
		$user_name = $data['user_name'];
		
		if ($this->db_user_account->where(array('id' => $id))->delete()) {
			
			if ($data['process_type'] == 1) {
				$amount = (-1) * $data['amount'];
				ecjia_admin::admin_log('会员名称是 '.$user_name.'，'.'金额是 '.price_format($amount), 'remove', 'withdraw_apply');
			} else {
				ecjia_admin::admin_log('会员名称是 '.$user_name.'，'.'金额是 '.price_format($data['amount']), 'remove', 'pay_apply');
			}
			
			$this->showmessage(RC_Lang::lang('drop_success'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
		} else {
			$this->showmessage($db_view->error(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	}
	
	/**
	 * 批量删除
	 */
	public function batch_remove() {
		/* 检查权限 */
		$this->admin_priv('surplus_manage', ecjia::MSGTYPE_JSON);
		
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		
		if (isset($_POST['checkboxes'])) {
			$idArr = explode(',', $_POST['checkboxes']);
			$count = count($idArr);
			
			$data = $this->db_view->field('ua.amount,ua.process_type')->in(array('ua.id' => $idArr))->select();
			
			if($this->db_user_account->where(array('id' => $idArr))->delete()) {
				
				foreach ($data as $v) {
					if ($v['process_type'] == 1) {
						$amount = (-1) * $v['amount'];
						ecjia_admin::admin_log('会员名称是 '.$v['user_name'].'，'.'金额是 '.price_format($amount), 'batch_remove', 'withdraw_apply');
					} else {
						ecjia_admin::admin_log('会员名称是 '.$v['user_name'].'，'.'金额是 '.price_format($v['amount']), 'batch_remove', 'pay_apply');
					}
				}
				$this->showmessage(__('本次删除了').$count.__('条记录！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('user/admin_account/init')));
			}
		} else {
			$this->showmessage(__('请先选择要操作的项！'), ecjia_admin::MSGTYPE_JSON | ecjia_admin::MSGSTAT_ERROR);
		}
	}
}

// end