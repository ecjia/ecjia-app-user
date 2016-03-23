<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 删除收货地址
 * @author royalwang
 *
 */
class delete_module implements ecjia_interface {
	
	public function run(ecjia_api & $api) {

		EM_Api::authSession();
		
		$address_id = _POST('address_id', 0);
		
		if (empty($address_id)) {
			EM_Api::outPut(101);
		} 
		if (!drop_consignee($address_id)) {
			EM_Api::outPut(8);
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
function drop_consignee($id)
{
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