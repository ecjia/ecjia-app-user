<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 用户充值提现记录
 * @author royalwang
 *
 */
class record_module extends api_front implements api_interface {
    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {	
    	
    	$this->authSession();
    	$size = $this->requestData('pagination.count', 15);
    	$page = $this->requestData('pagination.page', 1);
 		$user_id = $_SESSION['user_id'];
 		$process_type = $this->requestData('type' ,'');
 		$type = array('', 'deposit', 'raply');
		if (!in_array($process_type, $type)) {
			EM_Api::outPut(101);
		}
 		$db = RC_Loader::load_app_model('user_account_model', 'user');
 		
 		$where = array(
 				'user_id' => $user_id,
 				'process_type' => array(SURPLUS_SAVE, SURPLUS_RETURN)
 		);
 		
		if (!empty($process_type)) {
			
 			$where['process_type'] = $process_type == 'deposit' ? 0 : 1;
 		}
 		/* 获取记录条数 */
 		$record_count = $db->where($where)->count();
 		
 		//加载分页类
		RC_Loader::load_sys_class('ecjia_page', false);
		//实例化分页
		$page_row = new ecjia_page($record_count, $size, 6, '', $page);
 		
 		RC_Loader::load_app_func('user' ,'user');

 		//获取余额记录
 		$account_log = get_account_log($user_id, $size, $page_row, $process_type);
 		$pager = array(
 				"total" => $page_row->total_records,
 				"count" => $page_row->total_records,
 				"more" => $page_row->total_pages <= $page ? 0 : 1,
 		);
 		
 		if (!empty($account_log) && is_array($account_log)) {
 			$account_list = array();
 			foreach ($account_log as $key => $value) {
				$account_list[$key]['account_id'] = $value['id'];
				$account_list[$key]['user_id'] = $value['user_id'];
				$account_list[$key]['admin_user'] = $value['admin_user'];
				$account_list[$key]['amount'] = $value['amount'];
				$account_list[$key]['format_amount'] = $value['format_amount'];
				$account_list[$key]['user_note'] = $value['user_note'];
				$account_list[$key]['type'] = $value['process_type'] == '0' ? 'deposit' : 'raply';
				$account_list[$key]['type_lable'] = $value['type'];
				$account_list[$key]['payment_name'] = strip_tags($value['payment']);
				$account_list[$key]['payment_id'] = $value['pid'];
				$account_list[$key]['is_paid'] = $value['is_paid'];
				$account_list[$key]['pay_status'] = $value['pay_status'];
				$account_list[$key]['add_time'] = $value['add_time'];
 			}
 			return array('data' => $account_list, 'pager' => $pager);
 		} else {
 			return array('data' => array(), 'pager' => $pager);
 		}
	}
}

// end