<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 用户注册字段
 * @author royalwang
 *
 */
class signupFields_module implements ecjia_interface {
	
	public function run(ecjia_api & $api) {

		$db = RC_Loader::load_app_model('reg_fields_model','user');

		$extend_info_list = $db->where(array('type' => array('lt' => 2), 'display' => '1', 'id' => array('neq' => 6)))->order(array('dis_order' => 'asc' , 'id' => 'asc'))->select();
		$out = array();
		foreach ($extend_info_list as $val) {
			$out[] = array(
					'id' => $val['id'],
					'name' => $val['reg_field_name'],
					'need' => $val['is_need']
			);
		}

		return $out;
	}
}


// end