<?php


namespace Ecjia\App\User\Integrate\Plugins;

use Ecjia\App\User\Integrate\UserIntegrateDatabaseAbstract;

class IntegrateEcjia extends UserIntegrateDatabaseAbstract
{


    public function __construct()
    {
        parent::__construct();

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
     * 获取插件的元数据
     *
     * @return \Royalcms\Component\Support\Collection
     */
    public function getPluginMateData()
    {
        return collect([
            'integrate_id'      => 1,
            'integrate_code'    => $this->getCode(),
            'integrate_name'    => $this->loadLanguage('ecjia'),
            'integrate_desc'    => $this->loadLanguage('ecjia_desc'),
            'configure'         => null,
        ]);
    }


}
