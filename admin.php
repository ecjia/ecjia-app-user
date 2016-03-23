<?php

/**
 * ECJIA 会员管理程序
*/

defined('IN_ECJIA') or exit('No permission resources.');

class admin extends ecjia_admin {
	private $db_user;
	private $db_order;
	private $db_user_rank;
	private $db_reg_fields;
	private $db_reg_extend_info;
	private $db_view;
	private $db_view_user;
	private $db_user_address;
	public function __construct() {
		parent::__construct();

		RC_Lang::load('admin_users');

		RC_Loader::load_app_func('user');
		RC_Loader::load_app_func('common','goods');

		$this->db_user				= RC_Loader::load_app_model('users_model');
		$this->db_order				= RC_Loader::load_app_model('order_info_model', 'orders');
		$this->db_user_rank			= RC_Loader::load_app_model('user_rank_model');
		$this->db_reg_fields		= RC_Loader::load_app_model('reg_fields_model');
		$this->db_reg_extend_info	= RC_Loader::load_app_model('reg_extend_info_model');
		$this->db_view				= RC_Loader::load_app_model('user_address_viewmodel');
		$this->db_view_user			= RC_Loader::load_app_model('user_viewmodel');
		$this->db_user_address		= RC_Loader::load_app_model('user_address_user_viewmodel','user');

		/* 加载所全局 js */
		RC_Script::enqueue_script('jquery-validate');
		RC_Script::enqueue_script('jquery-form');


		/* 组件模块加载 */
		RC_Script::enqueue_script('smoke');

		RC_Style::enqueue_style('chosen');
		RC_Script::enqueue_script('jquery-chosen');

		RC_Style::enqueue_style('uniform-aristo');
		RC_Script::enqueue_script('jquery-uniform');

		RC_Style::enqueue_style('bootstrap-editable', RC_Uri::admin_url('statics/lib/x-editable/bootstrap-editable/css/bootstrap-editable.css'));
		RC_Script::enqueue_script('bootstrap-editable.min', RC_Uri::admin_url('statics/lib/x-editable/bootstrap-editable/js/bootstrap-editable.min.js'));
		
		RC_Script::enqueue_script('user_info', RC_App::apps_url('statics/js/user_info.js' , __FILE__));
		
		$user_jslang = array(
				'keywords_required'			=> __('请先输入关键字！'),
				'username_required'			=> __('请输入会员名称！'),
				'email_required'			=> __('请输入邮件地址！'),
				'password_required'			=> __('请输入密码！')
		);
		RC_Script::localize_script( 'user_info' , 'user_jslang' , $user_jslang );
		
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('会员列表') , RC_Uri::url('user/admin/init')));
	}

	/**
	 * 用户帐号列表
	 */
	public function init ()
	{
		/* 检查权限 */
		$this->admin_priv('users_manage');

		ecjia_screen::get_current_screen()->remove_last_nav_here();
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('会员列表')));
		ecjia_screen::get_current_screen()->add_help_tab( array(
		'id'		=> 'overview',
		'title'		=> __('概述'),
		'content'	=>
		'<p>' . __('欢迎访问ECJia智能后台会员列表页面，系统中所有的会员都会显示在此列表中。') . '</p>'
		) );
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
		'<p><strong>' . __('更多信息:') . '</strong></p>' .
		'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:会员列表" target="_blank">关于会员列表帮助文档</a>') . '</p>'
		);
		

		$ranks = $this->db_user_rank->field('rank_id,rank_name,min_points')->order(array('min_points' => 'asc'))->select();
		$user_list = get_user_list($_REQUEST);

		$this->assign('user_ranks',		$ranks);
		$this->assign('user_list',		$user_list);
		$this->assign('ur_here',		RC_Lang::lang('03_users_list'));
		$this->assign('action_link',	array('text' => RC_Lang::lang('04_users_add') , 'href' => RC_Uri::url('user/admin/add')));
		$this->assign('search_action',	RC_Uri::url('user/admin/init'));
		$this->assign('form_action',	RC_Uri::url('user/admin/batch_remove'));
		$this->assign_lang();
		$this->display('user_list.dwt');
	}	

	/**
	 * 添加会员帐号
	 */
	public function add()
	{
		/* 检查权限 */
		$this->admin_priv('users_manage');

		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('添加会员')));
		ecjia_screen::get_current_screen()->add_help_tab( array(
		'id'		=> 'overview',
		'title'		=> __('概述'),
		'content'	=>
		'<p>' . __('欢迎访问ECJia智能后台添加会员页面，可以在此添加会员信息。') . '</p>'
		) );
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
		'<p><strong>' . __('更多信息:') . '</strong></p>' .
		'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:添加会员" target="_blank">关于添加会员帮助文档</a>') . '</p>'
		);
		
		$user = array(
			'rank_points'	=> ecjia::config('register_points'),
			'pay_points'	=> ecjia::config('register_points'),
			'sex'			=> 0,
			'credit_line' 	=> 0
		);
		/* 取出注册扩展字段 */
		$extend_info_list = $this->db_reg_fields->where(array('type' => array('lt' => 2) , 'display' => 1 , 'id' => array('neq' => 6)))->order(array('dis_order' => 'asc' , 'id' => 'asc'))->select();
		/* 给扩展字段加入key */
		foreach ($extend_info_list as $key => $val) {
			$val['key'] = $key+1 ;
			$extend_info_list[$key] = $val;	
		}
		
		$rank_list = get_user_rank_list(true);

		$this->assign('form_act',				'insert');
		$this->assign('ur_here',				RC_Lang::lang('04_users_add'));
		$this->assign('action_link',			array('text' => RC_Lang::lang('03_users_list') , 'href' => RC_Uri::url('user/admin/init')));
		$this->assign('form_action',			RC_Uri::url('user/admin/insert'));
		$this->assign('user',					$user);
		$this->assign('special_ranks',			$rank_list);
		$this->assign('extend_info_list',		$extend_info_list);
		$this->assign_lang();
		$this->display('user_edit.dwt');
	}
	
	
	/**
	 * 添加会员帐号
	 */
	public function insert()
	{
		/* 检查权限 */
		$this->admin_priv('users_manage' , ecjia::MSGTYPE_JSON);
		RC_Loader::load_app_class('integrate', 'user', false);
		$user = integrate::init_users();
		
		$username			= empty($_POST['username'])			? ''	: trim($_POST['username']);
		$password			= empty($_POST['password'])			? ''	: trim($_POST['password']);
		$confirm_password	= empty($_POST['confirm_password'])	? ''	: trim($_POST['confirm_password']);
		$email				= empty($_POST['email'])			? ''	: trim($_POST['email']);
		$sex				= empty($_POST['sex'])				? 0		: intval($_POST['sex']);
		$sex				= in_array($sex, array(0, 1, 2))	? $sex	: 0;
		$birthday			= empty($_POST['birthday'])			? ''	: $_POST['birthday'];
		$rank				= empty($_POST['user_rank'])		? 0		: intval($_POST['user_rank']);
		$credit_line		= empty($_POST['credit_line'])		? 0		: trim($_POST['credit_line']);

		/* 验证参数的合法性*/
		/* 邮箱*/
		if (!@ereg("^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+" , $email)) {
			$this->showmessage(RC_Lang::lang('js_languages/invalid_email') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	
		if (!empty($password)) {
			if (!preg_match("/^[A-Za-z0-9]+$/",$password)){
				$this->showmessage(RC_Lang::lang('js_languages/chinese_password') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
			if (empty($confirm_password)) {
				$this->showmessage(RC_Lang::lang('js_languages/no_confirm_password') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
			if ($password != $confirm_password ) {
				$this->showmessage(RC_Lang::lang('js_languages/password_not_same') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
			if (strlen($password) < 6 || strlen($confirm_password) < 6) {
				$this->showmessage(RC_Lang::lang('js_languages/password_len_err') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
			if (preg_match("/ /" , $password)) {
				$this->showmessage(RC_Lang::lang('js_languages/passwd_balnk') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
		}
	
		/* 信用额度*/
		if (!is_numeric($credit_line) || $credit_line < 0 ) {
			$this->showmessage(RC_Lang::lang('js_languages/credit_line') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}

		/* 注册送积分 */
		if (ecjia::config('register_points',ecjia::CONFIG_EXISTS)) {
			change_account_log($_SESSION['user_id'] , 0 , 0 , ecjia::config('register_points') , ecjia::config('register_points') , RC_Lang::lang('register_points'));
		}

		/* 更新会员的其它信息 */
		$other					= array();
		$other['user_name']		= $username;
		$other['password']		= $password;
		$other['email']			= $email;
		$other['credit_line']	= $credit_line;
		$other['user_rank']		= $rank;
		$other['sex']			= $sex;
		$other['birthday']		= $birthday;
		$other['reg_time']		= RC_Time::local_strtotime(RC_Time::local_date('Y-m-d H:i:s'));
		$other['msn']			= isset($_POST['extend_field1']) ? htmlspecialchars(trim($_POST['extend_field1'])) : '';
		$other['qq']			= isset($_POST['extend_field2']) ? htmlspecialchars(trim($_POST['extend_field2'])) : '';
		$other['office_phone']	= isset($_POST['extend_field3']) ? htmlspecialchars(trim($_POST['extend_field3'])) : '';
		$other['home_phone']	= isset($_POST['extend_field4']) ? htmlspecialchars(trim($_POST['extend_field4'])) : '';
		$other['mobile_phone']	= isset($_POST['extend_field5']) ? htmlspecialchars(trim($_POST['extend_field5'])) : '';

		$max_id = $this->db_user->insert($other);

		
		/*把新注册用户的扩展信息插入数据库*/
		$fields_arr = $this->db_reg_fields->field('id')->where(array('type' => 0 , 'display' => 1))->order(array('dis_order' => 'asc' , 'id' => 'asc'))->select();
		
		$extend_field_str = '';	//生成扩展字段的内容字符串
		
		foreach ($fields_arr AS $val) {
			$extend_field_index = 'extend_field' . $val['id'];
			if(!empty($_POST[$extend_field_index])) {
				$temp_field_content = strlen($_POST[$extend_field_index]) > 100 ? mb_substr($_POST[$extend_field_index], 0, 99) : $_POST[$extend_field_index];
				$data = array (
						'user_id'		=> $max_id,
						'reg_field_id'	=> $val['id'],
						'content'		=> $temp_field_content
				);	
				$this->db_reg_extend_info->insert($data);
			}
		}
		
		/* 记录管理员操作 */
		ecjia_admin::admin_log($username , 'add' , 'users');
		
		/* 提示信息 */
		$links[] = array('text' => '返回会员列表' , 'href' => RC_Uri::url('user/admin/init'));
		$links[] = array('text' =>'继续添加会员', 'href' => RC_Uri::url('user/admin/add'));
		$this->showmessage(__('会员名')."[ ".$username." ]".__('添加成功！') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS , array('links' => $links , 'pjaxurl' => RC_Uri::url('user/admin/edit' , "id=$max_id")));
		
		
// 		$users = &init_users();
// 		if (!$users->add_user($username, $password, $email)) {
// 			/* 插入会员数据失败 */
// 			if ($users->error == ERR_INVALID_USERNAME) {
// 				$msg = RC_Lang::lang('username_invalid');
// 			} elseif ($users->error == ERR_USERNAME_NOT_ALLOW) {
// 				$msg = RC_Lang::lang('username_not_allow');
// 			} elseif ($users->error == ERR_USERNAME_EXISTS) {
// 				$msg = RC_Lang::lang('username_exists');
// 			} elseif ($users->error == ERR_INVALID_EMAIL) {
// 				$msg = RC_Lang::lang('email_invalid');
// 			} elseif ($users->error == ERR_EMAIL_NOT_ALLOW) {
// 				$msg = RC_Lang::lang('email_not_allow');
// 			} elseif ($users->error == ERR_EMAIL_EXISTS) {
// 				$msg = RC_Lang::lang('email_exists');
// 			}
// 			$this->showmessage($msg , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
// 		}
	}
	
	/**
	 * 编辑用户帐号
	 */
	public function edit()
	{
		/* 检查权限 */
		$this->admin_priv('users_manage' , ecjia::MSGTYPE_JSON);

		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('编辑会员')));
		ecjia_screen::get_current_screen()->add_help_tab( array(
		'id'		=> 'overview',
		'title'		=> __('概述'),
		'content'	=>
		'<p>' . __('欢迎访问ECJia智能后台编辑会员页面，可以在此页面编辑相应的会员信息。') . '</p>'
		) );
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
		'<p><strong>' . __('更多信息:') . '</strong></p>' .
		'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:会员列表#.E4.BC.9A.E5.91.98.E7.BC.96.E8.BE.91" target="_blank">关于编辑会员帮助文档</a>') . '</p>'
		);
		
		$row = $this->db_user->find(array('user_id' => $_GET['id']));
		if ($row) {
			$user['user_id']				= $row['user_id'];
			$user['email']					= $row['email'];
			$user['user_name']				= $row['user_name'];
			$user['sex']					= $row['sex'];
			$user['birthday']				= date($row['birthday']);
			$user['pay_points']				= $row['pay_points'];
			$user['rank_points']			= $row['rank_points'];
			$user['user_rank']				= $row['user_rank'];
			$user['user_money']				= $row['user_money'];
			$user['frozen_money']			= $row['frozen_money'];
			$user['credit_line']			= $row['credit_line'];
			$user['formated_user_money']	= price_format($row['user_money']);
			$user['formated_frozen_money']	= price_format($row['frozen_money']);
			$user['qq']						= $row['qq'];
			$user['msn']					= $row['msn'];
			$user['office_phone']			= $row['office_phone'];
			$user['home_phone']				= $row['home_phone'];
			$user['mobile_phone']			= $row['mobile_phone'];
		} 

		/* 取出注册扩展字段 */
		$extend_info_list	= $this->db_reg_fields->where(array('type' => array('lt' => 2) , 'display' => 1 , 'id' => array('neq' => 6)))->order(array('dis_order' => 'asc' , 'id' => 'asc'))->select();
		$extend_info_arr	= $this->db_reg_extend_info->field('reg_field_id, content')->where(array('user_id' => $user['user_id']))->select();

		$temp_arr = array();
		if (isset($extend_info_arr)) {
			foreach ($extend_info_arr AS $val) {
				$temp_arr[$val['reg_field_id']] = $val['content'];
			}
		}

		foreach ($extend_info_list AS $key => $val) {
// 			$val['key'] = $key+1 ;
// 			$extend_info_list[$key] = $val;
// 			switch ($key+1) {
			switch ($val['id']) {
				case 1:	 $extend_info_list[$key]['content'] = $user['msn']; break;
				case 2:	 $extend_info_list[$key]['content'] = $user['qq']; break;
				case 3:	 $extend_info_list[$key]['content'] = $user['office_phone']; break;
				case 4:	 $extend_info_list[$key]['content'] = $user['home_phone']; break;
				case 5:	 $extend_info_list[$key]['content'] = $user['mobile_phone']; break;
				default: $extend_info_list[$key]['content'] = empty($temp_arr[$val['id']]) ? '' : $temp_arr[$val['id']] ;
			}
		}
		
		$this->assign('extend_info_list' , $extend_info_list);

		/* 当前会员推荐信息 */
		$affiliate = unserialize(ecjia::config('affiliate'));
		$this->assign('affiliate', $affiliate);
		
		empty($affiliate) && $affiliate = array();
		if(empty($affiliate['config']['separate_by'])) {
			//推荐注册分成
			$affdb = array();
			$num = count($affiliate['item']);
			$up_uid = "'$_GET[id]'";
			for ($i = 1 ; $i <=$num ;$i++) {
				$count = 0;
				if ($up_uid) {
					$data = $this->db_user->field('user_id')->in(array('parent_id' => $up_uid))->select();
					$up_uid = '';
					if(!empty($data)) {
						foreach ($data as $key => $rt) {
							$up_uid .= $up_uid ? ",'$rt[user_id]'" : "'$rt[user_id]'";
							$count++;
						}
					}
				}
				$affdb[$i]['num'] = $count;
			}
			if ($affdb[1]['num'] > 0) {
				$this->assign('affdb', $affdb);
			}
		}

		$this->assign('ur_here',		__("编辑会员"));
		$this->assign('action_link',	array('text' => RC_Lang::lang('03_users_list') , 'href' => RC_Uri::url('user/admin/init')));
		$this->assign('form_action',	RC_Uri::url('user/admin/update'));
		$this->assign('special_ranks',	get_user_rank_list(true));
		$this->assign('form_act',		'update');
		$this->assign('action',    'edit');
		$this->assign('user',			$user);
		$this->assign_lang();
		$this->display('user_edit.dwt');
		
// 		$users=& init_users();
// 		$user = $users->get_user_info($row['user_name']);
		
	}	
	
	/**
	 * 更新用户帐号
	 */
	public function update()
	{
		/* 检查权限 */
		$this->admin_priv('users_manage' , ecjia::MSGTYPE_JSON);
		RC_Loader::load_app_class('integrate', 'user', false);
		$user = integrate::init_users();

		$username			= empty($_POST['username'])				? '' : trim($_POST['username']);
		$user_id			= trim($_POST['id']);
		$password			= trim($_POST['newpassword']);
		$confirm_password	= empty($_POST['confirm_password'])		? '' : trim($_POST['confirm_password']);
		$email				= empty($_POST['email'])				? '' : trim($_POST['email']);
		$sex				= empty($_POST['sex'])					? 0: intval($_POST['sex']);
		$sex				= in_array($sex, array(0, 1, 2))		? $sex : 0;
		$birthday			= empty($_POST['birthday'])			 	? '' : $_POST['birthday'];
		$rank				= empty($_POST['user_rank'])			? 0: intval($_POST['user_rank']);
		$credit_line		= empty($_POST['credit_line'])			? 0: trim($_POST['credit_line']);

		/* 验证参数的合法性*/
		/* 邮箱*/
		if (!@ereg("^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+",$email)) {
			$this->showmessage(RC_Lang::lang('js_languages/invalid_email') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 密码 */
		if (!empty($password)) {
			if (!preg_match("/^[A-Za-z0-9]+$/",$password)){
				$this->showmessage(RC_Lang::lang('js_languages/chinese_password') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
			if (empty($confirm_password)) {
				$this->showmessage(RC_Lang::lang('js_languages/no_confirm_password') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
			if ($password != $confirm_password ) {
				$this->showmessage(RC_Lang::lang('js_languages/password_not_same') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
			if (strlen($password) < 6 || strlen($confirm_password) < 6) {
				$this->showmessage(RC_Lang::lang('js_languages/password_len_err') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
			if (preg_match("/ /" , $password)) {
				$this->showmessage(RC_Lang::lang('js_languages/passwd_balnk') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
		}
	
		/* 信用额度*/
		if (!is_numeric($credit_line) || $credit_line < 0 ) {
			$this->showmessage(RC_Lang::lang('js_languages/credit_line') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}

		if (!empty($password)) {
			$data = array('ec_salt' => '0');
			$this->db_user->where(array('user_id' => $user_id))->update($data);
		}
		/* 更新用户扩展字段的数据 */
		$fields_arr = $this->db_reg_fields->field('id')->where(array('type' => 0, 'display' => 1))->order(array('dis_order' => 'asc' , 'id' => 'asc'))->select();

		/* 循环更新扩展用户信息 */
		foreach ($fields_arr AS $val) {
			$extend_field_index = 'extend_field' . $val['id'];
			if(isset($_POST[$extend_field_index])) {
				$temp_field_content = strlen($_POST[$extend_field_index]) > 100 ? mb_substr($_POST[$extend_field_index], 0, 99) : $_POST[$extend_field_index];
				$sql_one = $this->db_reg_extend_info->find(array('reg_field_id' => $val['id'] , 'user_id' => $user_id));
				/* 如果之前没有记录，则插入 */
				if($sql_one) {
					$data = array('content' => $temp_field_content);
					$this->db_reg_extend_info->where(array('reg_field_id' => $val['id'] , 'user_id' => $user_id))->update($data);
				} else {
					$data = array(
						'user_id'		=> $user_id,
						'reg_field_id'	=> $val['id'],
						'content'		=> $temp_field_content,
					);
					$this->db_reg_extend_info->insert($data);
				}
			}
		}	

		/* 更新会员的其它信息 */
		$other =array();
		if ($password) {
			$other['password']	= md5($password);
		}

		$other['user_name']		= $username;
		$other['email']			= $email;
		$other['credit_line']	= $credit_line;
		$other['sex']			= $sex;
		$other['birthday']		= $birthday;
		$other['user_rank']		= $rank;
		$other['msn']			= isset($_POST['extend_field1']) ? htmlspecialchars(trim($_POST['extend_field1'])) : '';
		$other['qq']			= isset($_POST['extend_field2']) ? htmlspecialchars(trim($_POST['extend_field2'])) : '';
		$other['office_phone']	= isset($_POST['extend_field3']) ? htmlspecialchars(trim($_POST['extend_field3'])) : '';
		$other['home_phone']	= isset($_POST['extend_field4']) ? htmlspecialchars(trim($_POST['extend_field4'])) : '';
		$other['mobile_phone']	= isset($_POST['extend_field5']) ? htmlspecialchars(trim($_POST['extend_field5'])) : '';
		
		$this->db_user->where(array('user_id' => $user_id))->update($other);
		
		/* 记录管理员操作 */
		ecjia_admin::admin_log($username, 'edit', 'users');

		/* 提示信息 */
		$links[0]['text']	= '返回会员列表';
		$links[0]['href']	= RC_Uri::url('user/admin/init');
		$this->showmessage(__('会员名')."[ ". $username ." ]".__('编辑成功！') , ecjia_admin::MSGTYPE_JSON | ecjia_admin::MSGSTAT_SUCCESS , array('links' => $links , 'pjaxurl' => RC_Uri::url('user/admin/edit' , "id=$user_id")));
	}
	
	/**
	 * 用户详情页面
	 */
	public function info()
	{
		/* 检查权限 */
		$this->admin_priv('users_manage');
	
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('会员详情')));
		ecjia_screen::get_current_screen()->add_help_tab( array(
		'id'		=> 'overview',
		'title'		=> __('概述'),
		'content'	=>
		'<p>' . __('欢迎访问ECJia智能后台会员详情页面，可以在此页面查看相应的会员详细信息。') . '</p>'
		) );
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
		'<p><strong>' . __('更多信息:') . '</strong></p>' .
		'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:会员列表#.E8.AF.A6.E7.BB.86.E4.BF.A1.E6.81.AF" target="_blank">关于会员详情帮助文档</a>') . '</p>'
		);
		
		$id = $_REQUEST['id'];
		$keywords = $_REQUEST['keywords'];

		if (!empty($keywords)) {
			$row = $this->db_user->find("user_id = '$keywords' or user_name = '$keywords' or email = '$keywords'");
		} else {
			$row = $this->db_user->find(array('user_id' => $id));
		}

		if(empty($row)){
			$this->showmessage ( '没有查询到该会员的信息！' , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR );
		}
		
		/* 获得用户等级名 */
		$user['user_rank'] = $this->db_user_rank->where(array('rank_id' => $row['user_rank']))->get_field('rank_name');
	
		if ($row) {
			$user['user_id']				= $row['user_id'];
			$user['user_name']				= $row['user_name'];
			$user['email']					= $row['email'];
			$user['sex']					= $row['sex'];
			$user['reg_time']				= RC_Time::local_date(ecjia::config('time_format') , $row['reg_time']);
			$user['birthday']				= date($row['birthday']);
			$user['pay_points']				= $row['pay_points'];
			$user['rank_points']			= $row['rank_points'];
			$user['user_money']				= $row['user_money'];
			$user['frozen_money']			= $row['frozen_money'];
			$user['credit_line']			= $row['credit_line'];
			$user['formated_user_money']	= price_format($row['user_money']);
			$user['formated_frozen_money']	= price_format($row['frozen_money']);
			$user['parent_id']				= $row['parent_id'];
			$user['parent_username']		= $row['parent_username'];
			$user['qq']						= $row['qq'];
			$user['msn']					= $row['msn'];
			$user['office_phone']			= $row['office_phone'];
			$user['home_phone']				= $row['home_phone'];
			$user['mobile_phone']			= $row['mobile_phone'];
			$user['is_validated']			= $row['is_validated'] == 0 ? '未验证' : '已验证';
			$user['last_time']				= $row['last_time'] == '0000-00-00 00:00:00' ? '1970-01-01 00:00:00':$row['last_time'];
			$user['last_ip']				= $row['last_ip'];
			
			/* 用户地址列表*/
			$field = array("ua.*,IF(address_id=".$row['address_id'].",1,0) as default_address,IFNULL(c.region_name, '') as country_name, IFNULL(p.region_name, '') as province_name,IFNULL(t.region_name, '') as city_name,IFNULL(d.region_name, '') as district_name");

			$address_list = $this->db_view->field($field)->where(array('user_id' => $row['user_id'] ))->order('default_address desc')->limit(5)->select();
		
			/* 查找用户前5条订单 */
			$order = $this->db_order->where(array('user_id' => $row['user_id'] ))->order(array('add_time' => 'desc'))->limit(5)->select();
			RC_Lang::load('orders/order');
			foreach ($order as $k => $v) {
				$order[$k]['add_time']	 = RC_Time::local_date(ecjia::config('time_format') , $v['add_time']);
				$order[$k]['status']	 = RC_Lang::lang("os/$v[order_status]") . ',' . RC_Lang::lang("ps/$v[pay_status]") . ',' . RC_Lang::lang("ss/$v[shipping_status]");
			}
		}
	
		$this->assign('ur_here',		__("会员详情"));
		$this->assign('action_link',	array('text' => RC_Lang::lang('03_users_list') , 'href' => RC_Uri::url('user/admin/init')));
		$this->assign('user',			$user);
		$this->assign('order_list',		$order);
		$this->assign('address_list',	$address_list);
		$this->assign_lang();
		$this->display('user_info.dwt');
		
		
// 		$users=& init_users();
// 		$user = $users->get_user_info($row['user_name']);
	}
	
	
	/**
	 * 批量删除会员帐号
	 */
	public function batch_remove()
	{
		/* 检查权限 */
		$this->admin_priv('users_drop' , ecjia::MSGTYPE_JSON);
		
		/* 对批量删除会员帐号进行权限检查  BY：MaLiuWei  START */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 对批量删除会员帐号进行权限检查  BY：MaLiuWei  END */
		if (isset($_POST['checkboxes'])) {
			$idArr = explode(',' , $_POST['checkboxes']);
			$count = count($idArr);
			$data = $this->db_user->field('user_name')->where(array('user_id' => $idArr))->select();

			/* 通过插件来删除用户 */
			RC_Loader::load_app_class('integrate', 'user', false);
			$user = integrate::init_users();
			$user->remove_user($idArr); //已经删除用户所有数据
			
			foreach ($data as $row) {
				ecjia_admin::admin_log($row['user_name'] , 'batch_remove' , 'users');
			}		
			
			$this->showmessage(sprintf(RC_Lang::lang('batch_remove_success') , $count) , ecjia_admin::MSGTYPE_JSON | ecjia_admin::MSGSTAT_SUCCESS , array('pjaxurl' => RC_Uri::url('user/admin/init')));
		} else {
			$this->showmessage(RC_Lang::lang('no_select_user') , ecjia_admin::MSGTYPE_JSON | ecjia_admin::MSGSTAT_ERROR);
		}

// 		$links[] = array('text' => RC_Lang::lang('go_back') , 'href' => RC_Uri::url('user/admin/init'));
//		$sql = "SELECT user_name FROM " . $ecs->table('users') . " WHERE user_id " . db_create_in($_POST['checkboxes']);
//		$col = $db->getCol($sql);

//		sys_msg(sprintf(RC_Lang::lang('batch_remove_success'), $count), 0, $lnk);
//		sys_msg(RC_Lang::lang('no_select_user'), 0, $lnk);

/* 通过插件来删除用户 */
//		$users =& init_users();
//		$users->remove_user($col);
	}
	
	/**
	 * 编辑用户名
	 */
	public function edit_username()
	{
		/* 检查权限 */
		$this->admin_priv('users_manage', ecjia::MSGTYPE_JSON);
		
		/* 对编辑用户名行权限检查  BY：MaLiuWei  START */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 对编辑用户名进行权限检查  BY：MaLiuWei  END */
		$username	= trim($_POST['value']);
		$id			= intval($_POST['pk']);

		if (empty($username)) {
			$this->showmessage(__('会员名不为空') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR );
		}
		
		/* 编辑名称 根据id获取之前的名称 */
		$old_name = $this->db_user->where(array('user_id' => $id))->get_field('user_name');
		if (!empty($username)) {
			if ($username != $old_name) {
				if ($this->db_user->where(array('user_name' => $username))->count() == 0) {
					if ($this->db_user->where(array('user_id' => $id))->update(array('user_name' => $username))) {
						ecjia_admin::admin_log(addslashes($username), 'edit', 'users');
						$this->showmessage(RC_Lang::lang('edit_success') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
					}
				} else {
					$this->showmessage(RC_Lang::lang('username_exists') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR );
				}
			}
		} else {
			$this->showmessage(RC_Lang::lang('not_empty') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR );
		}

	}
	
	/**
	 * 编辑email
	 */
	public function edit_email()
	{
		/* 检查权限 */
		$this->admin_priv('users_manage', ecjia::MSGTYPE_JSON);
		
		/* 对编辑email行权限检查  BY：MaLiuWei  START */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 对编辑email进行权限检查  BY：MaLiuWei  END */
		$id	= intval($_REQUEST['pk']);
		$email	= trim($_REQUEST['value']);

		/* 验证邮箱*/
		if (!@ereg("^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+",$email)) {
			$this->showmessage(RC_Lang::lang('js_languages/invalid_email') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}

		/* 编辑email 根据id获取之前的email */
		$old_email = $this->db_user->where(array('user_id' => $id))->get_field('email');
		if (!empty($email)) {
			if ($email != $old_email) {
				if ($this->db_user->where(array('email' => $email))->count() == 0) {
					if ($this->db_user->where(array('user_id' => $id))->update(array('email' => $email))) {
						
						$user_name = $this->db_user->where(array('user_id' => $id))->get_field('user_name');
						ecjia_admin::admin_log($user_name.'，'.'修改邮件地址为 '.addslashes($email), 'edit', 'users');
						
						$this->showmessage(RC_Lang::lang('edit_success') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
					}
					
				} else {
					$this->showmessage(RC_Lang::lang('email_exists') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR );
				}
			}
		} else {
			$this->showmessage(RC_Lang::lang('not_empty') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR );
		}
	}

	/**
	 * 删除会员帐号
	 */
	public function remove()
	{
		/* 检查权限 */
		$this->admin_priv('users_drop' , ecjia::MSGTYPE_JSON);
		
		$user_id = $_GET['id'];
		$username = $this->db_user->where(array('user_id' => $user_id))->get_field('user_name');
		
		RC_Loader::load_app_class('integrate', 'user', false);
		$user = integrate::init_users();
    	$user->remove_user($username); //已经删除用户所有数据
    	
// 		$this->db_user->where(array('user_id' => $user_id))->delete();
		/* 记录管理员操作 */
		ecjia_admin::admin_log(addslashes($username), 'remove', 'users');
		
		/* 提示信息 */
		$this->showmessage(__('会员名')."[ ". $username ." ]".__('删除成功！') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);

		/* 通过插件来删除用户 */
//		$users = &init_users();
//		$users->remove_user($username); //已经删除用户所有数据
	}
	
	/**
	 * 收货地址查看
	 */
	public function address_list()
	{
		/* 检查权限 */
		$this->admin_priv('users_manage');
	
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('收货地址')));
		ecjia_screen::get_current_screen()->add_help_tab( array(
		'id'		=> 'overview',
		'title'		=> __('概述'),
		'content'	=>
		'<p>' . __('欢迎访问ECJia智能后台收货地址页面，可以在此查看相应会员的收货地址信息。') . '</p>'
		) );
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
		'<p><strong>' . __('更多信息:') . '</strong></p>' .
		'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:会员列表#.E6.94.B6.E8.B4.A7.E5.9C.B0.E5.9D.80" target="_blank">关于收货地址帮助文档</a>') . '</p>'
		);
		
		$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		
		$user_name = $this->db_user->where(array('user_id' => $id))->get_field('user_name');
		
		$act = intval($_GET['type']) ;
		
		/* 取用户默认地址id */
		$address_id = $this->db_user_address->find(array('u.user_id' => $id));
		$default_address_count = empty($address_id['address_id']) ? 0 : 1;
		
		/* 用户地址列表*/
		if ($address_id) {
			$field = array("ua.*,IF(address_id=".$address_id['address_id'].",1,0) as default_address,IFNULL(c.region_name, '') as country_name, IFNULL(p.region_name, '') as province_name,IFNULL(t.region_name, '') as city_name,IFNULL(d.region_name, '') as district_name");
			$order = array('default_address' => 'desc');
		} 
		
		$row = $this->db_view->field($field)->where(array('user_id' => $id ))->order($order)->select();
		$count = count($row);

		if ($act) {
			foreach ($row as $k => $v) {
				if ($address_id['address_id'] != $v['address_id']) {
					unset($row[$k]);
				}
			}
		}

		$this->assign('count',			$count);
		$this->assign('default_count',  $default_address_count);
		$this->assign('address_list',	$row);
		$this->assign('id',			 	$id);
		$this->assign('user_name',		$user_name);
		$this->assign('ur_here',		__('收货地址'));
		$this->assign('action_link',	array('text' => RC_Lang::lang('03_users_list') , 'href' => RC_Uri::url('user/admin/init')));
		$this->assign_lang();
		$this->display('user_address_list.dwt');
		
// 		TODO : 同表只能连一次
//		$sql = "SELECT ua.*, c.region_name AS country_name, p.region_name AS province, t.region_name AS city_name, d.region_name AS district_name ".
//				" FROM " .$ecs->table('user_address'). " as ua ".
//				" LEFT JOIN " . $ecs->table('region') . " AS c ON c.region_id = ua.country " .
//				" LEFT JOIN " . $ecs->table('region') . " AS p ON p.region_id = ua.province " .
//				" LEFT JOIN " . $ecs->table('region') . " AS t ON t.region_id = ua.city " .
//				" LEFT JOIN " . $ecs->table('region') . " AS d ON d.region_id = ua.district " .
//				" WHERE user_id='$id'";
//		$address = $db->getAll($sql);
		
//		$this->assign('action_link',	array('text' => $_LANG['03_users_list'], 'href'=>'index.php?m=user&c=admin&a=init&' . list_link_postfix()));
//		$this->assign('action_link',	array('text' => RC_Lang::lang('03_users_list'), 'href'=>'index.php?m=user&c=admin&a=init'));
	}
	
	/**
	 * 脱离推荐关系
	 */
// 	public function remove_parent()
// 	{
// 		/* 检查权限 */
// 		$this->admin_priv('users_manage');
		
// 		$data = array('parent_id' => 0);
// 		$this->db_user->where(array('user_id'=>$_GET['id']))->update($data);

// 		/* 记录管理员操作 */
// 		$username = $this->db_user->where(array('user_id'=>$_GET['id']))->get_field('user_name');
// 		ecjia_admin::admin_log(addslashes($username), 'edit', 'users');

// 		/* 提示信息 */
// 		$link[] = array('text' => RC_Lang::lang('go_back'), 'href' => RC_Uri::url('user/admin/init'));
// 		$this->showmessage(sprintf(RC_Lang::lang('update_success'), $username), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);

// //		$sql = "UPDATE " . $ecs->table('users') . " SET parent_id = 0 WHERE user_id = '" . $_GET['id'] . "'";
// //		$db->query($sql);
		
// //		$sql = "SELECT user_name FROM " . $ecs->table('users') . " WHERE user_id = '" . $_GET['id'] . "'";
// //		$username = $db->getOne($sql);
		
// //		$link[] = array('text' => RC_Lang::lang('go_back'), 'href'=>'index.php?m=user&c=admin&a=init');
// //		sys_msg(sprintf($_LANG['update_success'], $username), 0, $link);	
// 	}

	/**
	 * 查看用户推荐会员列表
	 */
// 	public function aff_list()
// 	{
// 		/* 检查权限 */
// 		$this->admin_priv('users_manage');

// 		$auid = $_GET['auid'];
// 		$user_list['user_list'] = array();

// 		$affiliate = unserialize(ecjia::config('affiliate'));
// 		$this->assign('affiliate', $affiliate);

// 		empty($affiliate) && $affiliate = array();

// 		$num = count($affiliate['item']);
// 		$up_uid = "'$auid'";
// 		$all_count = 0;
// 		for ($i = 1; $i<=$num; $i++) {
// 			$count = 0;
// 			if ($up_uid) {
// 				$data = $this->db_user->field('user_id')->in(array('parent_id'=>$up_uid))->select();	
// 				$up_uid = '';
// 				foreach ($data as $key => $rt) {
// 					$up_uid .= $up_uid ? ",'$rt[user_id]'" : "'$rt[user_id]'";
// 					$count++;
// 				}
// 			}
// 			$all_count += $count;
// 			if ($count) {
// 				$data = $this->db_user->field('user_id,user_name,'.$i.'|level,email,is_validated,user_money,frozen_money,rank_points,pay_points,reg_time')->in(array('user_id' => $up_uid))->order(array('level'=>'asc' ,'user_id'=>'asc'))->select();	
// 				$user_list['user_list'] = array_merge($user_list['user_list'], $data);
// 			}
// 		}
// 		$temp_count = count($user_list['user_list']);
// 		for ($i=0; $i<$temp_count; $i++) {
// 			$user_list['user_list'][$i]['reg_time'] = RC_Time::local_date(ecjia::config('date_format'), $user_list['user_list'][$i]['reg_time']);
// 		}

// 		$user_list['record_count']		= $all_count;
// 		$this->assign('ur_here',		RC_Lang::lang('03_users_list'));
// 		$this->assign('action_link',	array('text' => RC_Lang::lang('back_note'), 'href'=>RC_Uri::url('m=user&c=edit&id=$auid')));
// 		$this->assign('user_list',		$user_list['user_list']);
// 		$this->assign('record_count',	$user_list['record_count']);
// 		$this->assign('full_page',		1);

// 		$this->display('affiliate_list.htm');

// //		$sql = "SELECT user_id FROM " . $ecs->table('users') . " WHERE parent_id IN($up_uid)";
// //		$query = $db->query($sql);
// //		while ($rt = $db->fetch_array($query))

// //		$sql = "SELECT user_id, user_name, '$i' AS level, email, is_validated, user_money, frozen_money, rank_points, pay_points, reg_time ".
// //				" FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id IN($up_uid)" .
// //				" ORDER by level, user_id";

// //		$user_list['user_list'] = array_merge($user_list['user_list'], $db->getAll($sql));

// //		$this->assign('action_link',array('text' => RC_Lang::lang('back_note'), 'href'=>"users.php?act=edit&id=$auid"));
// 	}

}

// end