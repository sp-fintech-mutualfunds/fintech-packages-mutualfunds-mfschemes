<?php

namespace Apps\Fintech\Packages\Mf\Schemes\Install;

use Apps\Fintech\Packages\Mf\Schemes\Install\Schema\MfSchemes;
use System\Base\BasePackage;

class Install extends BasePackage
{
    public function install()
    {
        $this->preInstall();

        $this->installDb();

        $this->postInstall();

        return true;
    }

    protected function preInstall()
    {
        return true;
    }

    protected function installDb()
    {
        //Refer to Package installation for Core.
        return true;
    }

    protected function postInstall()
    {
        return true;
    }

    public function uninstall()
    {
        //Check Relationship
        //Drop Table(s)
        return true;
    }
}