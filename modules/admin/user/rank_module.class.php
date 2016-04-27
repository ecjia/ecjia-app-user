<?php
defined('IN_ECJIA') or exit('No permission resources.');

/**
 * 会员
 * @author will
 *
 */
class rank_module implements ecjia_interface {
	
	public function run(ecjia_api & $api) {
		
		$ecjia = RC_Loader::load_app_class('api_admin', 'api');
		$ecjia->authadminSession();
		
		$db_user_rank = RC_Loader::load_app_model('user_rank_model', 'user');
		$result = $db_user_rank->order(array('rank_id' => 'desc'))->select();
		
		$user_rank = array();
		if (!empty($result)) {
			foreach ($result as $val) {
				$user_rank[] = array(
					'rank_id'	=> $val['rank_id'],
					'rank_name' => $val['rank_name'],
					'min_points' => $val['min_points'],
					'max_points' => $val['max_points'], 
				);
			}
		}
		
		return $user_rank;
	}
}


// end