<?php
/**
* 添加管理员记录日志操作对象
*/
function assign_adminlog() {
	ecjia_admin_log::instance()->add_object('users_account', '会员账户');
	
	ecjia_admin_log::instance()->add_object('pay_apply', '充值申请');
	ecjia_admin_log::instance()->add_object('withdraw_apply', '提现申请');
}

//end