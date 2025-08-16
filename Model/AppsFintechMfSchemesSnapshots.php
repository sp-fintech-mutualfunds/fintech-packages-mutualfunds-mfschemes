<?php

namespace Apps\Fintech\Packages\Mf\Schemes\Model;

use System\Base\BaseModel;

class AppsFintechMfSchemesSnapshots extends BaseModel
{
    public $id;

    public $date;

    public $day_trajectory;

    public $day_cagr;

    public $year_cagr;

    public $two_year_cagr;

    public $three_year_cagr;

    public $five_year_cagr;

    public $seven_year_cagr;

    public $ten_year_cagr;

    public $fifteen_year_cagr;

    public $year_rr;

    public $two_year_rr;

    public $three_year_rr;

    public $five_year_rr;

    public $seven_year_rr;

    public $ten_year_rr;

    public $fifteen_year_rr;

    public $latest_nav;
}