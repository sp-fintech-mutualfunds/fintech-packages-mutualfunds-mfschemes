<?php

namespace Apps\Fintech\Packages\Mf\Schemes\Model;

use System\Base\BaseModel;

class AppsFintechMfSchemesAll extends BaseModel
{
    public $id;

    public $isin;

    public $isin_reinvest;

    public $name;

    public $scheme_name;

    public $scheme_type;

    public $expense_ratio_type;

    public $management_type;

    public $plan_type;

    public $category_id;

    public $amc_id;

    public $closed;

    public $closed_date;

    public $minimum_amount;

    public $launch_date;

    public $start_date;

    public $crisil_rating;

    public $scheme_md5;

    public $navs_last_updated;

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