<?php

namespace Ecjia\App\User\Integrate;

use Ecjia\System\Plugin\AbstractPlugin;
use Ecjia\App\User\Integrate\Tables\EcjiaUserTable;
use RC_DB;
use RC_Api;

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

    protected $user_table;


    public function __construct()
    {
        $this->user_table = new EcjiaUserTable();

    }


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

    public function getUserInfo($username)
    {
        return $this->getProfileByName($username);
    }

    /**
     *  获取指定用户的信息
     *
     * @param $username
     * @return array
     */
    public function getProfileByName($username)
    {
        $row = RC_DB::table($this->user_table->getUserTable())->selectRaw(
            $this->user_table->getFieldId() . ' AS `user_id`, ' .
            $this->user_table->getFieldName() . ' AS `user_name`, ' .
            $this->user_table->getFieldEmail() . ' AS `email`, ' .
            $this->user_table->getFieldGender() . ' AS `sex`, ' .
            $this->user_table->getFieldBirthDay() . ' AS `birthday`, ' .
            $this->user_table->getFieldRegDate() . ' AS `reg_time`, ' .
            $this->user_table->getFieldPass() . ' AS `password`'
        )->where($this->user_table->getFieldName(), $username)
            ->first();

        return $row;
    }

    /**
     *  获取指定用户的信息
     *
     * @param $id
     * @return array
     */
    public function getProfileById($id)
    {
        $row = RC_DB::table($this->user_table->getUserTable())->selectRaw(
            $this->user_table->getFieldId() . ' AS `user_id`, ' .
            $this->user_table->getFieldName() . ' AS `user_name`, ' .
            $this->user_table->getFieldEmail() . ' AS `email`, ' .
            $this->user_table->getFieldGender() . ' AS `sex`, ' .
            $this->user_table->getFieldBirthDay() . ' AS `birthday`, ' .
            $this->user_table->getFieldRegDate() . ' AS `reg_time`, ' .
            $this->user_table->getFieldPass() . ' AS `password`'
        )->where($this->user_table->getFieldId(), $id)
            ->first();

        return $row;
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
     * 会员同步
     *
     * @param $username
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

        $profile = RC_DB::table($this->user_table->getUserTable())
            ->selectRaw(
                $this->user_table->getFieldName() . ' AS `user_name`, ' .
                $this->user_table->getFieldEmail() . ' AS `email`, ' .
                $this->user_table->getFieldGender() . ' AS `sex`, ' .
                $this->user_table->getFieldBirthDay() . ' AS `birthday`, ' .
                $this->user_table->getFieldPass() . ' AS `password`'
            )->where($this->user_table->getFieldName(), $username)
            ->first();

        if (empty($profile)) {
            /* 向用户表插入一条新记录 */
            if (empty($md5password)) {
                $data = array(
                    $this->user_table->getFieldName()       => $username,
                    $this->user_table->getFieldEmail()      => $main_profile['email'],
                    $this->user_table->getFieldGender()     => $main_profile['sex'],
                    $this->user_table->getFieldBirthDay()   => $main_profile['birthday'] ,
                    $this->user_table->getFieldRegDate()    => $main_profile['reg_time'],
                );
                RC_DB::table($this->user_table->getUserTable())->insert($data);
            } else {
                $data = array(
                    $this->user_table->getFieldName()       => $username,
                    $this->user_table->getFieldEmail()      => $main_profile['email'],
                    $this->user_table->getFieldGender()     => $main_profile['sex'],
                    $this->user_table->getFieldBirthDay()   => $main_profile['birthday'] ,
                    $this->user_table->getFieldRegDate()    => $main_profile['reg_time'],
                    $this->user_table->getFieldPass()       => $md5password
                );
                RC_DB::table($this->user_table->getUserTable())->insert($data);
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
                RC_DB::table($this->user_table->getUserTable())->where($this->user_table->getFieldName(), $username)->update($values);
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
     * 根据登录状态设置cookie
     *
     * @return boolean
     */
    public function getCookie()
    {
        $id = $this->checkCookie();
        if ($id) {
            if ($this->need_sync) {
                $this->sync($id);
            }
            $this->setSession($id);
            return true;
        } else {
            return false;
        }
    }

    
    public function clearCookie()
    {
        
    }
    
    public function clearSession()
    {
        
    }
    
    
    
}