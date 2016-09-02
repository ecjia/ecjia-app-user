<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 验证积分
 * @author royalwang
 *
 */
class integral_module extends api_front implements api_interface {
    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {	
    	$this->authSession();	
    	
		RC_Loader::load_app_func('order','orders');
		
		$integral = $this->requestData('integral', 0);
		if (!$integral) {
			EM_Api::outPut(101);
		}
		
		// 	        // 查询用户有多少积分
		// 	        $flow_points = flow_available_points();  // 该订单允许使用的积分
		
		$integral_to_p =  value_of_integral($integral);
		EM_Api::outPut(array(
			"bonus" => $integral_to_p,
			"bonus_formated" => price_format($integral_to_p, false)
		));
		EM_Api::outPut(101);
	}
}



/**
 * 获得用户的可用积分
 *
 * @access  private
 * @return  integral
 */
// function flow_available_points()
// {
//     $dbview = RC_Loader::load_app_model('cart_goods_viewmodel','cart');
    
// 	if ($_SESSION['user_id']) {
// 		$val = $dbview->join('goods')->find(array('c.user_id' => $_SESSION['user_id'] , 'c.is_gift' => 0 , 'g.integral' => array('gt' => 0) , 'c.rec_type' =>  CART_GENERAL_GOODS));
// 	} else {
// 		$val = $dbview->join('goods')->find(array('c.session_id' => SESS_ID , 'c.is_gift' => 0 , 'g.integral' => array('gt' => 0) , 'c.rec_type' =>  CART_GENERAL_GOODS));
// 	}

//     return integral_of_value($val);
// }

// end