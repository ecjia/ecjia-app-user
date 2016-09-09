<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 删除收货地址
 * @author royalwang
 *
 */
class delete_module extends api_front implements api_interface {
    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {	
    	
    	$this->authSession();	
		$address_id = $this->requestData('address_id', 0);
		if (empty($address_id)) {
			return new ecjia_error(101, '参数错误');
		} 
		if (!drop_consignee($address_id)) {
			return new ecjia_error(8, 'fail');
		}
		return array();		
	}
}

/**
 * 删除一个收货地址
 *
 * @access public
 * @param integer $id
 * @return boolean
 */
function drop_consignee($id) {
    $db_user_address = RC_Loader::load_app_model('user_address_model', 'user');
    
    $uid = $db_user_address->where(array('address_id' => $id))->get_field('user_id');
    if (!empty($uid)) {
        if ($uid != $_SESSION['user_id']) {
            return false;
        } else {
            $res = $db_user_address->where(array('address_id' => $id))->delete();
            return $res;
        }
    } else {
        return false;
    }
}

// end