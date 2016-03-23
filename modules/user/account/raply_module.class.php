<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 用户提现申请
 * @author royalwang
 *
 */
class raply_module implements ecjia_interface {
	
	public function run(ecjia_api & $api) {

 		EM_Api::authSession();
 		
 		$amount = _POST('amount');
 		$user_note = _POST('note', '');
 		$amount = floatval($amount);
		if ($amount <= 0) {
 			$result = new ecjia_error('amount_gt_zero', __('请在“金额”栏输入大于0的数字！'));
 			EM_Api::outPut($result);
 		}
 		$user_id = $_SESSION['user_id'];
 		/* 变量初始化 */
 		$surplus = array(
 				'user_id'      => $user_id,
 				'account_id'   => 0,
 				'process_type' => 1,
 				'payment_id'   => 0,
 				'user_note'    => $user_note,
 				'amount'       => $amount
 		);
 		
 		RC_Loader::load_app_func('user', 'user');
 		/* 判断是否有足够的余额的进行退款的操作 */
 		$sur_amount = get_user_surplus($user_id);
 		if ($amount > $sur_amount) {
 			$result = new ecjia_error('surplus_amount_error', __('您要申请提现的金额超过了您现有的余额，此操作将不可进行！'));
 			EM_Api::outPut($result);
 		}
 		
 		//插入会员账目明细
 		$amount = '-'.$amount;
 		$surplus['payment'] = '';
 		$surplus['account_id']  = insert_user_account($surplus, $amount);
 		
 		/* 如果成功提交 */
 		if ($surplus['account_id'] > 0) {
 			EM_Api::outPut(array('data' => "您的提现申请已成功提交，请等待管理员的审核！"));
 		} else {
 			$result = new ecjia_error('process_false', __('此次操作失败，请返回重试！'));
 			EM_Api::outPut($result);
 		}
 		
	}
}

// end