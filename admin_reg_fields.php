<?php

/**
 * ECJIA 会员注册项管理程序
*/

defined('IN_ECJIA') or exit('No permission resources.');

class admin_reg_fields extends ecjia_admin {
	private $db_reg_fields;
	private $db_reg_extend_info;
	public function __construct() {
		parent::__construct();
		
		RC_Lang::load('reg_fields');
		RC_Loader::load_app_func('user');
		RC_Loader::load_app_func('common','goods');
		$this->db_reg_fields		= RC_Loader::load_app_model('reg_fields_model','user');
		$this->db_reg_extend_info	= RC_Loader::load_app_model('reg_extend_info_model','user');
		
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
		RC_Script::enqueue_script('user_info', RC_App::apps_url('statics/js/user_info.js' , __FILE__));

		$reg_field_jslang = array(
				'reg_field_name_required'	=> __('请输入注册项名称！'),
				'reg_field_order_required'	=> __('请输入排序！')
		);
		RC_Script::localize_script( 'user_info' , 'reg_jslang' , $reg_field_jslang );
		
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('会员注册项设置') , RC_Uri::url('user/admin_reg_fields/init')));

	}
	
	/**
	 * 会员注册项列表
	 */
	public function init()
	{
		$this->admin_priv('reg_fields');
		
		ecjia_screen::get_current_screen()->remove_last_nav_here();
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('会员注册项设置')));
		ecjia_screen::get_current_screen()->add_help_tab( array(
		'id'		=> 'overview',
		'title'		=> __('概述'),
		'content'	=>
		'<p>' . __('欢迎访问ECJia智能后台会员注册项列表页面，系统中所有的会员注册项都会显示在此列表中。') . '</p>'
		) );
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
		'<p><strong>' . __('更多信息:') . '</strong></p>' .
		'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:会员注册项设置" target="_blank">关于会员注册项列表帮助文档</a>') . '</p>'
		);
		
		$fields = $this->db_reg_fields->order(array('dis_order' => 'asc' , 'id' => 'asc'))->select();
		
		$this->assign('ur_here',		RC_Lang::lang('21_reg_fields'));
		$this->assign('action_link',	array('text' => RC_Lang::lang('add_reg_field') , 'href' => RC_Uri::url('user/admin_reg_fields/add')));
		$this->assign('reg_fields',		$fields);
		
		$this->assign_lang();
		$this->display('reg_fields_list.dwt');
	}
	
	
	/**
	 * 添加会员注册项
	 */
	public function add()
	{
		$this->admin_priv('reg_fields');
		
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('添加会员注册项')));
		ecjia_screen::get_current_screen()->add_help_tab( array(
		'id'		=> 'overview',
		'title'		=> __('概述'),
		'content'	=>
		'<p>' . __('欢迎访问ECJia智能后台添加会员注册项页面，可以在此页面添加会员注册项。') . '</p>'
		) );
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
		'<p><strong>' . __('更多信息:') . '</strong></p>' .
		'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:会员注册项设置" target="_blank">关于添加会员注册项帮助文档</a>') . '</p>'
		);
		
		$reg_field['reg_field_order']		= 100;
		$reg_field['reg_field_display']		= 1;
		$reg_field['reg_field_need']		= 1;
		$this->assign('reg_field',			$reg_field);
		$this->assign('ur_here',			RC_Lang::lang('add_reg_field'));
		$this->assign('action_link',		array('text' => RC_Lang::lang('21_reg_fields') , 'href' => RC_Uri::url('user/admin_reg_fields/init')));
		$this->assign('form_action',		RC_Uri::url('user/admin_reg_fields/insert'));
		$this->assign_lang();
		$this->display('reg_fields_edit.dwt');
	}
	
	/**
	 * 增加会员注册项到数据库
	 */
	public function insert()
	{
		
		$this->admin_priv('reg_fields' , ecjia::MSGTYPE_JSON);
		
		/* 取得参数  */
		$field_name		= trim($_POST['reg_field_name']);
		$dis_order		= trim($_POST['reg_field_order']);
		$display		= trim($_POST['reg_field_display']);
		$is_need		= trim($_POST['reg_field_need']);
		
		/* 检查是否存在重名的会员注册项 */
		if ($this->db_reg_fields->where(array('reg_field_name' => $field_name))->count() != 0){
			$this->showmessage(sprintf(RC_Lang::lang('field_name_exist') , $field_name) , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		
		$data = array(
				'reg_field_name'	=> $field_name,
				'dis_order'			=> $dis_order,
				'display'			=> $display,
				'is_need'			=> $is_need,
		);
		$max_id = $this->db_reg_fields->insert($data);
		
		/* 管理员日志 */
		ecjia_admin::admin_log($field_name , 'add' , 'reg_fields');
		
		$links[] = array('text' => RC_Lang::lang('back_list') , 'href' => RC_Uri::url('user/admin_reg_fields/init'));
		$links[] = array('text' => RC_Lang::lang('add_continue') , 'href' => RC_Uri::url('user/admin_reg_fields/add'));
		$this->showmessage(__('注册项')."[ ".$field_name." ]".__('添加成功！') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS , array('links' => $links , 'pjaxurl' => RC_Uri::url('user/admin_reg_fields/edit' , "id=$max_id")));
	}
	
	/**
	 * 编辑会员注册项
	 */
	public function edit()
	{
		
		$this->admin_priv('reg_fields');
		
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('编辑会员注册项')));
		ecjia_screen::get_current_screen()->add_help_tab( array(
		'id'		=> 'overview',
		'title'		=> __('概述'),
		'content'	=>
		'<p>' . __('欢迎访问ECJia智能后台编辑会员注册项页面，可以在此页面编辑相应的会员注册项。') . '</p>'
		) );
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
		'<p><strong>' . __('更多信息:') . '</strong></p>' .
		'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:会员注册项设置" target="_blank">关于编辑会员注册项帮助文档</a>') . '</p>'
		);
		
		
		$reg_field = $this->db_reg_fields->field('id as reg_field_id, reg_field_name, dis_order as reg_field_order, display as reg_field_display, is_need as reg_field_need')->find(array('id' => $_REQUEST['id']));
		
		$this->assign('reg_field',		$reg_field);
		$this->assign('ur_here',		RC_Lang::lang('edit_reg_field'));
		$this->assign('action_link',	array('text' => RC_Lang::lang('21_reg_fields') , 'href' => RC_Uri::url('user/admin_reg_fields/init')));
		$this->assign('form_action',	RC_Uri::url('user/admin_reg_fields/update'));
		$this->assign_lang();
		$this->display('reg_fields_edit.dwt');
	}
	
	/**
	 * 更新会员注册项
	 */
	public function update()
	{
		$this->admin_priv('reg_fields' , ecjia::MSGTYPE_JSON);
		
		/* 取得参数  */
		$field_name		= trim($_POST['reg_field_name']);
		$dis_order		= trim($_POST['reg_field_order']);
		$display		= trim($_POST['reg_field_display']);
		$is_need		= trim($_POST['reg_field_need']);
		$id				= $_POST['id'];
		
		/* 根据id获取之前的名字  */
		$old_name = $this->db_reg_fields->where(array('id' => $id ))->get_field('reg_field_name');
		/* 检查是否存在重名的会员注册项 */
		
		if ($field_name != $old_name) {
			if ($this->db_reg_fields->where(array('reg_field_name' => $field_name))->count() != 0) {
				$this->showmessage(sprintf(RC_Lang::lang('field_name_exist') , $field_name) ,  ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
		}
		
		$data = array(
				'reg_field_name'	=> $field_name,
				'dis_order'			=> $dis_order,
				'display'			=> $display,
				'is_need'			=> $is_need,
		);
		$this->db_reg_fields->where(array('id' => $id))->update($data);
		/* 管理员日志 */
		ecjia_admin::admin_log($field_name , 'edit' , 'reg_fields');
		$links[] = array('text' => RC_Lang::lang('back_list') , 'href' => RC_Uri::url('user/admin_reg_fields/init'));
		$this->showmessage(__('注册项')."[ ".$field_name." ]".__('编辑成功！') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS , array('links' => $links , 'pjaxurl' => RC_Uri::url('user/admin_reg_fields/edit' , "id=$id")));
	}
	
	/**
	 * 删除会员注册项
	 */
	public function remove()
	{

		$this->admin_priv('reg_fields' , ecjia::MSGTYPE_JSON);
		
		$field_id	= intval($_GET['id']);
		$field_name = $this->db_reg_fields->where(array('id' => $field_id))->get_field('reg_field_name');
		
		if ($this->db_reg_fields->where(array('id' => $field_id))->delete()) {
			/* 删除会员扩展信息表的相应信息 */
			$this->db_reg_extend_info->where(array('reg_field_id' => $field_id))->delete();
			ecjia_admin::admin_log(addslashes($field_name) , 'remove' , 'reg_fields');
			$this->showmessage(RC_Lang::lang('drop_success') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
		}	
	}
	
	/**
	 * 编辑会员注册项名称
	 */
	public function edit_name()
	{

		$this->admin_priv('reg_fields' , ecjia::MSGTYPE_JSON);
		
		/* 对编辑会员注册项名称进行权限检查  BY：MaLiuWei  START */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 对编辑会员注册项名称进行权限检查  BY：MaLiuWei  END */
		$id		= intval($_REQUEST['pk']);
		$val	= empty($_REQUEST['value']) ? '' : trim($_REQUEST['value']);
		
		if (empty($val)) {
			$this->showmessage(__('注册项不为空') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR );
		}
		
		/* 验证名称 ,根据id获取之前的名字  */
		$old_name = $this->db_reg_fields->where(array('id' => $id))->get_field('reg_field_name');
		
		if ($val != $old_name) {
			if ($this->db_reg_fields->where(array('reg_field_name' => $val))->count() != 0) {
				$this->showmessage(sprintf(RC_Lang::lang('field_name_exist') , htmlspecialchars($val)) , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR );
			}
		}

		if ($this->db_reg_fields->where(array('id' => $id))->update(array('reg_field_name' => $val))) {
			/* 管理员日志 */
			ecjia_admin::admin_log($val , 'edit' , 'reg_fields');
			$this->showmessage(RC_Lang::lang('edit_success') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
		} else {
			$this->showmessage($this->db_reg_fields->error() , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		} 
	}
	
	/**
	 * 编辑会员注册项排序权值
	 */
	public function edit_order()
	{
		$this->admin_priv('reg_fields', ecjia::MSGTYPE_JSON);
		
		/* 对编辑会员注册项排序权值进行权限检查  BY：MaLiuWei  START */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 对编辑会员注册项排序权值进行权限检查  BY：MaLiuWei  END */
		$id = intval($_REQUEST['pk']);
		$val = isset($_REQUEST['value']) ? trim($_REQUEST['value']) : '' ;

		
		/* 验证参数有效性  */
		if (!is_numeric($val) || empty($val) || $val < 0 || strpos($val, '.') > 0 ) {
			$this->showmessage(RC_Lang::lang('order_not_num') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}

		if ($this->db_reg_fields->where(array('id' => $id))->update(array('dis_order' => $val))) {
			$this->showmessage(RC_Lang::lang('edit_success') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS , array('pjaxurl' => RC_Uri::url('user/admin_reg_fields/init')));
		} else {
			$this->showmessage($this->db_reg_fields->error() , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	}
	
	/**
	 * 修改会员注册项显示状态
	 */
	public function toggle_dis()
	{
		$this->admin_priv('reg_fields', ecjia::MSGTYPE_JSON);
		
		/* 对编辑会员注册项显示状态进行权限检查  BY：MaLiuWei  START */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 对编辑会员注册项显示状态进行权限检查  BY：MaLiuWei  END */
		$id		 = intval($_POST['id']);
		$is_dis = intval($_POST['val']);
		
		if ($this->db_reg_fields->where(array('id' => $id))->update(array('display' => $is_dis))) {
			
			$reg_field_name = $this->db_reg_fields->where(array('id' => $id))->get_field('reg_field_name');
			
			if ($is_dis == 1) {
				$sn = '显示注册项';
			} else {
				$sn = '隐藏注册项';
			}
			ecjia_admin::admin_log($sn.'，'.'注册项名称是 '.$reg_field_name, 'setup' , 'reg_fields');
			
			$this->showmessage(__('操作成功！') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS , array('content' => $is_dis));
		} else {
			$this->showmessage($this->db_reg_fields->error() , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	}
	
	/**
	 * 修改会员注册项必填状态
	 */
	public function toggle_need()
	{
		$this->admin_priv('reg_fields' , ecjia::MSGTYPE_JSON);

		/* 对编辑会员注册项必填状态进行权限检查  BY：MaLiuWei  START */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 对编辑会员注册项必填状态进行权限检查  BY：MaLiuWei  END */
		$id		 = intval($_POST['id']);
		$id			= intval($_POST['id']);
		$is_need	= intval($_POST['val']);
		
		if ($this->db_reg_fields->where(array('id' => $id))->update(array('is_need' => $is_need))) {
			
			$reg_field_name = $this->db_reg_fields->where(array('id' => $id))->get_field('reg_field_name');
				
			if ($is_need == 1) {
				$sn = '设为必填';
			} else {
				$sn = '设为非必填';
			}
			ecjia_admin::admin_log($sn.'，'.'注册项名称是 '.$reg_field_name, 'setup' , 'reg_fields');
			
			$this->showmessage(__('操作成功！') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS , array('content' => $is_need));
		} else {
			$this->showmessage($this->db_reg_fields->error() , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	}
}

// end