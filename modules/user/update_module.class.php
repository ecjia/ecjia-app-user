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
 		
 		$db = RC_Model::model('user/users_model');
 		$userinfo = $db->field('user_name')->find(array('user_id' => $_SESSION['user_id']));
 		
 		$uid = $_SESSION['user_id'];
 		
 		$uid = abs(intval($uid));//保证uid为绝对的正整数
 		
 		$uid = sprintf("%09d", $uid);//格式化uid字串， d 表示把uid格式为9位数的整数，位数不够的填0
 		
 		$dir1 = substr($uid, 0, 3);//把uid分段
 		$dir2 = substr($uid, 3, 2);
 		$dir3 = substr($uid, 5, 2);
 		
 		$filename = md5($userinfo['user_name']);

 		$path = RC_Upload::upload_path() . 'data' . DIRECTORY_SEPARATOR . 'avatar' . DIRECTORY_SEPARATOR .$dir1 . DIRECTORY_SEPARATOR . $dir2 . DIRECTORY_SEPARATOR . $dir3;
 		$filename_path = $path. DIRECTORY_SEPARATOR . substr($uid, -2)."_".$filename.'.jpg';
 		
 		//创建目录 		
 		RC_Dir::create($path);

 		@unlink($filename_path);//删除原有图片
 		$img = base64_decode($img);
 		file_put_contents($filename_path, $img);//返回的是字节数printr(a);

 		RC_Loader::load_app_func('user', 'user');
 		
 		$user_info = EM_user_info($_SESSION['user_id']);
 		return $user_info;
 		
	}
}

// end