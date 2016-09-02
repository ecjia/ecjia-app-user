<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 用户收藏商品
 * @author royalwang
 *
 */
class create_module extends api_front implements api_interface {
    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {	
    	$this->authSession();	
		
		$goods_id = _POST('goods_id', 0);
		if (!$goods_id) {
			EM_Api::outPut(101);
		}
		
		RC_Loader::load_app_func('goods', 'goods');
		$goods = get_goods_info($goods_id);

		if (!$goods) {
			EM_Api::outPut(13);
		}
		/* 检查是否已经存在于用户的收藏夹 */
		
		$db_collect_goods = RC_Loader::load_app_model('collect_goods_model','goods');
        $count = $db_collect_goods->where(array('user_id' => $_SESSION['user_id'] , 'goods_id' => $goods_id))->count();

		if ($count > 0) {
			EM_Api::outPut(10007);
		} else {
			$time = RC_Time::gmtime();
			$data = $db_collect_goods->insert(array('user_id' => $_SESSION['user_id'] , 'goods_id' => $goods_id , 'add_time' => $time));
			if ($data === false) {
				EM_Api::outPut(8);
			} else {
				EM_Api::outPut(array());
			}
		}	
	}
}


// end