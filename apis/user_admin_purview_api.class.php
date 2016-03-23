<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 后台权限API
 * @author royalwang
 *
 */
class user_admin_purview_api extends Component_Event_Api {
    
    public function call(&$options) {
        $purviews = array(
            array('action_name' => __('会员账户管理'), 'action_code' => 'account_manage', 'relevance'   => ''),
            array('action_name' => __('会员余额管理'), 'action_code' => 'surplus_manage', 'relevance'   => 'account_manage'),
            array('action_name' => __('会员添加/编辑'), 'action_code' => 'users_manage', 'relevance'   => ''),
            array('action_name' => __('会员删除'), 'action_code' => 'users_drop', 'relevance'   => 'users_manage'),
            array('action_name' => __('会员等级管理'), 'action_code' => 'user_rank', 'relevance'   => ''),
            array('action_name' => __('同步会员数据'), 'action_code' => 'sync_users', 'relevance'   => ''),
            array('action_name' => __('会员数据整合'), 'action_code' => 'integrate_users', 'relevance'   => ''),
        	array('action_name' => __('会员注册项管理'), 'action_code' => 'reg_fields', 'relevance'   => ''),
        );
        
        return $purviews;
    }
}

// end