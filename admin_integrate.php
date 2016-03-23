<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * ECJIA 第三方程序会员数据整合插件管理程序
 */
class admin_integrate extends ecjia_admin {

	private $db_user;	
	private $integrate;
	
	public function __construct() {
		parent::__construct();

		RC_Lang::load('integrate');
		
		RC_Loader::load_app_func('user');
		
		$this->integrate = RC_Loader::load_app_class('integrate', 'user');

		$this->db_user = RC_Loader::load_app_model('users_model');

		/* 加载所全局 js/css */
		RC_Script::enqueue_script('jquery-validate');
		RC_Script::enqueue_script('jquery-form');

		RC_Script::enqueue_script('smoke');

		RC_Script::enqueue_script('jquery-chosen');
		RC_Style::enqueue_style('chosen');
		/* 编辑页 js/css */
		RC_Script::enqueue_script('jquery-uniform');
		RC_Style::enqueue_style('uniform-aristo');
		
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('会员整合') , RC_Uri::url('user/admin_integrate/init')));
		
		RC_Script::enqueue_script('integrate_list', RC_Uri::home_url('content/apps/user/statics/js/integrate_list.js'));
	}

	/**
	 * 会员数据整合插件列表
	 */
	public function init() {
	    $this->admin_priv('integrate_users');
		
		RC_Script::enqueue_script('jquery-dataTables-bootstrap');
		RC_Script::enqueue_script('jquery-dataTables-sorting');
		
		ecjia_screen::get_current_screen()->remove_last_nav_here();
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('会员整合')));
		ecjia_screen::get_current_screen()->add_help_tab( array(
		'id'		=> 'overview',
		'title'		=> __('概述'),
		'content'	=>
		'<p>' . __('欢迎访问ECJia智能后台会员整合页面，系统中所有的会员整合都会显示在此列表中。') . '</p>'
		) );
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
		'<p><strong>' . __('更多信息:') . '</strong></p>' .
		'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:会员整合" target="_blank">关于会员整合帮助文档</a>') . '</p>'
		);
		
		$integrate_list['ecjia'] = array(
		        'format_name'           => 'ECJia',
		        'code'   	            => 'ecjia',
		        'format_description'   	=> 'ECJia默认会员系统',
		    );
		
		$list = $this->integrate->integrate_list();
		if (is_array($list)) {
		    $integrate_list = array_merge($integrate_list, $list);
		}

		foreach ($integrate_list as &$integrate) {
		    $code = ecjia::config('integrate_code') == 'ecshop' ? 'ecjia' : ecjia::config('integrate_code');
		    if ($integrate['code'] == $code) {
		        $integrate['activate'] = 1;
		    } else {
		        $integrate['activate'] = 0;
		    }
		}
		
		$this->assign('ur_here',			__('会员整合'));
		$this->assign('integrate_list',		$integrate_list);
		$this->assign_lang();
		$this->display('integrates_list.dwt');
	}
	
	
	/**
	 * 设置会员数据整合插件
	 */
	public function setup() {
	    $this->admin_priv('integrate_users');
	
	    $code = strval($_GET['code']);
	    
	    if ($code == 'ecshop' || $code == 'ecjia') {
	        $this->showmessage(__('当您采用ECJia会员系统时，无须进行设置。'), ecjia::MSGTYPE_HTML | ecjia::MSGSTAT_INFO);
	    }
	
	    $cfg = unserialize(ecjia::config('integrate_config'));

	    ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('设置会员数据整合插件')));

	    
	    if ($code != 'ucenter') {
	        $this->assign('set_list',     integrate::charset_list());
	        $cfg['integrate_url'] = "http://";
	    }
	    

	    $this->assign('ur_here',      __('设置会员数据整合插件'));
	    $this->assign('code',         $code);
	    $this->assign('cfg',		  $cfg);
	    $this->assign('form_action',  RC_Uri::url('user/admin_integrate/save_config'));
	    $this->assign('action_link',  array('text' => '返回会员整合', 'href' => RC_Uri::url('user/admin_integrate/init')));
	    $this->assign_lang();
	    $this->display('integrates_setup.dwt');
	}
	
	
	/**
	 * 启用会员数据整合插件
	 */
	public function activate() {
        $this->admin_priv('integrate_users');

		$code = strval($_GET['code']);

		if ($code == 'ucenter') {
		    ecjia_config::instance()->write_config('integrate_code', 'ucenter'); 
		} elseif ($code == 'ecshop') {
			ecjia_config::instance()->write_config('integrate_code', 'ecshop');
		} elseif ($code == 'ecjia') {
		    ecjia_config::instance()->write_config('integrate_code', 'ecjia');
		} else {
		    //如果有标记，清空标记
			$data = array(
					'flag' => 0,
					'alias' => ''
			);
			$this->db_user->where(array('flag' => array('gt' > 0)))->update($data);
			
			ecjia_config::instance()->write_config('integrate_code', $code);
		}
		

		ecjia_config::instance()->write_config('points_rule', '');
		
		$this->showmessage(__('成功启用会员整合插件！') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('user/admin_integrate/init')));
	}
	

	
	/**
	 * 保存整合填写的配置资料
	 */
	public function save_config() {
		$code = strval($_POST['code']);

		if ($code != 'ecjia' && $code != 'ucenter' && $code != 'ecshop') {
		    $this->showmessage(__('目前仅支持UCenter方式的会员整合。'), ecjia::MSGSTAT_ERROR | ecjia::MSGTYPE_JSON);
		}
		
		$cfg = unserialize(ecjia::config('integrate_config'));

		$_POST['cfg']['quiet'] = 1;
		
		/* 合并数组，保存原值 */
		$cfg = array_merge($cfg, $_POST['cfg']);
		
		/* 直接保存修改 */
		if (integrate::save_integrate_config($code, $cfg)) {	
			$this->showmessage(RC_Lang::lang('save_ok'), ecjia::MSGSTAT_SUCCESS | ecjia::MSGTYPE_JSON);
		} else {			
			$this->showmessage(RC_Lang::lang('save_error'), ecjia::MSGSTAT_ERROR | ecjia::MSGTYPE_JSON);
		}
	}

}


// end