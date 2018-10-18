<?php

namespace Ecjia\App\User\Integrate;


use Royalcms\Component\Database\Connection;

class UserField
{
    /**
     * 整合对象会员表名
     * @var string
     */
    protected $table;

    /**
     * 整合对象数据连接
     * @var string
     */
    protected $connection = null;
    
    /**
     * 会员ID的字段名
     * @var integer
     */
    protected $field_id;
    
    /**
     * 会员名称的字段名
     * @var string
     */
    protected $field_name;
    
    /**
     * 会员密码的字段名
     * @var string
     */
    protected $field_pass;
    
    /**
     * 会员邮箱的字段名
     * @var string
     */
    protected $field_email;
    
    /**
     * 会员性别
     * @var integer
     */
    protected $field_gender;
    
    /**
     * 会员生日
     * @var date
     */
    protected $field_birthday;
    
    /**
     * 注册日期的字段名
     * @var integer
     */
    protected $field_reg_date;

    /**
     * 设置数据库连接对象
     * @param $connect
     */
    public function setDatabaseConnection(Connection $connection)
    {
        $this->connection = $connection;

        return $this;
    }

    public function getDatabaseConnection()
    {
        return $this->connection;
    }

    /**
     * @return string
     */
    public function getUserTable()
    {
        return $this->table;
    }

    /**
     * 在给定的表名前加上数据库名以及前缀
     *
     * @access  private
     * @param   string      $table    表名
     *
     * @return string
     */
    public function getFullTable($table)
    {
        return '`' .$this->connection->getDatabaseName(). '`.`'.$this->connection->getTablePrefix() . $table .'`';
    }

    /**
     * @param $field_id
     * @return $this
     */
    public function setFieldId($field_id)
    {
        $this->field_id = $field_id;

        return $this;
    }

    /**
     * @return int
     */
    public function getFieldId()
    {
        return $this->field_id;
    }

    /**
     * @param $field_name
     * @return $this
     */
    public function setFieldName($field_name)
    {
        $this->field_name = $field_name;

        return $this;
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->field_name;
    }

    /**
     * @param $field_pass
     * @return $this
     */
    public function setFieldPass($field_pass)
    {
        $this->field_pass = $field_pass;

        return $this;
    }

    /**
     * @return string
     */
    public function getFieldPass()
    {
        return $this->field_pass;
    }

    /**
     * @param $field_email
     * @return $this
     */
    public function setFieldEmail($field_email)
    {
        $this->field_email = $field_email;

        return $this;
    }

    /**
     * @return string
     */
    public function getFieldEmail()
    {
        return $this->field_email;
    }

    /**
     * @param $field_gender
     * @return $this
     */
    public function setFieldGender($field_gender)
    {
        $this->field_gender = $field_gender;

        return $this;
    }

    /**
     * @return string
     */
    public function getFieldGender()
    {
        return $this->field_gender;
    }

    /**
     * @param $field_birthday
     * @return $this
     */
    public function setFieldBirthDay($field_birthday)
    {
        $this->field_birthday = $field_birthday;

        return $this;
    }

    /**
     * @return string
     */
    public function getFieldBirthDay()
    {
        return $this->field_birthday;
    }

    /**
     * @param $field_reg_date
     * @return $this
     */
    public function setFieldRegDate($field_reg_date)
    {
        $this->field_reg_date = $field_reg_date;

        return $this;
    }

    /**
     * @return int
     */
    public function getFieldRegDate()
    {
        return $this->field_reg_date;
    }
    
    
    
    
}