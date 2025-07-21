<?php

namespace Apps\Fintech\Packages\Mf\Schemes\Model;

use System\Base\BaseModel;

class AppsFintechMfSchemesNavs extends BaseModel
{
    public $id;

    public $last_updated;

    public $latest_nav;

    public $diff;

    public $diff_percent;

    public $trajectory;

    public $navs;
}