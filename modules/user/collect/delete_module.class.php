<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 用户删除收藏商品
 * @author royalwang
 *
 */
class delete_module extends api_front implements api_interface {
    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {	
    	$this->authSession();	
    	
		$collection_id = $this->requestData('rec_id');
		$goods_id = $this->requestData('goods_id', 0);
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