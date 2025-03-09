<?php

namespace Apps\Fintech\Packages\Mf\Schemes\Model;

use System\Base\BaseModel;

class AppsFintechMfSchemesDetails extends BaseModel
{
    public $id;

    public $amfi_code;

    public $vendor_code;

    public $investment_objective;

    public $crisil_rating;

    public $start_date;

    public $expense_ratio;

    public $expense_ratio_date;

    public $direct;

    public $switch_allowed;

    public $stp_flag;

    public $swp_flag;

    public $instant;

    public $lock_in_period;

    public $tax_period;

    public $lump_available;

    public $lump_min;

    public $lump_min_additional;

    public $lump_max;

    public $lump_multiplier;

    public $sip_available;

    public $sip_min;

    public $sip_max;

    public $sip_multiplier;

    public $sip_maximum_gap;

    public $redemption_allowed;

    public $redemption_amount_multiple;

    public $redemption_amount_minimum;

    public $redemption_quantity_multiple;

    public $redemption_quantity_minimum;
}