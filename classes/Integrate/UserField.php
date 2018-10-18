<?php

namespace Ecjia\App\User\Integrate;


class UserField
{
    /**
     * 整合对象会员表名
     * @var string
     */
    protected $user_table;
    
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
     * @param $field_birthday
     * @return $this
     */
    public function setFieldBirthDay($field_birthday)
    {
        $this->field_birthday = $field_birthday;

        return $this;
    }

    /**
     * @return date
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