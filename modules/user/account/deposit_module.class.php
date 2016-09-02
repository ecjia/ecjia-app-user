<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 用户充值申请
 * @author royalwang
 *
 */
class deposit_module extends api_front implements api_interface {
    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {
    		
    	$this->authSession();	
 		$amount = $this->requestData('amount');
 		$user_note = $this->requestData('note', '');
 		$account_id = $this->requestData('account_id', 0);
 		$payment_id = $this->requestData('payment_id', 0);
 		$user_id = $_SESSION['user_id'];
 		
 		$amount = floatval($amount);
 		if ($amount <= 0) {
 			$result = new ecjia_error('amount_gt_zero', __('请在“金额”栏输入大于0的数字！'));
 			EM_Api::outPut($result);
 		}
 		
 		/* 变量初始化 */
 		$surplus = array(
 				'user_id'      => $user_id,
 				'account_id'   => intval($account_id),
 				'process_type' => 0,
 				'payment_id'   => intval($payment_id),
 				'user_note'    => $user_note,
 				'amount'       => $amount
 		);
 		
 		if ($surplus['payment_id'] <= 0) {
 			$result = new ecjia_error('select_payment_pls', __('请选择支付方式！'));
 			EM_Api::outPut($result);
 		}
 		
 		$payment_method = RC_Loader::load_app_class('payment_method', 'payment');
 		
 		//获取支付方式名称
 		$payment_info = array();
 		$payment_info = $payment_method->payment_info($surplus['payment_id']);
//  		$surplus['payment'] = $payment_info['pay_name'];
 		$surplus['payment'] = $payment_info['pay_code'];
 		
 		if ($surplus['account_id'] > 0) {
 			//更新会员账目明细
 			$surplus['account_id'] = em_update_user_account($surplus);
 		} else {
 			RC_Loader::load_app_func('user', 'user');
 			//插入会员账目明细
 			$surplus['account_id'] = insert_user_account($surplus, $amount);
 		}
 		
 		//取得支付信息，生成支付代码
 		$payment_config = $payment_method->unserialize_config($payment_info['pay_config']);
 		
 		//生成伪订单号, 不足的时候补0
 		RC_Loader::load_app_func('order', 'orders');
 		$order = array();
 		$order['order_sn']       = get_order_sn();
 		$order['user_name']      = $_SESSION['user_name'];
 		$order['surplus_amount'] = $amount;
 		
 		RC_Loader::load_app_func('order', 'orders');
 		//计算支付手续费用
 		$payment_info['pay_fee'] = pay_fee($surplus['payment_id'], $order['surplus_amount'], 0);
 		
 		//计算此次预付款需要支付的总金额
 		$order['order_amount']   = strval($amount + $payment_info['pay_fee']);
 		
 		if ($account_id > 0) {
 			//获取需要支付的log_id
 			$order['log_id'] = $payment_method->get_paylog_id($surplus['account_id'], $pay_type = PAY_SURPLUS);
 			$payment_method->update_pay_log($surplus['account_id'], $order['order_amount'], $type=PAY_SURPLUS, 0);
 		} else {
 			//记录支付log
 			$order['log_id'] = strval($payment_method->insert_pay_log($surplus['account_id'], $order['order_amount'], $type=PAY_SURPLUS, 0));
 		}
 		
 		$order['payment']['payment_id'] = $surplus['payment_id'];
 		$order['payment']['account_id'] = $surplus['account_id'];
//  		$handler = $payment_method->get_payment_instance($payment_info['pay_code'], $payment_config);
//  		$handler->set_orderinfo($order);
//  		$handler->set_mobile(true);
 		
//  		$result = $handler->get_code(payment_abstract::PAYCODE_PARAM);
//  		if (is_ecjia_error($result)) {
//  			EM_Api::outPut($result);
//  		} else {
//  			$order['payment'] = $result;
//  			$order['payment']['account_id'] = $surplus['account_id'];
//  		}
 		
 		return array('payment' => $order['payment']);
	}
}

/**
 * 更新会员账目明细
 *
 * @access  public
 * @param   array     $surplus  会员余额信息
 *
 * @return  int
 */
function em_update_user_account($surplus) {
	$db = RC_Loader::load_app_model('user_account_model', 'user');
	$data = array(
		'amount'	=> $surplus['amount'],
		'user_note'	=> $surplus['user_note'],
		'payment'	=> $surplus['payment'],
	);
	$db->where(array('id' => $surplus['account_id']))->update($data);

	return $surplus['account_id'];
}

// end