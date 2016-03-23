<?php
defined('IN_ECJIA') or exit('No permission resources.');

class user_rank_member_price_viewmodel extends Component_Model_View {
	public $table_name = '';
	public $view = array();
	public function __construct() {
		$this->db_config = RC_Config::load_config('database');
		$this->db_setting = 'default';
		$this->table_name = 'user_rank';
		$this->table_alias_name = 'r';
		
		$this->view =array(
				'member_price' 	=> array(
					'type' 		=> Component_Model_View::TYPE_LEFT_JOIN,
					'alias' 	=> 'mp',
					'on' 		=> 'mp.user_rank = r.rank_id '
			),
		);	
		parent::__construct();
	}
}

// end