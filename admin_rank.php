<?php
/**
 * ECJIA 会员等级管理程序
*/

defined('IN_ECJIA') or exit('No permission resources.');

class admin_rank extends ecjia_admin {
	private $db_user;
	private $db_user_rank;
	public function __construct() {
		parent::__construct();

		RC_Lang::load('admin_user_rank');
		RC_Loader::load_app_func('user');
		RC_Loader::load_app_func('common', 'goods');
		$this->db_user		= RC_Loader::load_app_model('users_model');
		$this->db_user_rank	= RC_Loader::load_app_model('user_rank_model');
		
		/* 加载全局js/css */
		RC_Script::enqueue_script('jquery-validate');
		RC_Script::enqueue_script('jquery-form');
		/* 列表页 js/css */
		RC_Script::enqueue_script('smoke');
		RC_Script::enqueue_script('bootstrap-editable.min', RC_Uri::admin_url('statics/lib/x-editable/bootstrap-editable/js/bootstrap-editable.min.js'));
		RC_Style::enqueue_style('bootstrap-editable', RC_Uri::admin_url('statics/lib/x-editable/bootstrap-editable/css/bootstrap-editable.css'));
		
		/* 编辑页 js/css */
		RC_Script::enqueue_script('jquery-uniform');
		RC_Style::enqueue_style('uniform-aristo');
		RC_Script::enqueue_script('user_info', RC_App::apps_url('statics/js/user_info.js', __FILE__));
		
		$rank_jslang = array(
			'rank_name_required'		=> __('请输入会员等级名称！'),
			'min_points_required'		=> __('请输入积分下限！'),
			'max_points_required'		=> __('请输入积分上限！'),
			'discount_required'			=> __('请输入折扣率！')
		);
		RC_Script::localize_script( 'user_info', 'rank_jslang', $rank_jslang );
		
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('会员等级'), RC_Uri::url('user/admin_rank/init')));
	}

	/**
	 * 会员等级列表
	 */
	public function init() {
		$this->admin_priv('user_rank');
		
		ecjia_screen::get_current_screen()->remove_last_nav_here();
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('会员等级')));
		ecjia_screen::get_current_screen()->add_help_tab(array(
			'id'		=> 'overview',
			'title'		=> __('概述'),
			'content'	=>
			'<p>' . __('欢迎访问ECJia智能后台会员等级页面，系统中所有的会员等级都会显示在此列表中。') . '</p>'
		));
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
			'<p><strong>' . __('更多信息:') . '</strong></p>' .
			'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:会员等级" target="_blank">关于会员等级帮助文档</a>') . '</p>'
		);
		
		$ranks = $this->db_user_rank->order(array('rank_id' => 'desc'))->select();
		
		$this->assign('ur_here',		RC_Lang::lang('05_user_rank_list'));
		$this->assign('action_link',	array('text' => RC_Lang::lang('add_user_rank'), 'href' => RC_Uri::url('user/admin_rank/add')));
		$this->assign('user_ranks',		$ranks);
		$this->assign_lang();
		$this->display('user_rank_list.dwt');
	}
	
	/**
	 * 添加会员等级
	 */
	public function add() {
		$this->admin_priv('user_rank');
		
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('添加会员等级')));
		ecjia_screen::get_current_screen()->add_help_tab(array(
			'id'		=> 'overview',
			'title'		=> __('概述'),
			'content'	=>
			'<p>' . __('欢迎访问ECJia智能后台添加会员等级页面，可以在此页面添加会员等级。') . '</p>'
		));
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
			'<p><strong>' . __('更多信息:') . '</strong></p>' .
			'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:会员等级#.E6.B7.BB.E5.8A.A0.E4.BC.9A.E5.91.98.E7.AD.89.E7.BA.A7" target="_blank">关于添加会员等级帮助文档</a>') . '</p>'
		);
		
		$rank['rank_special']	= 0;
		$rank['show_price']		= 1;
		$rank['min_points']		= 0;
		$rank['max_points']		= 0;
		$rank['discount']		= 100;
		
		$this->assign('rank',			$rank);
		$this->assign('ur_here',		RC_Lang::lang('add_user_rank'));
		$this->assign('action_link',	array('text' => RC_Lang::lang('05_user_rank_list'), 'href' => RC_Uri::url('user/admin_rank/init')));
		$this->assign('form_action',	RC_Uri::url('user/admin_rank/insert'));
		$this->assign_lang();
		$this->display('user_rank_edit.dwt');
	}
	
	/**
	 * 增加会员等级到数据库
	 */
	public function insert() {
		$this->admin_priv('user_rank', ecjia::MSGTYPE_JSON);
		
		$rank_name		= trim($_POST['rank_name']);
		$special_rank	= isset($_POST['special_rank'])		? intval($_POST['special_rank']) : 0;
		$min_points		= empty($_POST['min_points'])		? 0 : intval($_POST['min_points']);
		$max_points		= empty($_POST['max_points'])		? 0 : intval($_POST['max_points']);
		$discount		= empty($_POST['discount'])			? 0 : intval($_POST['discount']);
		$show_price		= empty($_POST['show_price'])		? 0 : intval($_POST['show_price']);
		
		/* 检查是否存在重名的会员等级 */
		if ($this->db_user_rank->where(array('rank_name' => $rank_name))->count() != 0) {
			$this->showmessage(sprintf(RC_Lang::lang('rank_name_exists'), $rank_name), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		
		/* 非特殊会员组检查积分的上下限是否合理 */
		if ($min_points >= $max_points && $special_rank == 0) {
			$this->showmessage(RC_Lang::lang('js_languages/integral_max_small'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		
		/* 特殊等级会员组不判断积分限制 */
		if ($special_rank == 0) {
			/* 检查下限制有无重复 */
			if ($this->db_user_rank->where(array('min_points' => $min_points))->count() != 0) {
				$this->showmessage(sprintf(RC_Lang::lang('integral_min_exists'), $min_points), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
			/* 检查上限有无重复 */
			if ($this->db_user_rank->where(array('max_points' => $max_points))->count() != 0) {
				$this->showmessage(sprintf(RC_Lang::lang('integral_max_exists'), $max_points), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
		}

		/* 折扣验证 (0-100) */
		if ($discount > 100 || $discount < 0 || !is_numeric($discount) || empty($discount)) {
			$this->showmessage(RC_Lang::lang('notice_discount'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		
		$data = array(
			'rank_name'		=> $rank_name,
			'min_points'	=> $min_points,
			'max_points'	=> $max_points,
			'discount'		=> $discount,
			'special_rank'	=> $special_rank,
			'show_price'	=> $show_price,
		);
		$new_id = $this->db_user_rank->insert($data);
		
		/* 管理员日志 */
		ecjia_admin::admin_log('等级名是 '.$rank_name, 'add', 'user_rank');
		$links[] = array('text' => RC_Lang::lang('back_list'), 'href' => RC_Uri::url('user/admin_rank/init'));
		$links[] = array('text' => RC_Lang::lang('add_continue'), 'href' => RC_Uri::url('user/admin_rank/add'));
		$this->showmessage(__('会员等级')."[ ". $rank_name ." ]".__('添加成功！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('links' => $links, 'pjaxurl' => RC_Uri::url('user/admin_rank/edit', "id=$new_id")));

	}
	
	/**
	 * 编辑会员等级
	 */
	public function edit() {
		$this->admin_priv('user_rank');

	    ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('编辑会员等级')));
	    ecjia_screen::get_current_screen()->add_help_tab(array(
		    'id'		=> 'overview',
		    'title'		=> __('概述'),
		    'content'	=>
		    '<p>' . __('欢迎访问ECJia智能后台编辑会员等级页面，可以在此页面编辑相应的会员等级。') . '</p>'
    	));
	    
	    ecjia_screen::get_current_screen()->set_help_sidebar(
	   	 	'<p><strong>' . __('更多信息:') . '</strong></p>' .
	   	 	'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:会员等级#.E7.BC.96.E8.BE.91.E4.BC.9A.E5.91.98.E7.AD.89.E7.BA.A7" target="_blank">关于编辑会员等级帮助文档</a>') . '</p>'
    	);
	    
	    $id = $_REQUEST['id'];
	    $rank = $this->db_user_rank->find(array('rank_id' => $id));

	    $this->assign('rank',			$rank);
	    $this->assign('ur_here',		RC_Lang::lang('edit_user_rank'));
	    $this->assign('action_link',	array('text' => RC_Lang::lang('05_user_rank_list'), 'href' => RC_Uri::url('user/admin_rank/init')));
	    $this->assign('form_action',	RC_Uri::url('user/admin_rank/update'));
	    $this->assign_lang();
	    $this->display('user_rank_edit.dwt');
	}
	
	/**
	 * 更新会员等级到数据库
	 */
	public function update() {
		$this->admin_priv('user_rank', ecjia::MSGTYPE_JSON);
		
		$id				= $_POST['id']; 
		$rank_name		= trim($_POST['rank_name']);
		$special_rank	= isset($_POST['special_rank'])		? intval($_POST['special_rank']) : 0;
		$min_points		= empty($_POST['min_points'])		? 0 : intval($_POST['min_points']);
		$max_points		= empty($_POST['max_points'])		? 0 : intval($_POST['max_points']);
		$discount		= empty($_POST['discount'])			? 0 : intval($_POST['discount']);
		$show_price		= empty($_POST['show_price'])		? 0 : intval($_POST['show_price']);
		
		/* 验证名称 是否重复  */
		$old_name 		= $_POST['old_name'];
		$old_min 		= $_POST['old_min'];
		$old_max 		= $_POST['old_max'];
		
		if ($rank_name != $old_name) {
			if ($this->db_user_rank->where(array('rank_name' => $rank_name))->count() != 0) {
				$this->showmessage(sprintf(RC_Lang::lang('rank_name_exists'), htmlspecialchars($rank_name)), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR );
			}
		}
		
		/* 非特殊会员组检查积分的上下限是否合理 */
		if ($min_points >= $max_points && $special_rank == 0) {
			$this->showmessage(RC_Lang::lang('js_languages/integral_max_small'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		
		/* 特殊等级会员组不判断积分限制 */
		if ($special_rank == 0) {
			if ($min_points != $old_min) {
				/* 检查下限有无重复 */
				if ($this->db_user_rank->where(array('min_points' => $min_points, 'rank_id' => $id))->count() != 0) {
					$this->showmessage(sprintf(RC_Lang::lang('integral_min_exists'), $min_points), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
				}
			}
			if ($max_points != $old_max) {
				/* 检查上限有无重复 */
				if ($this->db_user_rank->where(array('max_points' => $max_points, 'rank_id' => $id))->count() != 0) {
					$this->showmessage(sprintf(RC_Lang::lang('integral_max_exists'), $max_points), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
				}
			}
		}

		/* 折扣验证 (0-100) */
		if ($discount > 100 || $discount < 0 || !is_numeric($discount) || empty($discount)) {
			$this->showmessage(RC_Lang::lang('notice_discount'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		
		$data = array(
			'rank_name'		=> $rank_name,
			'min_points'	=> $min_points,
			'max_points'	=> $max_points,
			'discount'		=> $discount,
			'special_rank'	=> $special_rank,
			'show_price'	=> $show_price,
		);

		$this->db_user_rank->where(array('rank_id' => $id))->update($data);
		
		/* 管理员日志 */
		ecjia_admin::admin_log('等级名是 '.$rank_name, 'edit', 'user_rank');
		$links[] = array('text' => RC_Lang::lang('back_list'), 'href' => RC_Uri::url('user/admin_rank/init'));
		$this->showmessage(__('会员等级')."[ ". $rank_name ." ]".__('编辑成功！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('links' => $links, 'pjaxurl' => RC_Uri::url('user/admin_rank/edit', "id=$id")));
	}
		
	/**
	 * 删除会员等级
	 */
	public function remove() {

		$this->admin_priv('user_rank', ecjia::MSGTYPE_JSON);
		$rank_id = intval($_GET['id']);
		$rank_name = $this->db_user_rank->where(array('rank_id' => $rank_id))->get_field('rank_name');
		
		if ($this->db_user_rank->where(array('rank_id' => $rank_id))->delete()) {
			/* 更新会员表的等级字段 */
			$this->db_user->where(array('user_rank' => $rank_id))->update(array('user_rank' => 0));
			
			ecjia_admin::admin_log(addslashes($rank_name), 'remove', 'user_rank');	
			$this->showmessage(__('会员等级')."[ ".$rank_name." ]".__('删除成功！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
		}
	}
	
	
	/**
	 * 编辑会员等级名称
	 */
	public function edit_name() {
		$this->admin_priv('user_rank', ecjia::MSGTYPE_JSON);
		
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		$rank_id = intval($_POST['pk']);
		$val	 = trim($_POST['value']);

		/* 验证名称 ,根据id获取之前的名字  */
		$old_name = $this->db_user_rank->where(array('rank_id' => $rank_id))->get_field('rank_name');
		
		if (empty($val)) {
			$this->showmessage(__('会员等级名不为空'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR );
		}
		
		if ($val != $old_name) {
			if ($this->db_user_rank->where(array('rank_name' => $val))->count() != 0) {
				$this->showmessage(sprintf(RC_Lang::lang('rank_name_exists'), htmlspecialchars($val)), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR );
			}
		}

		if ($this->db_user_rank->where(array('rank_id' => $rank_id))->update(array('rank_name' => $val))) {
			/* 管理员日志 */
			ecjia_admin::admin_log('等级名是 '.$val, 'edit', 'user_rank');
			$this->showmessage(RC_Lang::lang('edit_success'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
		} else {
			$this->showmessage($this->db_user_rank->error(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	}
	
	/**
	 * ajax编辑积分下限
	 */
	public function edit_min_points() {
		$this->admin_priv('user_rank', ecjia::MSGTYPE_JSON);
		
		$rank_id = intval($_REQUEST['pk']);
		$val	 = intval($_REQUEST['value']);
		
		/* 验证参数有效性  */
		if (!is_numeric($val) || empty($val) || $val <= 0 || strpos($val, '.') > 0) {
			$this->showmessage(RC_Lang::lang('js_languages/integral_min_invalid'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR );
		}
		/* 查找该ID 对应的积分上限值,验证是否大于上限  */
		$max_points = $this->db_user_rank->where(array('rank_id' => $rank_id))->get_field('max_points');
		if ($val >= $max_points ) {
			$this->showmessage(RC_Lang::lang('js_languages/integral_max_small'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR );
		}
		/* 验证是否存在 */
		if ($this->db_user_rank->where(array('min_points' => $val, 'rank_id' => $rank_id))->count() != 0) {
			$this->showmessage(sprintf(RC_Lang::lang('integral_min_exists'), $val), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR );
		}
		
		if ($this->db_user_rank->where(array('rank_id' => $rank_id))->update(array('min_points' => $val))) {
			$rank_name = $this->db_user_rank->where(array('rank_id' => $rank_id))->get_field('rank_name');
			
			ecjia_admin::admin_log(addslashes($rank_name).'，'.'修改积分下限为 '.$val, 'setup', 'user_rank');
			
			$this->showmessage(RC_Lang::lang('edit_success'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
		} else {
			$this->showmessage($this->db_user_rank->error(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	}
	
	/**
	 * ajax修改积分上限
	 */
	public function edit_max_points() {
		$this->admin_priv('user_rank', ecjia::MSGTYPE_JSON);
		
		$rank_id	= intval($_REQUEST['pk']);
		$val		= intval($_REQUEST['value']);
		
		/* 验证参数有效性  */
		if (!is_numeric($val) || empty($val) || $val <= 0 ) {
			$this->showmessage(RC_Lang::lang('js_languages/integral_min_invalid'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR );
		}
		/* 查找该ID 对应的积分下限值,验证是否大于上限  */
		$min_points = $this->db_user_rank->where(array('rank_id' => $rank_id))->get_field('min_points');
		if ($val <= $min_points ) {
			$this->showmessage(RC_Lang::lang('js_languages/integral_max_small'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR );
		}
		/* 验证是否存在 */
		if ($this->db_user_rank->where(array('max_points' => $val, 'rank_id' => $rank_id))->count() != 0) {
			$this->showmessage(sprintf(RC_Lang::lang('integral_max_exists'), $val), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR );
		}
		
		if ($this->db_user_rank->where(array('rank_id' => $rank_id))->update(array('max_points' => $val))){
			$rank_name = $this->db_user_rank->where(array('rank_id' => $rank_id))->get_field('rank_name');
			
			ecjia_admin::admin_log(addslashes($rank_name).'，'.'修改积分上限为 '.$val, 'setup', 'user_rank');
			
			$this->showmessage(RC_Lang::lang('edit_success'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
		} else {
			$this->showmessage($this->db_user_rank->error(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	}
	
	/**
	 * 修改折扣率
	 */
	public function edit_discount() {
		$this->admin_priv('user_rank', ecjia::MSGTYPE_JSON);
		
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		$rank_id	= intval($_REQUEST['pk']);
		$val		= intval($_REQUEST['value']);
		
		/* 验证参数有效性  */
		if ($val < 1 || $val > 100 || !is_numeric($val) || empty($val)) {
			$this->showmessage(RC_Lang::lang('notice_discount'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR );
		}
		
		if ($this->db_user_rank->where(array('rank_id' => $rank_id))->update(array('discount' => $val))) {
			$rank_name = $this->db_user_rank->where(array('rank_id' => $rank_id))->get_field('rank_name');
			
			ecjia_admin::admin_log(addslashes($rank_name).'，'.'修改折扣率为 '.$val, 'setup', 'user_rank');
			
			$this->showmessage(RC_Lang::lang('edit_success'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
		} else {
			$this->showmessage($this->db_user_rank->error(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	}
	
	/**
	 * 切换是否是特殊会员组
	 */
	public function toggle_special() {
		$this->admin_priv('user_rank', ecjia::MSGTYPE_JSON);
		
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		$rank_id	= intval($_POST['id']);
		$is_special	= intval($_POST['val']);
		
		if ($this->db_user_rank->where(array('rank_id' => $rank_id))->update(array('special_rank' => $is_special))) {
			$rank_name = $this->db_user_rank->where(array('rank_id' => $rank_id))->get_field('rank_name');
			
			if ($is_special == 1) {
				ecjia_admin::admin_log('加入特殊会员组'.'，'.'会员等级名称是 '.$rank_name, 'setup', 'user_rank');
			} else {
				ecjia_admin::admin_log('移出特殊会员组'.'，'.'会员等级名称是 '.$rank_name, 'setup', 'user_rank');
			}
			
			$this->showmessage(__('操作成功！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content' => $is_special, 'pjaxurl' => RC_Uri::url('user/admin_rank/init')));
		} else {
			$this->showmessage($this->db_user_rank->error(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	}
	
	/**
	 * 切换是否显示价格
	 */
	public function toggle_showprice() {
		$this->admin_priv('user_rank', ecjia::MSGTYPE_JSON);
		
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		$rank_id	= intval($_POST['id']);
		$is_show	= intval($_POST['val']);

		if ($this->db_user_rank->where(array('rank_id' => $rank_id))->update(array('show_price' => $is_show))) {
			$rank_name = $this->db_user_rank->where(array('rank_id' => $rank_id))->get_field('rank_name');
			if ($is_show == 1) {
				ecjia_admin::admin_log('显示价格'.'，'.'会员等级名称是 '.$rank_name, 'setup', 'user_rank');
			} else {
				ecjia_admin::admin_log('隐藏价格'.'，'.'会员等级名称是 '.$rank_name, 'setup', 'user_rank');
			}
			$this->showmessage(__('操作成功！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content' => $is_show));
		} else {
			$this->showmessage($this->db_user_rank->error(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	}
}

// end 