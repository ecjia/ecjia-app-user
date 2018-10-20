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
abstract class UserIntegrateDatabaseAbstract extends UserIntegrateAbstract
{

    protected $user_table;


    public function __construct()
    {
        $this->user_table = new EcjiaUserTable();

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
    public function addUser($username, $password, $email, $gender = -1, $bday = 0, $reg_date = 0, $md5password = null)
    {
        /* 将用户添加到整合方 */
        if ($this->checkUser($username) > 0) {
            $this->error = self::ERR_USERNAME_EXISTS;
            return false;
        }

        /* 检查email是否重复 */
        if ($this->checkEmail($email)) {
            $this->error = self::ERR_EMAIL_EXISTS;
            return false;
        }

        $post_username = $username;

        if ($md5password) {
            $post_password = $this->compilePassword(null, $md5password);
        } else {
            $post_password = $this->compilePassword($password);
        }

        $fields = array($this->user_table->getFieldName(), $this->user_table->getFieldEmail(), $this->user_table->getFieldPass());
        $values = array($post_username, $email, $post_password);

        if ($gender > -1) {
            $fields[] = $this->user_table->getFieldGender();
            $values[] = $gender;
        }

        if ($bday) {
            $fields[] = $this->user_table->getFieldBirthDay();
            $values[] = $bday;
        }

        if ($reg_date) {
            $fields[] = $this->user_table->getFieldRegDate();
            $values[] = $reg_date;
        }

        $data = array_combine($fields, $values);
        RC_DB::table($this->user_table->getUserTable())->insert($data);

        if ($this->need_sync) {
            $this->sync($username, $password);
        }

        return true;
    }

    /**
     * 编辑用户信息($password, $email, $gender, $bday)
     *
     * @param $username
     * @param null $password
     * @param $email
     * @param int $gender
     * @param int $bday
     * @param null $md5password
     * @return bool
     */
    public function editUser($username, $password, $email, $gender = -1, $bday = 0, $md5password = null)
    {
        $post_username = $username;

        $values = array();
        if (!empty($password) && empty($md5password)) {
            $md5password = md5($password);
        }

        if (!empty($md5password) && ! is_null($this->user_table->getFieldPass())) {
            $values[$this->user_table->getFieldPass()] = $this->compilePassword(null, $md5password);
        }

        if ((!empty($email)) && ! is_null($this->user_table->getFieldEmail())) {
            /* 检查email是否重复 */
            if ($this->checkEmail($email, $username) > 0) {
                $this->error = self::ERR_EMAIL_EXISTS;
                return false;
            }

            // 检查是否为新E-mail
            $count = $this->checkEmail($email);
            if (empty($count)) {
                // 新的E-mail，设置为未验证
                RC_DB::table($this->user_table)->where($this->user_table->getFieldName(), $username)->update(array('is_validated' => 0));
            }
            $values[$this->user_table->getFieldEmail()] = $email;
        }

        if (isset($gender) && ! is_null($this->user_table->getFieldGender())) {
            $values[$this->user_table->getFieldGender()] = $gender;
        }

        if ((!empty($bday)) && ! is_null($this->user_table->getFieldBirthDay())) {
            $values[$this->user_table->getFieldBirthDay()] = $bday;
        }

        if ($values) {
            RC_DB::table($this->user_table->getUserTable())->where($this->user_table->getFieldName(), $post_username)->update($values);

            if ($this->need_sync) {
                if (empty($md5password)) {
                    $this->sync($username);
                } else {
                    $this->sync($username, '', $md5password);
                }
            }
        }

        return true;
    }

    /**
     * 删除用户
     *
     * @param $username
     * @return
     */
    public function removeUser($username)
    {
        /* 如果需要同步或是ecjia插件执行这部分代码 */
        if ($this->need_sync || $this->getCode() == 'ecjia') {

            $this->syncRemoveUser($username);

        }

        if ($this->getCode() == 'ecjia')
        {
            /* 如果是ecshop插件直接退出 */
            return null;
        }

        //删除用户
        RC_DB::table($this->user_table->getUserTable())->where($this->user_table->getFieldName(), $username)->delete();

        return false;
    }

    /**
     * 检查指定用户是否存在及密码是否正确
     *
     * @param   string  $username   用户名
     * @return
     */
    public function checkUser($username, $password = null)
    {

        /* 如果没有定义密码则只检查用户名 */
        if ($password === null) {
            $user = RC_DB::table($this->user_table->getUserTable())
                ->where($this->user_table->getFieldName(), $username)
                ->pluck($this->user_table->getFieldId());

            return $user;
        } else {
            $password = $this->compilePassword($password);
            $user = RC_DB::table($this->user_table->getUserTable())
                ->where($this->user_table->getFieldName(), $username)
                ->where($this->user_table->getFieldPass(), $password)
                ->pluck($this->user_table->getFieldId());

            return $user;
        }

    }

    /**
     *  检查指定邮箱是否存在
     *
     * @param   string  $email   用户邮箱
     *
     * @return  boolean
     */
    public function checkEmail($email, $exclude_username = null)
    {
        if ($exclude_username) {
            /* 检查email是否重复，并排除指定的用户名 */
            $field_id = RC_DB::table($this->user_table->getUserTable())
                ->where($this->user_table->getFieldEmail(), $email)
                ->where($this->user_table->getFieldName(), $exclude_username)
                ->pluck($this->user_table->getFieldId());
        } else {
            /* 检查email是否重复 */
            $field_id = RC_DB::table($this->user_table->getUserTable())
                ->where($this->user_table->getFieldEmail(), $email)
                ->pluck($this->user_table->getFieldId());
        }

        if ($field_id > 0) {
            $this->error = self::ERR_EMAIL_EXISTS;
            return true;
        }
        return false;
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
     * 获取用户积分
     *
     * @param $username
     * @return bool
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
     * 设置用户积分
     *
     * @param $username
     * @param $credits
     * @return bool
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