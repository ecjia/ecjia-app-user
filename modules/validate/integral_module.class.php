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
			return new ecjia_error(101, '参数错误');
		}
		$integral_to_p =  value_of_integral($integral);
		return array(
			"bonus" => $integral_to_p,
			"bonus_formated" => price_format($integral_to_p, false)
		);
		return new ecjia_error(101, '参数错误');
	}
}

// end