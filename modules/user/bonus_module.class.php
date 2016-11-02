<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 会员红包列表
 * @author will.chen
 *
 */
class bonus_module extends api_front implements api_interface
{
    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request)
    {
        //如果用户登录获取其session
        $this->authSession();
        $user_id = $_SESSION['user_id'];
        if ($user_id <= 0) {
            return new ecjia_error(100, 'Invalid session');
        }
		
		$bonus_type	= $this->requestData('bonus_type');
		$bonus_list = RC_Api::api('user', 'user_bonus_list', array('bonus_type' => $bonus_type));
		
		return $bonus_list;
	}
}


// end