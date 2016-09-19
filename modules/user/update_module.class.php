<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 用户 头像上传
 * @author royalwang
 *
 */
class update_module extends api_front implements api_interface {
    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {
    		
    	$this->authSession();	
		$img = $this->requestData('avatar_img');
		$uid = $_SESSION['user_id'];
// 		if (!$uid) {
// 		    return new ecjia_error(100, 'Invalid session' );
// 		}
 		
 		$db = RC_Model::model('user/users_model');
 		$userinfo = $db->field('user_name')->find(array('user_id' => $uid));
 		
 		$uid = abs(intval($uid));//保证uid为绝对的正整数
 		
 		$uid = sprintf("%09d", $uid);//格式化uid字串， d 表示把uid格式为9位数的整数，位数不够的填0
 		
 		$dir1 = substr($uid, 0, 3);//把uid分段
 		$dir2 = substr($uid, 3, 2);
 		$dir3 = substr($uid, 5, 2);
 		
 		if (empty($userinfo)) {
 		    return new ecjia_error('user_error', __('用户信息错误！'));
 		}
 		
 		$filename = md5($userinfo['user_name']);

 		$path = RC_Upload::upload_path() . 'data' . DIRECTORY_SEPARATOR . 'avatar' . DIRECTORY_SEPARATOR . $dir1 . DIRECTORY_SEPARATOR . $dir2 . DIRECTORY_SEPARATOR . $dir3;
 		$filename_path = $path. DIRECTORY_SEPARATOR . substr($uid, -2)."_".$filename.'.jpg';
 		
 		//创建目录
 		$result = RC_Filesystem::mkdir($path, 0777, true, true);
 			
 		//删除原有图片
 		RC_Filesystem::delete($filename_path);

 		@unlink($filename_path);//删除原有图片
 		$img = base64_decode($img);
 		file_put_contents($filename_path, $img);//返回的是字节数printr(a);

 		RC_Loader::load_app_func('user', 'user');
 		
 		$user_info = EM_user_info($uid);
 		return $user_info;
 		
	}
}

// end