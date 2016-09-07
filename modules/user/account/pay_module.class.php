<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 用户充值付款
 * @author royalwang
 *
 */
class pay_module extends api_front implements api_interface {
    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {
    		
    	$this->authSession();	
 		//变量初始化
 		$account_id = $this->requestData('account_id', 0);
 		$payment_id = $this->requestData('payment_id', 0);
	
 		if ($account_id <= 0 || $payment_id <= 0) {
	    	EM_Api::outPut(101);
	    }
	    
	    //获取单条会员帐目信息
	    $order = array();
	    $order = get_surplus_info($account_id);
	
	    $payment_method = RC_Loader::load_app_class('payment_method', 'payment');
	    //支付方式的信息
	    $payment_info = array();
	    $payment_info = $payment_method->payment_info($payment_id);
	
	    /* 如果当前支付方式没有被禁用，进行支付的操作 */
	    if (!empty($payment_info)) {
	        //取得支付信息，生成支付代码
	        $payment_config = $payment_method->unserialize_config($payment_info['pay_config']);
	        
	        RC_Loader::load_app_func('order', 'orders');
	        //生成伪订单号
	        $order['order_sn'] = get_order_sn();
	
	        //获取需要支付的log_id
	        $order['log_id'] = $payment_method->get_paylog_id($account_id, $pay_type = PAY_SURPLUS);
	
	        $order['user_name']      = $_SESSION['user_name'];
	        $order['surplus_amount'] = $order['amount'];
	        
			RC_Loader::load_app_func('order', 'orders');
	        //计算支付手续费用
	        $payment_info['pay_fee'] = pay_fee($payment_id, $order['surplus_amount'], 0);
	
	        //计算此次预付款需要支付的总金额
	        $order['order_amount']   = strval($order['surplus_amount'] + $payment_info['pay_fee']);
	
	        //如果支付费用改变了，也要相应的更改pay_log表的order_amount
	        $pay_db = RC_Loader::load_app_model('pay_log_model', 'orders');
	        $order_amount = $pay_db-> where(array('log_id' => $order['log_id']))->get_field('order_amount');
	        if ($order_amount <> $order['order_amount']) {
	        	$pay_db->where(array('log_id' => $order['log_id']))->update(array('order_amount' => $order['order_amount']));
	        }

	        $handler = $payment_method->get_payment_instance($payment_info['pay_code'], $payment_config);
	        $handler->set_orderinfo($order);
	        $handler->set_mobile(true);
	        	
	        $result = $handler->get_code(payment_abstract::PAYCODE_PARAM);
	        if (is_ecjia_error($result)) {
	        	return $result;
	        } else {
	        	$order['payment'] = $result;
	        }
	        	
	        return array('payment' => $order['payment']);
	    } else {
	    	/* 重新选择支付方式 */
	    	$result = new ecjia_error('select_payment_pls_again', __('支付方式无效，请重新选择支付方式！'));
 			return $result;
	    }
	}
}

/**
 * 根据ID获取当前余额操作信息
 *
 * @access  public
 * @param   int     $account_id  会员余额的ID
 *
 * @return  int
 */
function get_surplus_info($account_id) {
	$db = RC_Loader::load_app_model('user_account_model', 'user');
	
	return $db->find(array('id' => $account_id));
}

// end