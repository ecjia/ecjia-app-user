<?php
/**
 * ECJIA 会员资金管理程序
*/
defined('IN_ECJIA') or exit('No permission resources.');

class admin_account_manage extends ecjia_admin 
{
	private $db_account_log;
	private $db_order_info;
	public function __construct() 
	{
		parent::__construct();
		
		RC_Lang::load('user_account_manage');
		
		RC_Loader::load_app_func('user');
		RC_Loader::load_app_func('common','goods');
		$this->db_account_log	= RC_Loader::load_app_model('account_log_model');
		$this->db_order_info	= RC_Loader::load_app_model('order_info_model');
		
		RC_Script::enqueue_script('smoke');
		RC_Script::enqueue_script('jquery-chosen');
		RC_Style::enqueue_style('chosen');
		/* 编辑页 js/css */
		RC_Script::enqueue_script('jquery-uniform');
		RC_Style::enqueue_style('uniform-aristo');
		
		RC_Style::enqueue_style('datepicker', RC_Uri::admin_url('statics/lib/datepicker/datepicker.css'));
		RC_Script::enqueue_script('bootstrap-datepicker', RC_Uri::admin_url('statics/lib/datepicker/bootstrap-datepicker.min.js'));
		RC_Script::enqueue_script('user_surplus', RC_App::apps_url('statics/js/user_surplus.js' , __FILE__));
		//加载生成图表的JQ插件
		RC_Script::enqueue_script('jquery-peity');
		
		$surplus_jslang = array(
			'keywords_required'			=> __('请先输入关键字！'),
		);
		RC_Script::localize_script( 'user_surplus' , 'surplus_jslang' , $surplus_jslang );
	}

	/**
	 * 资金管理
	 */
	public function init()
	{

		$this->admin_priv('account_manage');
		
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('资金管理')));
		ecjia_screen::get_current_screen()->add_help_tab( array(
		'id'		=> 'overview',
		'title'		=> __('概述'),
		'content'	=>
		'<p>' . __('欢迎访问ECJia智能后台资金管理页面，系统中所有的资金管理都会显示在此页面中。') . '</p>'
		) );
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
		'<p><strong>' . __('更多信息:') . '</strong></p>' .
		'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:资金管理" target="_blank">关于资金管理帮助文档</a>') . '</p>'
		);
		
		/* 时间参数 */
		$start_date = $end_date = '';
		if (isset($_POST) && !empty($_POST)) {
			$start_date	= RC_Time::local_strtotime($_POST['start_date']);
			$end_date	= RC_Time::local_strtotime($_POST['end_date']);
			
		} elseif (isset($_GET['start_date']) && !empty($_GET['end_date'])) {
			$start_date	= RC_Time::local_strtotime($_GET['start_date']);
			$end_date	= RC_Time::local_strtotime($_GET['end_date']);
			
		} else {
			$today		= RC_Time::local_strtotime(RC_Time::local_date('Y-m-d'));
			$start_date	= $today - 86400 * 7;
			$end_date	= $today;
		}

		$account = $money_list = array();
		$account['voucher_amount'] = get_total_amount($start_date, $end_date);		//	充值总额
		$account['to_cash_amount'] = get_total_amount($start_date, $end_date, 1);	//	提现总额

		$money_list = $this->db_account_log->field('IFNULL(SUM(user_money), 0)|user_money, IFNULL(SUM(frozen_money), 0)|frozen_money')->find(array('change_time' => array('egt' => $start_date , 'lt' => $end_date + 86400)));
		
		$account['user_money']		= price_format($money_list['user_money']);	//	用户可用余额
		$account['frozen_money']	= price_format($money_list['frozen_money']);	//	用户冻结金额		
		
		$money = $this->db_order_info->field('IFNULL(SUM(surplus), 0)|surplus, IFNULL(SUM(integral_money), 0)|integral_money')->find(array('add_time' => array('egt' => $start_date , 'lt' => $end_date + 86400)));
		
		$account['surplus']			= price_format($money['surplus']);		//	交易使用余额
		$account['integral_money']	= price_format($money['integral_money']); //	积分使用余额

		/* 赋值到模板 */
		$this->assign('account',		$account);
		$this->assign('start_date',		RC_Time::local_date('Y-m-d', $start_date));
		$this->assign('end_date',		RC_Time::local_date('Y-m-d', $end_date));
		$this->assign('ur_here',		RC_Lang::lang('user_account_manage'));
		$this->assign('form_action',	RC_Uri::url('user/admin_account_manage/init'));
		$this->assign_lang();
		$this->display('admin_account_manage.dwt');
	}
	
	/**
	 * 积分余额订单
	 */
	public function surplus()
	{

		$this->admin_priv('account_manage');
		
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('资金管理') , RC_Uri::url('user/admin_account_manage/init')));
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('积分余额订单')));
		ecjia_screen::get_current_screen()->add_help_tab( array(
		'id'		=> 'overview',
		'title'		=> __('概述'),
		'content'	=>
		'<p>' . __('欢迎访问ECJia智能后台积分余额订单页面，系统中所有的积分余额订单都会显示在此列表中。') . '</p>'
		) );
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
		'<p><strong>' . __('更多信息:') . '</strong></p>' .
		'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:资金管理" target="_blank">关于积分余额订单帮助文档</a>') . '</p>'
		);
		
		$order_list = get_user_order($_REQUEST);
		
		/* 赋值到模板 */
		$this->assign('order_list',		$order_list);
		$this->assign('ur_here',		__('积分余额订单'));
		$this->assign('form_action',	RC_Uri::url('user/admin_account_manage/surplus'));
		$this->assign('action_link',	array('text' => RC_Lang::lang('user_account_manage') , 'href' => RC_Uri::url('user/admin_account_manage/init')));
		
		$this->assign_lang();
		$this->display('user_surplus_list.dwt');
	}
	
}

// end 