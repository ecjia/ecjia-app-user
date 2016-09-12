<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 单条收货地址信息
 * @author royalwang
 *
 */
class info_module extends api_front implements api_interface {
    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {	
    	
    	$this->authSession();	
		$id = $this->requestData('address_id', 0);
		if(empty($id)){
			return new ecjia_error(101, '参数错误');
		}

		RC_Loader::load_app_func('order','orders');
		
		$user_id = $_SESSION['user_id'];

		$db_user_address = RC_Model::model('user/user_address_model');
		$db_region = RC_Model::model('shipping/region_model');
		$arr = $db_user_address->find(array('address_id' => $id, 'user_id' => $user_id));

		/* 验证地址id */
		if (empty($arr)) {
		    return new ecjia_error(13, '不存在的信息');
		}
		
		$consignee = get_consignee($user_id); // 取得默认地址
		$result['id']         = $arr['address_id'];
		$result['consignee']  = $arr['consignee'];
		$result['email']      = $arr['email'];
		
		$result['country']    = $arr['country'];
		$result['province']   = $arr['province'];
		$result['city']       = $arr['city'];
		$result['district']   = $arr['district'];
		$result['location']	  = array(
									'longitude' => $arr['longitude'],
									'latitude'	=> $arr['latitude'],
								);
		
		$ids = array($result['country'], $result['province'], $result['city'], $result['district']);
		$ids = array_filter($ids);

		$data = $db_region->in(array('region_id' => implode(',', $ids)))->select();
		
		$out = array();
		foreach ($data as $key => $val) {
			$out[$val['region_id']] = $val['region_name'];
		}
		
		$result['country_name']   = isset($out[$result['country']]) ? $out[$result['country']] : '';
		$result['province_name']  = isset($out[$result['province']]) ? $out[$result['province']] : '';
		$result['city_name']      = isset($out[$result['city']]) ? $out[$result['city']] : '';
		$result['district_name']  = isset($out[$result['district']]) ? $out[$result['district']] : '';
		
		$result['address']        = $arr['address'];
		$result['address_info']   = $arr['address_info'];
		$result['zipcode']        = $arr['zipcode'];
		$result['mobile']         = $arr['mobile'];
		$result['sign_building']  = $arr['sign_building'];
		$result['best_time']      = $arr['best_time'];
		$result['default_address']= $arr['default_address'];
		$result['tel']            = $arr['tel'];
		
		if ($arr['address_id'] == $consignee['address_id']) {
			$result['default_address'] = 1;
		} else {
			$result['default_address'] = 0;
		}
		
		return $result;		
	}
}

// end