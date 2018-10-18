<?php


namespace Ecjia\App\User\Integrate\Plugins;

use Ecjia\App\User\Integrate\UserIntegrateAbstract;

class IntegrateEcjia extends UserIntegrateAbstract
{

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

    }


    /**
     *  设置cookie
     *
     * @return void
     */
    public function setCookie($username, $remember = null)
    {

    }


    public function compile_password($cfg)
    {

    }


    public function check_user($username, $password = null)
    {

    }

    public function check_email($email)
    {

    }
    
    
}