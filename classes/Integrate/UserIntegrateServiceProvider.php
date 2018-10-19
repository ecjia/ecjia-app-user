<?php

namespace Ecjia\App\User\Integrate;

use Royalcms\Component\Support\ServiceProvider;

class UserIntegrateServiceProvider extends  ServiceProvider
{

    public function register()
    {
        $this->registerIntegrateManager();

        $this->registerIntegratePlugin();


        $this->loadAlias();
    }


    protected function registerIntegrateManager()
    {
        $this->royalcms->bindShared('ecjia.integrate', function($royalcms) {
            return new UserManager();
        });
    }


    protected function registerIntegratePlugin()
    {
        $this->royalcms->bindShared('ecjia.integrate.plugin', function($royalcms) {
            return new IntegratePlugin();
        });
    }


    /**
     * Load the alias = One less install step for the user
     */
    protected function loadAlias()
    {
        $this->royalcms->booting(function()
        {
            $loader = \Royalcms\Component\Foundation\AliasLoader::getInstance();
            $loader->alias('ecjia_integrate', 'Ecjia\App\User\Integrate\Facades\EcjiaIntegrate');

        });
    }
    
}