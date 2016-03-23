<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 会员统计
 * @author will.chen
 *
 */
class user_user_stats_api extends Component_Event_Api {
    
    public function call(&$options) {
		$db_user = RC_Loader::load_app_model ('users_model', 'user');
        
        /* 获取会员总数*/
		$stats['total'] = $db_user->count();
		
		return $stats;
    }
    
    
    
}

// end