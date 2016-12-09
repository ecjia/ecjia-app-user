<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 用户 头像上传
 * @author royalwang
 *
 */
class update_module extends api_admin implements api_interface {
    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {
    	
    	if ($_SESSION['admin_id' ] <= 0 && $_SESSION['staff_id'] <= 0) {
            return new ecjia_error(100, 'Invalid session');
        }
		
		$user_name = $this->requestData('username');
		
		if ($_SESSION['staff_id']) {
			if (isset($_FILES['avatar_img'])) {
				$save_path = 'data/staff/avatar_img';
				$upload = RC_Upload::uploader('image', array('save_path' => $save_path, 'auto_sub_dirs' => true));
					
				$image_info	= $upload->upload($_FILES['avatar_img']);
				/* 判断是否上传成功 */
				if (!empty($image_info)) {
					$avatar_img = $upload->get_position($image_info);
					$old_avatar_img = RC_DB::table('staff_user')->where('user_id', $_SESSION['staff_id'])->pluck('avatar');
					if (!empty($old_avatar_img)) {
						$upload->remove($old_avatar_img);
					}
					RC_DB::table('staff_user')->where('user_id', $_SESSION['staff_id'])->update(array('avatar' => $avatar_img));
				} else {
					return new ecjia_error('avatar_img_error', '头像上传失败！');
				}
			}
			
			if (!empty($user_name)) {
				RC_DB::table('staff_user')->where('user_id', $_SESSION['staff_id'])->update(array('name' => $user_name));
				$_SESSION['staff_name']		= $user_name;
			}
		}
		
 		return array();
 		
	}
}

// end