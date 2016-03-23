<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 所有收货地址列表
 * @author royalwang
 *
 */
class list_module implements ecjia_interface {
	
	
	public function run(ecjia_api & $api) {

		EM_Api::authSession();	
			
		$user_id = $_SESSION['user_id'];
		
		$db_user_address = RC_Loader::load_app_model('user_address_model','user');
		$db_region = RC_Loader::load_app_model('region_model','shipping');
		
		$consignee_list = $db_user_address->where(array('user_id' => $user_id))->order(array('address_id' => 'desc'))->limit(5)->select();
		
		RC_Loader::load_app_func('order','orders');
		$consignee = get_consignee($user_id); // 取得默认地址
		
		$result = array();
		foreach ($consignee_list as $key => $value) {
		
			$result[$key]['id'] = $value['address_id'];
			$result[$key]['consignee'] = $value['consignee'];
			$result[$key]['address'] = $value['address'];
		
			$country = $value['country'];
			$province = $value['province'];
			$city = $value['city'];
			$district = $value['district'];

			$region_name = $db_region->where(array('region_id' => array('in'=>$country,$province,$city,$district)))->order('region_type')->select();
			
			$result[$key]['country_name']    = $region_name[0]['region_name'];
			$result[$key]['province_name']   = $region_name[1]['region_name'];
			$result[$key]['city_name']       = $region_name[2]['region_name'];
			$result[$key]['district_name']   = $region_name[3]['region_name'];
			$result[$key]['tel']   			 = $value['tel'];
			$result[$key]['mobile']   		 = $value['mobile'];
			
			if ($value['address_id'] == $consignee['address_id']) {
				$result[$key]['default_address'] = 1;
			} else {
				$result[$key]['default_address'] = 0;
			}
		}
		
		return $result;
	}
}


// end