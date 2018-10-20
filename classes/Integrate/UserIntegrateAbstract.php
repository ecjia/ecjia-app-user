<?php

namespace Ecjia\App\User\Integrate;

use Ecjia\System\Plugin\AbstractPlugin;
use RC_DB;
use RC_Api;
use RC_Session;
use RC_Ip;

/**
 * 会员融合插件抽象类
 *
 * Class IntegrateAbstract
 * @package Ecjia\App\User\Integrate
 */
abstract class UserIntegrateAbstract extends AbstractPlugin implements UserIntegrateInterface
{

    /**
     * 用户名已经存在
     */
    const ERR_USERNAME_EXISTS       = 1;

    /**
     * Email已经存在
     */
    const ERR_EMAIL_EXISTS          = 2;

    /**
     * 无效的user_id
     */
    const ERR_INVALID_USERID        = 3;

    /**
     * 无效的用户名
     */
    const ERR_INVALID_USERNAME      = 4;

    /**
     * 密码错误
     */
    const ERR_INVALID_PASSWORD      = 5;

    /**
     * Email错误
     */
    const ERR_INVALID_EMAIL         = 6;

    /**
     * 用户名不允许注册
     */
    const ERR_USERNAME_NOT_ALLOW    = 7;

    /**
     * EMAIL不允许注册
     */
    const ERR_EMAIL_NOT_ALLOW       = 8;


    protected $error_message = [
        self::ERR_USERNAME_EXISTS         => '用户名已经存在',
        self::ERR_EMAIL_EXISTS            => 'Email已经存在',
        self::ERR_INVALID_USERID          => '无效的user_id',
        self::ERR_INVALID_USERNAME        => '无效的用户名',
        self::ERR_INVALID_PASSWORD        => '密码错误',
        self::ERR_INVALID_EMAIL           => 'Email错误',
        self::ERR_USERNAME_NOT_ALLOW      => '用户名不允许注册',
        self::ERR_EMAIL_NOT_ALLOW         => 'Email不允许注册',
    ];


    protected $cookie_domain;

    protected $cookie_path;

    /* 是否需要同步数据到商城 */
    protected $need_sync = true;

    protected $error = 0;


    public function getError()
    {
        return $this->error;
    }

    public function getErrorMessage()
    {
        return array_get($this->error_message, $this->error, '未知错误');
    }

    public function needSync()
    {
        return $this->need_sync;
    }

    /**
     * 获取插件的元数据
     *
     * @return \Royalcms\Component\Support\Collection
     */
    abstract public function getPluginMateData();

    /**
     *  获取指定用户的信息
     *
     * @param $username
     * @return array
     */
    abstract public function getProfileByName($username);


    /**
     *  获取指定用户的信息
     *
     * @param $id
     * @return array
     */
    abstract public function getProfileById($id);


    /**
     * 编译密码函数 包含参数为 $password, $md5password, $salt, $type
     *
     * @param $password
     * @param $md5password
     * @param null $salt
     * @param null $type
     * @return mixed
     */
    public function compilePassword($password, $md5password = null, $salt = null, $type = null)
    {
        $password = with(new Password())->compilePassword($password, $md5password, $salt, $type);

        return $password;
    }

    /**
     * @param $username
     * @return array
     */
    public function getUserInfo($username)
    {
        return $this->getProfileByName($username);
    }


    /**
     *  获取论坛有效积分及单位
     *
     * @access  public
     * @param
     *
     * @return array
     */
    public function getPointsName()
    {
        return array();
    }

    /**
     * 同步删除用户
     *
     * @param $username
     */
    public function syncRemoveUser($username)
    {
        $user_id = RC_DB::table('users')->where('user_name', $username)->pluck('user_id');

        if ($user_id) {

            $result = $this->userRemoveClearData($user_id);
            if ($result) {
                //将删除用户的下级的parent_id 改为0
                RC_DB::table('users')->where('parent_id', $user_id)->update(['parent_id' => 0]);

                //删除用户
                RC_DB::table('users')->where('user_id', $user_id)->delete();

            }

        }
    }

    /**
     * 会员同步
     * 使用第三方用户数据表同步时，将用户信息同步一份到ecjia_users数据表中
     *
     * @param string $username
     * @param null $password
     * @param null $md5password
     * @return bool
     */
    public function sync($username, $password = null, $md5password = null)
    {

        if ((!empty($password)) && empty($md5password)) {
            $md5password = md5($password);
        }

        $main_profile = $this->getProfileByName($username);
        if (empty($main_profile)) {
            return false;
        }

        $profile = RC_DB::table('users')
            ->select('user_name', 'email', 'password', 'sex', 'birthday')
            ->where('user_name', $username)
            ->first();

        if (empty($profile)) {
            /* 向用户表插入一条新记录 */
            if (empty($md5password)) {
                $data = array(
                    'user_name'  => $username,
                    'email'      => $main_profile['email'],
                    'sex'        => $main_profile['sex'],
                    'birthday'   => $main_profile['birthday'] ,
                    'reg_time'   => $main_profile['reg_time'],
                );
                RC_DB::table('users')->insert($data);
            } else {
                $data = array(
                    'user_name'  => $username,
                    'email'      => $main_profile['email'],
                    'sex'        => $main_profile['sex'],
                    'birthday'   => $main_profile['birthday'] ,
                    'reg_time'   => $main_profile['reg_time'],
                    'password'   => $md5password
                );
                RC_DB::table('users')->insert($data);
            }
            return true;
        } else {
            $values = array();
            if ($main_profile['email'] != $profile['email']) {
                $values['email'] = $main_profile['email'];
            }

            if ($main_profile['sex'] != $profile['sex']) {
                $values['sex'] = $main_profile['sex'];
            }

            if ($main_profile['birthday'] != $profile['birthday']) {
                $values['birthday'] = $main_profile['birthday'];
            }

            if ((!empty($md5password)) && ($md5password != $profile['password'])) {
                $values['password'] = $md5password;
            }

            if (empty($values)) {
                return true;
            } else {
                RC_DB::table('users')->where('user_name', $username)->update($values);
                return true;
            }
        }
    }

    /**
     * 删除用户时，清除用户数据
     *
     * @param $user_id
     * @return mixed
     */
    protected function userRemoveClearData($user_id)
    {
        //删除用户订单
        //删除会员收藏商品
        //删除用户留言
        //删除用户地址
        //删除用户红包
        //删除用户帐号金额
        //删除用户标记
        //删除用户帐户日志
        //删除用户关联帐号连接

        return RC_Api::apis('user_remove_cleardata', array('user_id' => $user_id));
    }

    /**
     *  用户登录函数
     *
     * @param   string  $username
     * @param   string  $password
     *
     * @return boolean
     */
    public function login($username, $password, $remember = null)
    {
        if ($this->checkUser($username, $password) > 0) {
            if ($this->need_sync) {
                $this->sync($username, $password);
            }
            $this->setSession($username);
            $this->setCookie($username, $remember);
        
            return true;
        } else {
            return false;
        }
        
    }
    
    
    /**
     *
     * 用户退出登录
     * 
     * @return void
     */
    public function logout()
    {
        //清除cookie
        $this->clearCookie(); 
        
        //清除session
        $this->clearSession(); 
    }

    /**
     * 检查cookie是正确，返回用户名
     *
     * @return boolean
     */
    public function checkCookie()
    {
        return null;
    }

    /**
     *  设置cookie
     *
     * @return void
     */
    public function setCookie($username, $remember = null)
    {
        if (empty($username)) {
            /* 摧毁cookie */
            $time = SYS_TIME - 3600;
            setcookie("ECJIA[user_id]",  '', $time, $this->cookie_path);
            setcookie("ECJIA[password]", '', $time, $this->cookie_path);

        } elseif ($remember) {
            /* 设置cookie */
            $time = SYS_TIME + 3600 * 24 * 15;
            setcookie("ECJIA[username]", $username, $time, $this->cookie_path, $this->cookie_domain);

            $row = RC_DB::table('users')->select('user_id', 'password')->where('user_name', $username)->first();
            if ($row) {
                setcookie("ECJIA[user_id]", $row['user_id'], $time, $this->cookie_path, $this->cookie_domain);
                setcookie("ECJIA[password]", $row['password'], $time, $this->cookie_path, $this->cookie_domain);
            }
        }
    }

    /**
     * 根据登录状态设置cookie
     *
     * @return boolean
     */
    public function getCookie()
    {
        $username = $this->checkCookie();
        if ($username) {
            if ($this->need_sync) {
                $this->sync($username);
            }
            $this->setSession($username);
            return true;
        } else {
            return false;
        }
    }

    
    public function clearCookie()
    {
        
    }

    /**
     *  设置指定用户SESSION
     *
     * @access  public
     *
     * @return void
     */
    public function setSession($username = null)
    {
        if (empty($username)) {

            RC_Session::destroy();

        } else {
            $row = RC_DB::table('users')->select('user_id', 'password', 'email')->where('user_name', $username)->first();
            if ($row) {
                RC_Session::set('user_id', $row['user_id']);
                RC_Session::set('user_name', $username);
                RC_Session::set('session_user_id', $row['user_id']);
                RC_Session::set('session_user_type', 'user');
                RC_Session::set('email', $row['email']);
                RC_Session::set('ip', RC_Ip::client_ip());
            }
        }
    }
    
    public function clearSession()
    {
        
    }
    
    
    
}