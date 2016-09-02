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
		$integral_to_p =  value_of_integral($integral);
		EM_Api::outPut(array(
			"bonus" => $integral_to_p,
			"bonus_formated" => price_format($integral_to_p, false)
		));
		EM_Api::outPut(101);
	}
}

// end