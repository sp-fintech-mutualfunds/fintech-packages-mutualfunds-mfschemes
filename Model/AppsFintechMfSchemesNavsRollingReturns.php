<?php

namespace Apps\Fintech\Packages\Mf\Schemes\Model;

use System\Base\BaseModel;

class AppsFintechMfSchemesNavsRollingReturns extends BaseModel
{
    public $id;

    public $last_updated;

    public $year;

    public $two_year;

    public $three_years;

    public $five_years;

    public $ten_years;

    public $fifteen_years;
}