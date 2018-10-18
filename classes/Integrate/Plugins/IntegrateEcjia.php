<?php


namespace Ecjia\App\User\Integrate\Plugins;

use Ecjia\App\User\Integrate\Password;
use Ecjia\App\User\Integrate\Tables\EcjiaUserTable;
use Ecjia\App\User\Integrate\UserIntegrateAbstract;
use ecjia_error;
use RC_DB;
use RC_Lang;

class IntegrateEcjia extends UserIntegrateAbstract
{

    protected $user_table;


    public function __construct()
    {
        $this->user_table = new EcjiaUserTable();

        $this->need_sync  = false;

    }

    /**
     * 获取插件代号
     *
     * @see \Ecjia\System\Plugin\PluginInterface::getCode()
     */
    public function getCode()
    {
        return 'ecjia';
    }

    /**
     * 加载配置文件
     *
     * @see \Ecjia\System\Plugin\PluginInterface::loadConfig()
     */
    public function loadConfig($key = null, $default = null)
    {
        return null;
    }

    /**
     * 加载语言包
     *
     * @see \Ecjia\System\Plugin\PluginInterface::loadLanguage()
     */
    public function loadLanguage($key = null, $default = null)
    {
        $lang = array(
            'ecjia'            => 'ECJia',
            'ecjia_desc'       => 'ECJia默认会员系统',
        );

        return $this->getArrayData($lang, $key, $default);
    }

    /**
     *  设置指定用户SESSION
     *
     * @access  public
     *
     * @return void
     */
    public function setSession($username)
    {
        if (empty($username)) {
            RC_Session::destroy();
        } else {
            $row = $this->db->field('user_id, password, email')->find(array('user_name' => $username));
            if ($row) {
                //$_SESSION['user_id']   			= $row['user_id'];
                //$_SESSION['user_name'] 			= $username;
                //$_SESSION['session_user_id']    = $row['user_id'];
                //$_SESSION['session_user_type']  = $row['user'];
                //$_SESSION['email']     			= $row['email'];
                //$_SESSION['ip']     			= RC_Ip::client_ip();

                RC_Session::set('user_id', $row['user_id']);
                RC_Session::set('user_name', $username);
                RC_Session::set('session_user_id', $row['user_id']);
                RC_Session::set('session_user_type', 'user');
                RC_Session::set('email', $row['email']);
                RC_Session::set('ip', RC_Ip::client_ip());
            }
        }
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
            $time = time() - 3600;
            setcookie("ECJIA[user_id]",  '', $time, $this->cookie_path);
            setcookie("ECJIA[password]", '', $time, $this->cookie_path);

        } elseif ($remember) {
            /* 设置cookie */
            $time = time() + 3600 * 24 * 15;
            setcookie("ECJIA[username]", $username, $time, $this->cookie_path, $this->cookie_domain);

            $row = $this->db->field('user_id, password')->find(array('user_name' => $username));
            if ($row) {
                setcookie("ECJIA[user_id]", $row['user_id'], $time, $this->cookie_path, $this->cookie_domain);
                setcookie("ECJIA[password]", $row['password'], $time, $this->cookie_path, $this->cookie_domain);
            }
        }
    }

    /**
     *  编译密码函数
     *
     * @access  public
     * @param   array   $cfg 包含参数为 $password, $md5password, $salt, $type
     *
      @return string
     */
    public function compilePassword($password, $salt = null, $type = null)
    {
        $password = with(new Password())->compilePassword($password, $salt, $type);

        return $password;
    }

    /**
     *  检查指定用户是否存在及密码是否正确
     *
     * @access  public
     * @param   string  $username   用户名
     *
     * @return  int
     */
    public function checkUser($username, $password = null)
    {
        $post_username = $username;

        /* 如果没有定义密码则只检查用户名 */
        if ($password === null) {
            return $this->db->field($this->field_id)->find(array($this->field_name => $post_username));
        } else {
            return $this->db->field($this->field_id)->find(array($this->field_name => $post_username, $this->field_pass => $this->compile_password(array('password' => $password))));
        }

    }

    /**
     *  检查指定邮箱是否存在
     *
     * @access  public
     * @param   string  $email   用户邮箱
     *
     * @return  boolean
     */
    public function checkEmail($email)
    {
        if (!empty($email)) {
            /* 检查email是否重复 */
            $result = $this->db->field($this->field_id)->find(array($this->field_email => $email));
            if($result[$this->field_id] > 0) {
                $this->error = ERR_EMAIL_EXISTS;
                return true;
            }
            return false;
        }
    }

    /**
     *  检查cookie是正确，返回用户名
     *
     * @access  public
     * @param
     *
     * @return boolean
     */
    public function checkCookie()
    {
        return true;
    }

    /**
     * 添加一个新用户
     *
     * @param $username
     * @param null $password
     * @param $email
     * @param int $gender
     * @param int $bday
     * @param int $reg_date
     * @param string $md5password
     * @return bool
     */
    public function addUser($username, $password = null, $email, $gender = -1, $bday = 0, $reg_date = 0, $md5password = '')
    {
        /* 将用户添加到整合方 */
        if ($this->check_user($username) > 0) {
            $this->error = new ecjia_error('ERR_USERNAME_EXISTS', RC_Lang::get('user::users.username_exists'));
            return false;
        }

        /* 检查email是否重复 */
        $query = $this->db->field($this->field_id)->find(array($this->field_email => $email));
        if ($query[$this->field_id] > 0) {
            $this->error = new ecjia_error('ERR_EMAIL_EXISTS', RC_Lang::get('user::users.email_exists'));
            return false;
        }

        $post_username = $username;

        if ($md5password) {
            $post_password = $this->compile_password(array('md5password' => $md5password));
        } else {
            $post_password = $this->compile_password(array('password' => $password));
        }

        $fields = array($this->field_name, $this->field_email, $this->field_pass);
        $values = array($post_username, $email, $post_password);

        if ($gender > -1) {
            $fields[] = $this->field_gender;
            $values[] = $gender;
        }

        if ($bday) {
            $fields[] = $this->field_bday;
            $values[] = $bday;
        }

        if ($reg_date) {
            $fields[] = $this->field_reg_date;
            $values[] = $reg_date;
        }

        $data = array_combine($fields, $values);
        $this->db->insert($data);

        if ($this->need_sync) {
            $this->sync($username, $password);
        }

        return true;
    }


    /**
     *  编辑用户信息($password, $email, $gender, $bday)
     *
     * @access  public
     * @param
     *
     * @return void
     */
    public function editUser($cfg)
    {
        if (empty($cfg['username'])) {
            return false;
        } else {
            $cfg['post_username'] = $cfg['username'];
        }

        $values = array();
        if (!empty($cfg['password']) && empty($cfg['md5password'])) {
            $cfg['md5password'] = md5($cfg['password']);
        }
        if ((!empty($cfg['md5password'])) && $this->field_pass != 'NULL') {
            $values[$this->field_pass] = $this->compile_password(array('md5password' => $cfg['md5password']));
        }

        if ((!empty($cfg['email'])) && $this->field_email != 'NULL') {
            /* 检查email是否重复 */
            $query = $this->db->field($this->field_id)->find(array($this->field_email => $cfg['email'], $this->field_name => array('neq' => $cfg['post_username'])));
            if ($query[$this->field_id] > 0) {
                $this->error = ERR_EMAIL_EXISTS;
                return false;
            }
            // 检查是否为新E-mail
            $count = $this->db->where(array($this->field_email => $cfg['email']))->count();
            if ($count == 0) {
                // 新的E-mail
                $this->db->where(array('user_name' => $cfg['post_username']))->update(array('is_validated' => 0));
            }
            $values[$this->field_email] = $cfg['email'];
        }

        if (isset($cfg['gender']) && $this->field_gender != 'NULL') {
            $values[$this->field_gender] = $cfg['gender'];
        }

        if ((!empty($cfg['bday'])) && $this->field_bday != 'NULL') {
            $values[$this->field_bday] = $cfg['bday'];
        }

        if ($values) {
            $this->db->where(array($this->field_name => $cfg['post_username']))->update($values);

            if ($this->need_sync) {
                if (empty($cfg['md5password'])) {
                    $this->sync($cfg['username']);
                } else {
                    $this->sync($cfg['username'], '', $cfg['md5password']);
                }
            }
        }

        return true;
    }

    /**
     * 删除用户
     *
     * @param $id
     * @return void
     */
    public function removeUser($id)
    {
        $post_id = $id;

        $db_order_info      = RC_Model::model('orders/order_info_model');
        $db_order_goods     = RC_Model::model('orders/order_goods_model');
        $db_collect_goods   = RC_Model::model('goods/collect_goods_model');

        /* 如果需要同步或是ecjia插件执行这部分代码 */
        if ($this->need_sync || (isset($this->is_ecjia) && $this->is_ecjia)) {
            if (is_array($post_id)) {
                $col = $this->db->in(array('user_id' => $post_id))->get_field('user_id', true);
            } else {
                $col = $this->db->field('user_id')->where(array('user_name' => $post_id))->find();
            }

            if ($col) {

                //将删除用户的下级的parent_id 改为0
                $this->db->in(array('parent_id' => $col))->update(array('parent_id' => 0));
                //删除用户
                $this->db->in(array('user_id' => $col))->delete();
                /* 删除用户订单 */
                $col_order_id = $db_order_info->in(array('user_id' => $col))->get_field('order_id', true);
                if ($col_order_id) {
                    $db_order_info->in(array('order_id' => $col_order_id))->delete();
                    $db_order_goods->in(array('order_id' => $col_order_id))->delete();
                }

                //删除会员收藏商品
                $db_collect_goods->in(array('user_id' => $col))->delete();
                //删除用户留言
//                 $db_feedback->in(array('user_id' => $col))->delete();
                //删除用户地址
                RC_DB::table('user_address')->whereIn('user_id', $col)->delete();
                //删除用户红包
                RC_DB::table('user_bonus')->whereIn('user_id', $col)->delete();
                //删除用户帐号金额
                RC_DB::table('user_account')->whereIn('user_id', $col)->delete();
                //删除用户标记
//                 $db_tag->in(array('user_id' => $col))->delete();
                //删除用户日志
                RC_DB::table('account_log')->whereIn('user_id', $col)->delete();

                RC_Api::api('connect', 'connect_user_remove', array('user_id' => $col));
            }
        }

        /* 如果是ecjia插件直接退出 */
        if (isset($this->ecjia) && $this->ecjia) {
            return;
        }

        if (is_array($post_id)) {
            $this->db->in(array($this->field_id => $post_id))->delete();
        } else {
            $this->db->where(array($this->field_name => $post_id))->delete();
        }
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
            $this->user_table->getFieldBirthDay() . ' AS `birthday`' .
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
            $this->user_table->getFieldBirthDay() . ' AS `birthday`' .
            $this->user_table->getFieldRegDate() . ' AS `reg_time`, ' .
            $this->user_table->getFieldPass() . ' AS `password`'
            )->where($this->user_table->getFieldId(), $id)
            ->first();

        return $row;
    }

    /**
     *  根据登录状态设置cookie
     *
     * @access  public
     * @param
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


    /**
     *  会员同步
     *
     * @access  public
     * @param
     *
     * @return boolean
     */
    public function sync($username, $password = '', $md5password = '')
    {

        if ((!empty($password)) && empty($md5password)) {
            $md5password = md5($password);
        }

        $main_profile = $this->getProfileByName($username);

        if (empty($main_profile)) {
            return false;
        }

        $profile = $this->db->field('user_name, email, password, sex, birthday')->find(array('user_name' => $username));
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
                $this->db->insert($data);
            } else {
                $data = array(
                    'user_name'  => $username,
                    'email'      => $main_profile['email'],
                    'sex'        => $main_profile['sex'],
                    'birthday'   => $main_profile['birthday'] ,
                    'reg_time'   => $main_profile['reg_time'],
                    'password'   => $md5password
                );
                $this->db->insert($data);

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
                return  true;
            } else {
                $this->db->where(array('user_name' => $username))->update($values);
                return true;
            }
        }
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
     *  获取用户积分
     *
     * @access  public
     * @param
     *
     * @return array|boolean
     */
    public function getPoints($username)
    {
        $credits = $this->getPointsName();
        $fileds = array_keys($credits);
        if ($fileds) {
            $row = RC_DB::table($this->user_table->getUserTable())
                ->select($this->user_table->getFieldId())
                ->selectRaw(implode(', ',$fileds))
                ->where($this->user_table->getFieldName(), $username)
                ->first();
            return $row;
        } else {
            return false;
        }
    }


    /**
     *设置用户积分
     *
     * @access  public
     * @param
     *
     * @return boolean
     */
    public function setPoints($username, $credits)
    {
        $user_set = array_keys($credits);
        $points_set = array_keys($this->getPointsName());

        $set = array_intersect($user_set, $points_set);

        if ($set) {
            $tmp = array();
            foreach ($set as $credit) {
                $tmp[$credit] = $credit + $credits[$credit];
            }

            RC_DB::table($this->user_table->getUserTable())
                ->where($this->user_table->getFieldName(), $username)
                ->update($tmp);
        }

        return true;
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
     * 检查有无重名用户，有则返回重名用户
     *
     * @param $user_list
     * @return null|array
     */
    public function testConflict($user_list)
    {
        if (empty($user_list)) {
            return array();
        }

        $user_list = RC_DB::table($this->user_table->getUserTable())
            ->select($this->user_table->getFieldName())
            ->whereIn($this->user_table->getFieldName(), $user_list)
            ->get();

        return $user_list;
    }




    
}