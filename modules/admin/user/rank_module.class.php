<?php
defined('IN_ECJIA') or exit('No permission resources.');

/**
 * 会员
 * @author will
 *
 */
class rank_module extends api_admin implements api_interface {
    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {
    		
		$this->authadminSession();
		$ecjia = RC_Loader::load_app_class('api_admin', 'api');
		$db_user_rank = RC_Model::model('user/user_rank_model');
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