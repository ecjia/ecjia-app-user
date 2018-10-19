<?php
/**
 * Created by PhpStorm.
 * User: royalwang
 * Date: 2018/10/18
 * Time: 5:33 PM
 */

namespace Ecjia\App\User\Integrate;


class Password
{
    /**
     * md5加密方式
     * int
     */
    const PWD_MD5 = 1;

    /**
     * 前置验证串的加密方式
     * int
     */
    const PWD_PRE_SALT = 2;

    /**
     * 后置验证串的加密方式
     * int
     */
    const PWD_SUF_SALT = 3;

    /**
     * 编译密码函数
     *
     * @param $password
     * @param $md5password
     * @param $salt
     * @param $type
     */
    public function compilePassword($password, $md5password = null, $salt = null, $type = self::PWD_MD5)
    {
        if ($password) {
            $md5password = md5($password);
        }

        $newpassword = '';

        switch ($type) {
            case self::PWD_MD5 :
                if (!empty($salt)) {
                    $newpassword = md5($md5password . $salt);
                } else {
                    $newpassword = $md5password;
                }
                break;

            case self::PWD_PRE_SALT :
                if (empty($salt)) {
                    $salt = '';
                }
                $newpassword = md5($salt . $md5password);
                break;

            case self::PWD_SUF_SALT :
                if (empty($salt)) {
                    $salt = '';
                }
                $newpassword = md5($md5password . $salt);
                break;

            default:
                break;
        }

        return $newpassword;
    }

}