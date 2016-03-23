<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 用户删除收藏商品
 * @author royalwang
 *
 */
class delete_module implements ecjia_interface {
	
	public function run(ecjia_api & $api) {
		EM_Api::authSession();
		$collection_id = _POST('rec_id');
		$goods_id = _POST('goods_id', 0);
		if (empty($collection_id) && !goods_id) {
			EM_Api::outPut(101);
		}
		$db_collect_goods = RC_Loader::load_app_model('collect_goods_model','goods');
		
		if ($goods_id > 0) {
			$db_collect_goods->where(array('goods_id' => $goods_id, 'user_id' => $_SESSION['user_id']))->delete();
		} else {
			$collection_id = explode(',', $collection_id);
			$db_collect_goods->where(array('rec_id' => $collection_id, 'user_id' => $_SESSION['user_id']))->delete();
		}
	
		EM_Api::outPut(array());
	}
}


// end