<?php

namespace Apps\Fintech\Packages\Mf\Schemes\Model;

use System\Base\BaseModel;

class AppsFintechMfSchemes extends BaseModel
{
    public $id;

    public $isin;

    public $isin_reinvest;

    public $amfi_code;

    public $vendor_code;

    public $name;

    public $scheme_type;

    public $expense_ratio_type;

    public $management_type;

    public $plan_type;

    public $category_id;

    public $amc_id;
}