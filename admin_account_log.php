<?php
/**
 * 会员帐户变动记录
 */

defined('IN_ECJIA') or exit('No permission resources.');

class admin_account_log extends ecjia_admin {
	public function __construct() {
		parent::__construct();
		
		RC_Lang::load(ROUTE_C);
		RC_Loader::load_app_func('user');
		RC_Loader::load_app_func('global');
		assign_adminlog();
		
		/* 加载所需js */
		RC_Script::enqueue_script('jquery-validate');
		RC_Script::enqueue_script('jquery-form');
		
		RC_Script::enqueue_script('jquery-chosen');
		RC_Style::enqueue_style('chosen');

		RC_Script::enqueue_script('user_info', RC_App::apps_url('statics/js/user_info.js', __FILE__));
		//加载生成图表的JQ插件
		RC_Script::enqueue_script('jquery-peity');
		
		$account_log_jslang = array(
			'change_desc_required' => __('请输入账户变动原因！')
		);
		RC_Script::localize_script('user_info', 'account_log_jslang', $account_log_jslang );
		
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('会员列表'), RC_Uri::url('user/admin/init')));
	}
	
	/**
	 * 账户明细列表
	 */
	public function init() {
		$this->admin_priv('account_manage');
		
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('会员账户变动明细')));
		ecjia_screen::get_current_screen()->add_help_tab(array(
			'id'		=> 'overview',
			'title'		=> __('概述'),
			'content'	=>
			'<p>' . __('欢迎访问ECJia智能后台会员账户变动明细页面，可以在此页面查看相应会员账户变动明细信息。') . '</p>'
		));
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
			'<p><strong>' . __('更多信息:') . '</strong></p>' .
			'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:会员列表#.E6.9F.A5.E7.9C.8B.E8.B4.A6.E7.9B.AE.E6.98.8E.E7.BB.86" target="_blank">关于会员账户变动明细帮助文档</a>') . '</p>'
		);
		
		/* 检查参数 */
		$user_id = empty($_REQUEST['user_id']) ? 0 : intval($_REQUEST['user_id']);
		$user = get_user_info($user_id);

		if (empty($_REQUEST['account_type']) || !in_array($_REQUEST['account_type'], array('user_money', 'frozen_money', 'rank_points', 'pay_points'))) {
			$account_type = '';
		} else {
			$account_type = $_REQUEST['account_type'];
		}

		$account_list = get_account_log_list($user_id, $account_type);
		
		$this->assign('user',			$user);
		$this->assign('account_type',	$account_type);
		$this->assign('ur_here',		RC_Lang::lang('account_list'));
		$this->assign('action_link',	array('text' => RC_Lang::lang('add_account'), 'href' => RC_Uri::url('user/admin_account_log/edit', array('user_id' => $user_id))));
		$this->assign('account_list',	$account_list);
		$this->assign('form_action',	RC_Uri::url('user/admin_account_log/init', array('user_id' => $user_id)));
		$this->assign_lang();
		$this->display('account_log_list.dwt');
	}
	
	/**
	 * 调节帐户
	 */
	public function edit() {
		$this->admin_priv('account_manage');
		/* 检查参数 */
		$user_id = empty($_REQUEST['user_id']) ? 0 : intval($_REQUEST['user_id']);
		
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('会员账户变动明细') , RC_Uri::url('user/admin_account_log/init', 'user_id='.$user_id)));
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('调节会员账户')));
		ecjia_screen::get_current_screen()->add_help_tab(array(
			'id'		=> 'overview',
			'title'		=> __('概述'),
			'content'	=>
			'<p>' . __('欢迎访问ECJia智能后台调节会员帐户页面，可以在此页面编辑相应会员账户信息。') . '</p>'
		));
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
			'<p><strong>' . __('更多信息:') . '</strong></p>' .
			'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:会员列表#.E6.9F.A5.E7.9C.8B.E8.B4.A6.E7.9B.AE.E6.98.8E.E7.BB.86" target="_blank">关于调节会员帐户帮助文档</a>') . '</p>'
		);
		
		/* 检查权限 */
		// $this->admin_priv('account_log');
		
		$user = get_user_info($user_id);

		/* 显示模板 */
		$this->assign('user',			$user);
		$this->assign('ur_here',		RC_Lang::lang('add_account'));
		$this->assign('action_link',	array('href' => RC_Uri::url('user/admin_account_log/init', array('user_id' => $user_id)), 'text' => RC_Lang::lang('account_list')));
		$this->assign('form_action',	RC_Uri::url('user/admin_account_log/update', array('user_id' => $user_id)));
		$this->assign_lang();
		$this->display('account_log_edit.dwt');
	}
	
	/**
	 * 调节会员账户
	 */
	public function update() {
		$this->admin_priv('account_manage', ecjia::MSGTYPE_JSON);

		$user_id = empty($_REQUEST['user_id']) ? 0 : intval($_REQUEST['user_id']);
		$user = get_user_info($user_id);
		
		if (empty($user)) {			
			$this->showmessage(RC_Lang::lang('user_not_exist') , ecjia_admin::MSGTYPE_JSON | ecjia_admin::MSGSTAT_ERROR);
		}
		
		$user_money		= !empty($_POST['user_money']) ? intval($_POST['user_money']): 0;
		$frozen_money	= $_POST['frozen_money'] ? intval($_POST['frozen_money']): 0;
		$rank_points	= $_POST['rank_points'] ? intval($_POST['rank_points']): 0;
		$pay_points		= $_POST['pay_points'] ? intval($_POST['pay_points']): 0;

		/* 参数验证 */
		if ($user_money < 0 || !is_numeric($user_money) || !isset($user_money)) {
			$this->showmessage(__('可用资金账户填写有误！') , ecjia_admin::MSGTYPE_JSON | ecjia_admin::MSGSTAT_ERROR);
		}
		if ($frozen_money < 0 || !is_numeric($frozen_money) || !isset($frozen_money)) {
			$this->showmessage(__('冻结资金账户填写有误！') , ecjia_admin::MSGTYPE_JSON | ecjia_admin::MSGSTAT_ERROR);
		}
		if ($rank_points < 0 || !is_numeric($rank_points) || !isset($rank_points)) {
			$this->showmessage(__('等级积分账户填写有误！') , ecjia_admin::MSGTYPE_JSON | ecjia_admin::MSGSTAT_ERROR);
		}
		if ($pay_points < 0 || !is_numeric($pay_points) || !isset($pay_points)) {
			$this->showmessage(__('消费积分账户填写有误！') , ecjia_admin::MSGTYPE_JSON | ecjia_admin::MSGSTAT_ERROR);
		}
		
		if ($user_money == 0 && $frozen_money == 0 && $rank_points == 0 && $pay_points == 0) {
			$this->showmessage(__('没有修改任何记录！') , ecjia_admin::MSGTYPE_JSON | ecjia_admin::MSGSTAT_ERROR);
		}
		
		$change_desc	= RC_String::sub_str($_POST['change_desc'] , 255 , false);
		$user_money		= floatval($_POST['add_sub_user_money']) * abs(floatval($user_money));
		$frozen_money	= floatval($_POST['add_sub_frozen_money']) * abs(floatval($frozen_money));
		$rank_points	= floatval($_POST['add_sub_rank_points']) * abs(floatval($rank_points));
		$pay_points		= floatval($_POST['add_sub_pay_points']) * abs(floatval($pay_points));
		
		/* 保存 */
		change_account_log($user_id, $user_money, $frozen_money, $rank_points, $pay_points, $change_desc, ACT_ADJUSTING);
		
		if (!empty($user_money)) {
			if ($user_money > 0) {
				ecjia_admin::admin_log('增加可用资金 '.intval($_POST['user_money']).'，'.'会员名称是 '.$user['user_name'], 'setup', 'users_account');
			} else {
				ecjia_admin::admin_log('减少可用资金 '.intval($_POST['user_money']).'，'.'会员名称是 '.$user['user_name'], 'setup', 'users_account');
			}
		}
		
		if (!empty($frozen_money)) {
			if ($frozen_money > 0) {
				ecjia_admin::admin_log('增加冻结资金 '.intval($_POST['frozen_money']).'，'.'会员名称是 '.$user['user_name'], 'setup', 'users_account');
			} else {
				ecjia_admin::admin_log('减少冻结资金 '.intval($_POST['frozen_money']).'，'.'会员名称是 '.$user['user_name'], 'setup', 'users_account');
			}
		}
		
		if (!empty($rank_points)) {
			if ($rank_points > 0) {
				ecjia_admin::admin_log('增加等级积分 '.intval($_POST['rank_points']).'，'.'会员名称是 '.$user['user_name'], 'setup', 'users_account');
			} else {
				ecjia_admin::admin_log('减少等级积分 '.intval($_POST['rank_points']).'，'.'会员名称是 '.$user['user_name'], 'setup', 'users_account');
			}
		}
		
		if (!empty($pay_points)) {
			if ($pay_points > 0) {
				ecjia_admin::admin_log('增加消费积分 '.intval($_POST['pay_points']).'，'.'会员名称是 '.$user['user_name'], 'setup', 'users_account');
			} else {
				ecjia_admin::admin_log('减少消费积分 '.intval($_POST['pay_points']).'，'.'会员名称是 '.$user['user_name'], 'setup', 'users_account');
			}
		}
		
		/* 提示信息 */
		$links[] = array('href' => RC_Uri::url('user/admin_account_log/init', array('user_id' => $user_id)), 'text' => RC_Lang::lang('account_list'));
		$this->showmessage(RC_Lang::lang('log_account_change_ok'), ecjia_admin::MSGTYPE_JSON | ecjia_admin::MSGSTAT_SUCCESS, array('url' => RC_Uri::url('user/admin_account_log/edit', array('user_id' => $user_id)),'links' => $links));
	}
}

// end