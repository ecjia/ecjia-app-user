<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * ECJIA 后台会员菜单API
 * @author royalwang
 */
class user_admin_menu_api extends Component_Event_Api {	
	public function call(&$options) {
		$menus = ecjia_admin::make_admin_menu('08_members', __('会员管理'), '', 6);
		$submenus = array(
				ecjia_admin::make_admin_menu('03_users_list', __('会员列表'), RC_Uri::url('user/admin/init'), 1)->add_purview('users_manage'),
				ecjia_admin::make_admin_menu('04_users_add', __('添加会员'), RC_Uri::url('user/admin/add'), 2)->add_purview('users_manage'),
				ecjia_admin::make_admin_menu('05_user_rank_list', __('会员等级'), RC_Uri::url('user/admin_rank/init'), 3)->add_purview('user_rank'),
				ecjia_admin::make_admin_menu('09_user_account', __('充值和提现申请'), RC_Uri::url('user/admin_account/init'), 4)->add_purview('surplus_manage'),
				ecjia_admin::make_admin_menu('10_user_account_manage', __('资金管理'), RC_Uri::url('user/admin_account_manage/init'), 5)->add_purview('account_manage'),
				ecjia_admin::make_admin_menu('21_reg_fields', __('会员注册项设置'), RC_Uri::url('user/admin_reg_fields/init'), 6)->add_purview('reg_fields'),
				ecjia_admin::make_admin_menu('divider', '', '', 50)->add_purview('integrate_users'),
				ecjia_admin::make_admin_menu('menu_user_integrate', __('会员整合'), RC_Uri::url('user/admin_integrate/init'), 51)->add_purview('integrate_users'),
		);
		
		$menus->add_submenu($submenus);
		return RC_Hook::apply_filters('user_admin_menu_api', $menus);;
	}
}

// end